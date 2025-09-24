<?php

namespace App\Services\Karyawan;

use App\Models\Reservation;
use App\Models\FixedSchedule;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class ReservationService
{
    public function create(array $data)
{
    $data['status'] = 'pending';

    // Parse tanggal & waktu
    $tanggal = Carbon::parse($data['tanggal'])->format('Y-m-d');
    $mulai   = Carbon::parse($tanggal . ' ' . $data['waktu_mulai']);
    $selesai = Carbon::parse($tanggal . ' ' . $data['waktu_selesai']);

    // 🚫 Tidak boleh reservasi di waktu lampau
    if ($mulai->isPast()) {
        throw ValidationException::withMessages([
            'tanggal' => 'Tidak bisa membuat reservasi di waktu yang sudah lewat.'
        ]);
    }

    // 🚫 Limit waktu booking: maksimal H-30
    if ($mulai->gt(now()->addDays(30))) {
        throw ValidationException::withMessages([
            'tanggal' => 'Reservasi hanya bisa dilakukan maksimal H-30 sebelum tanggal meeting.'
        ]);
    }

    // 🚫 Validasi waktu mulai < waktu selesai
    if ($mulai >= $selesai) {
        throw ValidationException::withMessages([
            'waktu' => 'Waktu mulai harus lebih awal dari waktu selesai.'
        ]);
    }

    // 🚫 Durasi minimal 3 jam
    if ($selesai->diffInHours($mulai) < 3) {
        throw ValidationException::withMessages([
            'durasi' => 'Durasi meeting minimal 3 jam.'
        ]);
    }

    // Simpan tanggal & waktu
    $data['tanggal']       = $tanggal;
    $data['hari']          = Carbon::parse($tanggal)->locale('id')->dayName;
    $data['waktu_mulai']   = $mulai->format('H:i');
    $data['waktu_selesai'] = $selesai->format('H:i');

    // ✅ Validasi bentrok dengan FixedSchedule (HARUS ditolak)
    $conflictFixed = FixedSchedule::where('room_id', $data['room_id'])
        ->where('hari', $data['hari'])
        ->where(function ($q) use ($mulai, $selesai) {
            $q->whereBetween('waktu_mulai', [$mulai, $selesai])
              ->orWhereBetween('waktu_selesai', [$mulai, $selesai])
              ->orWhere(function ($q2) use ($mulai, $selesai) {
                  $q2->where('waktu_mulai', '<=', $mulai)
                     ->where('waktu_selesai', '>=', $selesai);
              });
        })
        ->exists();

    if ($conflictFixed) {
        throw ValidationException::withMessages([
            'reservation' => 'Bentrok dengan jadwal tetap.'
        ]);
    }

    // ✅ Cek bentrok dengan reservasi lain, tapi JANGAN tolak
    $conflictReservation = Reservation::overlapping(
        $data['room_id'], $mulai, $selesai
    )->whereDate('tanggal', $tanggal)
     ->whereIn('status', ['pending', 'approved'])
     ->exists();

    if ($conflictReservation) {
        $data['reason'] = ($data['reason'] ?? '') . ' (Bentrok, menunggu keputusan admin)';
    }

    return Reservation::create($data);
}


    public function getUserReservations($userId)
    {
        return Reservation::with('room')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Batalkan reservasi oleh karyawan
     */
    public function cancel(int $reservationId, int $userId, string $reason)
    {
        $reservation = Reservation::where('id', $reservationId)
            ->where('user_id', $userId) // hanya boleh cancel miliknya sendiri
            ->whereIn('status', ['pending', 'approved']) // hanya yg aktif
            ->firstOrFail();

        $reservation->update([
            'status' => 'canceled',
            'reason' => $reason,
        ]);

        return $reservation;
    }
}

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

        // Validasi waktu
        if ($mulai >= $selesai) {
            throw ValidationException::withMessages([
                'waktu' => 'Waktu mulai harus lebih awal dari waktu selesai.'
            ]);
        }

        // Simpan tanggal & waktu
        $data['tanggal']       = $tanggal;
        $data['hari']          = Carbon::parse($tanggal)->locale('id')->dayName; // ✅ auto "Senin"
        $data['waktu_mulai']   = $mulai->format('H:i');
        $data['waktu_selesai'] = $selesai->format('H:i');


        // ✅ Validasi bentrok dengan FixedSchedule
        $dayOfWeek = Carbon::parse($tanggal)->dayOfWeek; // 0=Min, 1=Senin, dst

        $conflictFixed = FixedSchedule::where('room_id', $data['room_id'])
    ->where('hari', $data['hari'])   // ✅ bandingkan string "Senin"
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

        // ✅ Validasi bentrok dengan reservasi lain
        $conflictReservation = Reservation::overlapping(
            $data['room_id'], $mulai, $selesai
        )->exists();

        if ($conflictReservation) {
            throw ValidationException::withMessages([
                'reservation' => 'Bentrok dengan reservasi lain.'
            ]);
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
}

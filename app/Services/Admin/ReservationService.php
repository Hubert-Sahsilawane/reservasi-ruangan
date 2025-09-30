<?php

namespace App\Services\Admin;

use App\Models\Reservation;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReservationApprovedMail;
use App\Mail\ReservationRejectedMail;
use App\Mail\ReservationCanceledByOverlapMail;
use App\Services\Traits\ReservationCommonTrait;
use Illuminate\Validation\ValidationException;

class ReservationService
{
    use ReservationCommonTrait;

public function getAll()
    {
        return Reservation::with(['user', 'room'])
            ->latest()
            ->get(); // otomatis exclude soft deleted
    }
    
    public function updateStatus($id, array $data)
{
    $reservation = Reservation::with(['user', 'room'])->findOrFail($id);

    if (!in_array($data['status'], ['approved', 'rejected'])) {
        throw ValidationException::withMessages([
            'status' => 'Status reservasi tidak valid.'
        ]);
    }

    $reservation->update([
        'status' => $data['status'],
        'reason' => $data['reason'], // ✅ wajib diisi
    ]);

    // ✅ Approved
    if ($data['status'] === 'approved') {
        if ($reservation->room) {
            $reservation->room->update(['status' => 'aktif']);
        }

        if ($reservation->user && $reservation->user->email) {
            Mail::to($reservation->user->email)
                ->send(new ReservationApprovedMail($reservation, $data['reason']));
        }

        // Tolak semua pending lain yang bentrok
        $overlaps = Reservation::where('room_id', $reservation->room_id)
            ->where('hari', $reservation->hari)
            ->where('id', '!=', $reservation->id)
            ->where('status', 'pending')
            ->where(function ($q) use ($reservation) {
                $q->whereBetween('waktu_mulai', [$reservation->waktu_mulai, $reservation->waktu_selesai])
                  ->orWhereBetween('waktu_selesai', [$reservation->waktu_mulai, $reservation->waktu_selesai])
                  ->orWhere(function ($q2) use ($reservation) {
                      $q2->where('waktu_mulai', '<=', $reservation->waktu_mulai)
                         ->where('waktu_selesai', '>=', $reservation->waktu_selesai);
                  });
            })
            ->get();

        foreach ($overlaps as $overlap) {
            $overlap->update([
                'status' => 'rejected',
                'reason' => 'Ditolak otomatis karena bentrok dengan reservasi lain yang sudah disetujui.'
            ]);

            if ($overlap->user && $overlap->user->email) {
                Mail::to($overlap->user->email)
                    ->send(new ReservationCanceledByOverlapMail($overlap, $reservation));
            }
        }
    }

    // ✅ Rejected
    if ($data['status'] === 'rejected' && $reservation->user && $reservation->user->email) {
        Mail::to($reservation->user->email)
            ->send(new ReservationRejectedMail($reservation, $data['reason']));
    }

    return $reservation;
}


    public function delete($id)
{
    $reservation = Reservation::findOrFail($id);

    // contoh: jangan hapus kalau status masih pending
    if ($reservation->status === 'pending') {
        throw ValidationException::withMessages([
            'delete' => 'Reservasi pending tidak boleh langsung dihapus.'
        ]);
    }

    $reservation->delete(); // ini soft delete
    return true;
}


}

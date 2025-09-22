<?php

namespace App\Services\Admin;

use App\Models\Reservation;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReservationApprovedMail;
use App\Mail\ReservationRejectedMail;
use App\Mail\ReservationCanceledByOverlapMail;

class ReservationService
{
    public function getAll()
    {
        return Reservation::with(['user', 'room'])
            ->orderBy('tanggal', 'desc')
            ->orderBy('waktu_mulai')
            ->get();
    }

    public function approve($id)
    {
        return $this->updateStatus($id, 'approved');
    }

    public function reject($id, $reason = null)
    {
        return $this->updateStatus($id, 'rejected', $reason);
    }

    public function delete($id)
    {
        $reservation = Reservation::findOrFail($id);
        $reservation->delete();

        return true;
    }

    public function updateStatus($id, string $status, $reason = null)
    {
        $reservation = Reservation::with(['user', 'room'])->findOrFail($id);

        $reservation->update(['status' => $status]);

        // ✅ Jika disetujui
        if ($status === 'approved') {
            // kirim email approved
            if ($reservation->user) {
                Mail::to($reservation->user->email)
                    ->send(new ReservationApprovedMail($reservation));
            }

            // ✅ Cancel semua pending yang bentrok di hari yg sama
            $overlaps = Reservation::where('room_id', $reservation->room_id)
                ->where('hari', $reservation->hari)   // tambahkan filter hari
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
                $overlap->update(['status' => 'canceled']);
                if ($overlap->user) {
                    Mail::to($overlap->user->email)
                        ->send(new ReservationCanceledByOverlapMail($overlap, $reservation));
                }
            }
        }

        // ✅ Jika ditolak
        if ($status === 'rejected' && $reservation->user) {
            Mail::to($reservation->user->email)
                ->send(new ReservationRejectedMail($reservation, $reason));
        }

        return $reservation;
    }
}

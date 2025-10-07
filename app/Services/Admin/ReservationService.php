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

    /**
     * 🔍 Get all reservations with optional filters and pagination
     */
    public function getAllWithFilters(array $filters = [], int $perPage = 10)
{
    $query = Reservation::with(['user', 'room'])
        ->orderBy('tanggal', 'desc')
        ->orderBy('waktu_mulai', 'asc');

    // Filter berdasarkan tanggal
    if (!empty($filters['tanggal'])) {
        $query->whereDate('tanggal', $filters['tanggal']);
    }

    // Filter berdasarkan hari (day_of_week → hari)
    if (!empty($filters['hari'])) {
        $query->where('hari', $filters['hari']);
    }

    // Filter berdasarkan waktu mulai
    if (!empty($filters['waktu_mulai'])) {
        $query->whereTime('waktu_mulai', '>=', $filters['waktu_mulai']);
    }

    // Filter berdasarkan waktu selesai
    if (!empty($filters['waktu_selesai'])) {
        $query->whereTime('waktu_selesai', '<=', $filters['waktu_selesai']);
    }

    return $query->paginate($perPage);
}



    public function updateStatus($id, array $data)
    {
        $reservation = Reservation::with(['user', 'room'])->findOrFail($id);

        if (!in_array($data['status'], ['approved', 'rejected'])) {
            throw ValidationException::withMessages([
                'status' => 'Status reservasi tidak valid.'
            ]);
        }

        if (empty($data['reason'])) {
            throw ValidationException::withMessages([
                'reason' => 'Alasan wajib diisi.'
            ]);
        }

        $reservation->update([
            'status' => $data['status'],
            'reason' => $data['reason'],
        ]);

        // Approved
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

        // Rejected
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

        $reservation->delete(); // soft delete
        return true;
    }
}

<?php

namespace App\Services\Admin;

use App\Models\Reservation;
use Illuminate\Support\Facades\Mail;
use Spatie\Activitylog\Models\Activity;
use function activity;
use App\Mail\ReservationApprovedMail;
use App\Mail\ReservationRejectedMail;
use App\Mail\ReservationCanceledByOverlapMail;
use App\Services\Traits\ReservationCommonTrait;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;

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

        if (!empty($filters['tanggal'])) {
            $query->whereDate('tanggal', $filters['tanggal']);
        }

        if (!empty($filters['hari'])) {
            $query->where('hari', $filters['hari']);
        }

        if (!empty($filters['waktu_mulai'])) {
            $query->whereTime('waktu_mulai', '>=', $filters['waktu_mulai']);
        }

        if (!empty($filters['waktu_selesai'])) {
            $query->whereTime('waktu_selesai', '<=', $filters['waktu_selesai']);
        }

        return $query->paginate($perPage);
    }

    public function updateStatus($id, array $data)
    {
        $reservation = Reservation::with(['user', 'room'])->findOrFail($id);
        $user = Auth::user();

        if (!in_array($data['status'], ['approved', 'rejected'])) {
            throw ValidationException::withMessages(['status' => 'Status reservasi tidak valid.']);
        }

        if (empty($data['reason'])) {
            throw ValidationException::withMessages(['reason' => 'Alasan wajib diisi.']);
        }

        $reservation->update([
            'status' => $data['status'],
            'reason' => $data['reason'],
        ]);

        // ✅ Catat activity log
        activity()
            ->performedOn($reservation)
            ->causedBy($user)
            ->withProperties(['status' => $data['status'], 'reason' => $data['reason']])
            ->log("Admin mengubah status reservasi menjadi {$data['status']} untuk ruangan {$reservation->room->name}");

        // Approved
        if ($data['status'] === 'approved') {
            if ($reservation->room) {
                $reservation->room->update(['status' => 'aktif']);
            }

            if ($reservation->user && $reservation->user->email) {
                Mail::to($reservation->user->email)->send(new ReservationApprovedMail($reservation, $data['reason']));
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
                    'reason' => 'Ditolak otomatis karena bentrok dengan reservasi lain yang disetujui.'
                ]);

                if ($overlap->user && $overlap->user->email) {
                    Mail::to($overlap->user->email)
                        ->send(new ReservationCanceledByOverlapMail($overlap, $reservation));
                }

                // Log juga penolakan otomatis
                activity()
                    ->performedOn($overlap)
                    ->causedBy($user)
                    ->log("Reservasi otomatis ditolak karena bentrok dengan reservasi lain.");
            }
        }

        // Rejected
        if ($data['status'] === 'rejected' && $reservation->user && $reservation->user->email) {
            Mail::to($reservation->user->email)->send(new ReservationRejectedMail($reservation, $data['reason']));
        }

        return $reservation;
    }

    public function delete($id)
    {
        $reservation = Reservation::findOrFail($id);
        $user = Auth::user();

        if ($reservation->status === 'pending') {
            throw ValidationException::withMessages([
                'delete' => 'Reservasi pending tidak boleh langsung dihapus.'
            ]);
        }

        $reservation->delete();

        // ✅ Catat activity log
        activity()
            ->performedOn($reservation)
            ->causedBy($user)
            ->log("Admin menghapus reservasi untuk ruangan {$reservation->room->name}");

        return true;
    }
}

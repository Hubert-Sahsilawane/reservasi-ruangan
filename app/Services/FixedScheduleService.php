<?php

namespace App\Services;

use App\Models\FixedSchedule;
use App\Models\Reservation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use App\Mail\ReservationRejectedByFixedScheduleMail;

class FixedScheduleService
{
    /**
     * Ambil semua fixed schedule
     */
    public function getAll()
    {
        return FixedSchedule::with(['room', 'user'])->latest()->get();
    }

    /**
     * Buat FixedSchedule baru + auto reject reservation yang bentrok
     */
    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            // ✅ isi user_id otomatis dari user yang login
            $data['user_id'] = Auth::guard('api')->id();

            $fixedSchedule = FixedSchedule::create($data);

            // ✅ Cari reservation yang bentrok dengan jadwal tetap
            $conflictReservations = Reservation::where('room_id', $fixedSchedule->room_id)
                ->where('tanggal', $fixedSchedule->tanggal)
                ->whereIn('status', ['pending', 'approved'])
                ->where(function ($q) use ($fixedSchedule) {
                    $q->whereBetween('waktu_mulai', [$fixedSchedule->waktu_mulai, $fixedSchedule->waktu_selesai])
                        ->orWhereBetween('waktu_selesai', [$fixedSchedule->waktu_mulai, $fixedSchedule->waktu_selesai])
                        ->orWhere(function ($q2) use ($fixedSchedule) {
                            $q2->where('waktu_mulai', '<=', $fixedSchedule->waktu_mulai)
                               ->where('waktu_selesai', '>=', $fixedSchedule->waktu_selesai);
                        });
                })
                ->get();

            // ✅ Update jadi rejected + kirim email
            foreach ($conflictReservations as $reservation) {
                $reservation->update([
                    'status' => 'rejected',
                    'reason' => 'Ditolak otomatis karena bentrok dengan Fixed Schedule.'
                ]);

                if ($reservation->user && $reservation->user->email) {
                    Mail::to($reservation->user->email)
                        ->send(new ReservationRejectedByFixedScheduleMail($reservation));
                }
            }

            return $fixedSchedule;
        });
    }

    /**
     * Update FixedSchedule + cek ulang konflik
     */
    public function update(FixedSchedule $fixedSchedule, array $data)
    {
        return DB::transaction(function () use ($fixedSchedule, $data) {
            // ✅ catat siapa yang terakhir update
            $data['user_id'] = Auth::guard('api')->id();

            $fixedSchedule->update($data);

            // ✅ Cari reservation yang bentrok
            $conflictReservations = Reservation::where('room_id', $fixedSchedule->room_id)
                ->where('tanggal', $fixedSchedule->tanggal)
                ->whereIn('status', ['pending', 'approved'])
                ->where(function ($q) use ($fixedSchedule) {
                    $q->whereBetween('waktu_mulai', [$fixedSchedule->waktu_mulai, $fixedSchedule->waktu_selesai])
                        ->orWhereBetween('waktu_selesai', [$fixedSchedule->waktu_mulai, $fixedSchedule->waktu_selesai])
                        ->orWhere(function ($q2) use ($fixedSchedule) {
                            $q2->where('waktu_mulai', '<=', $fixedSchedule->waktu_mulai)
                               ->where('waktu_selesai', '>=', $fixedSchedule->waktu_selesai);
                        });
                })
                ->get();

            // ✅ Update jadi rejected + kirim email
            foreach ($conflictReservations as $reservation) {
                $reservation->update([
                    'status' => 'rejected',
                    'reason' => 'Ditolak otomatis karena bentrok dengan Fixed Schedule.'
                ]);

                if ($reservation->user && $reservation->user->email) {
                    Mail::to($reservation->user->email)
                        ->send(new ReservationRejectedByFixedScheduleMail($reservation));
                }
            }

            return $fixedSchedule;
        });
    }

    /**
     * Hapus FixedSchedule
     */
    public function delete(FixedSchedule $fixedSchedule)
    {
        return $fixedSchedule->delete();
    }
}

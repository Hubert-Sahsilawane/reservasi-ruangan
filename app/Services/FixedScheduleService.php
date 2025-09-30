<?php

namespace App\Services;

use App\Models\FixedSchedule;
use App\Models\Reservation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use App\Mail\ReservationRejectedByFixedScheduleMail;
use Carbon\Carbon;

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
            // user_id otomatis dari user login
            $data['user_id'] = Auth::guard('api')->id();

            // generate hari otomatis dari tanggal
            if (!empty($data['tanggal'])) {
                $data['hari'] = Carbon::parse($data['tanggal'])
                    ->locale('id')
                    ->dayName;
            }

            $fixedSchedule = FixedSchedule::create($data);

            // cari reservation yang bentrok
            $this->rejectConflictingReservations($fixedSchedule);

            return $fixedSchedule;
        });
    }

    /**
     * Update FixedSchedule + cek ulang konflik
     */
    public function update(int $id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $fixedSchedule = FixedSchedule::findOrFail($id);

            $data['user_id'] = Auth::guard('api')->id();

            // generate hari otomatis dari tanggal
            if (!empty($data['tanggal'])) {
                $data['hari'] = Carbon::parse($data['tanggal'])
                    ->locale('id')
                    ->dayName;
            }

            $fixedSchedule->update($data);

            // cek reservation bentrok
            $this->rejectConflictingReservations($fixedSchedule);

            return $fixedSchedule;
        });
    }

    /**
     * Hapus FixedSchedule
     */
    public function delete(int $id)
    {
        $fixedSchedule = FixedSchedule::findOrFail($id);
        return $fixedSchedule->delete();
    }

    /**
     * Private helper → reject reservation yang bentrok
     */
    private function rejectConflictingReservations(FixedSchedule $fixedSchedule)
    {
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
    }
}

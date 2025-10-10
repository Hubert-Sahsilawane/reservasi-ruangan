<?php

namespace App\Services;

use App\Models\FixedSchedule;
use App\Models\Reservation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use App\Mail\ReservationRejectedByFixedScheduleMail;
use Carbon\Carbon;
use Exception;

class FixedScheduleService
{
    /* -------------------------------------------------------------------------- */
    /*  GET ALL FIXED SCHEDULES (with filter & pagination)                       */
    /* -------------------------------------------------------------------------- */
   public function getAll(array $filters = [], int $perPage = 10, int $page = 1)
{
    try {
        $query = FixedSchedule::with(['room', 'user'])
        ->orderBy('id', 'asc'); // biar urut stabil (kayak reservasi)

        // 🏢 Filter ruangan
        if (!empty($filters['room_id'])) {
            $query->where('room_id', $filters['room_id']);
        }

        // 📅 Filter tanggal
        if (!empty($filters['tanggal'])) {
            $query->whereDate('tanggal', $filters['tanggal']);
        }

        // ⏰ Filter waktu mulai & selesai
        if (!empty($filters['waktu_mulai'])) {
            $query->whereTime('waktu_mulai', '>=', $filters['waktu_mulai']);
        }

        if (!empty($filters['waktu_selesai'])) {
            $query->whereTime('waktu_selesai', '<=', $filters['waktu_selesai']);
        }

        // 🔁 Pagination sama seperti reservasi
        return $query->paginate($perPage, ['*'], 'page', $page);

    } catch (Exception $e) {
        throw new Exception("Gagal mengambil data jadwal tetap: " . $e->getMessage());
    }
}

    /* -------------------------------------------------------------------------- */
    /*  GET FIXED SCHEDULE BY ID                                                  */
    /* -------------------------------------------------------------------------- */
    public function getById(int $id)
    {
        try {
            $schedule = FixedSchedule::with(['room', 'user'])->findOrFail($id);
            return $schedule;
        } catch (Exception $e) {
            throw new Exception("Data jadwal tetap tidak ditemukan: " . $e->getMessage());
        }
    }

    /* -------------------------------------------------------------------------- */
    /*  CREATE FIXED SCHEDULE                                                     */
    /* -------------------------------------------------------------------------- */
    public function create(array $data)
    {
        try {
            return DB::transaction(function () use ($data) {
                $data['user_id'] = Auth::guard('api')->id();

                if (!empty($data['tanggal'])) {
                    $data['hari'] = Carbon::parse($data['tanggal'])
                        ->locale('id')
                        ->dayName;
                }

                $fixedSchedule = FixedSchedule::create($data);

                // 🚫 Cek & tolak reservasi yang bentrok
                $this->rejectConflictingReservations($fixedSchedule);

                return $fixedSchedule;
            });
        } catch (Exception $e) {
            throw new Exception("Gagal membuat jadwal tetap: " . $e->getMessage());
        }
    }

    /* -------------------------------------------------------------------------- */
    /*  UPDATE FIXED SCHEDULE                                                     */
    /* -------------------------------------------------------------------------- */
    public function update(int $id, array $data)
    {
        try {
            return DB::transaction(function () use ($id, $data) {
                $fixedSchedule = FixedSchedule::findOrFail($id);

                $data['user_id'] = Auth::guard('api')->id();

                if (!empty($data['tanggal'])) {
                    $data['hari'] = Carbon::parse($data['tanggal'])
                        ->locale('id')
                        ->dayName;
                }

                $fixedSchedule->update($data);

                // 🚫 Cek ulang bentrokan setelah update
                $this->rejectConflictingReservations($fixedSchedule);

                return $fixedSchedule;
            });
        } catch (Exception $e) {
            throw new Exception("Gagal memperbarui jadwal tetap: " . $e->getMessage());
        }
    }

    /* -------------------------------------------------------------------------- */
    /*  DELETE FIXED SCHEDULE                                                     */
    /* -------------------------------------------------------------------------- */
    public function delete(int $id)
    {
        try {
            $fixedSchedule = FixedSchedule::findOrFail($id);
            $fixedSchedule->delete();

            return true;
        } catch (Exception $e) {
            throw new Exception("Gagal menghapus jadwal tetap: " . $e->getMessage());
        }
    }

    /* -------------------------------------------------------------------------- */
    /*  PRIVATE FUNCTION: REJECT CONFLICTING RESERVATIONS                         */
    /* -------------------------------------------------------------------------- */
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
                'reason' => 'Ditolak otomatis karena bentrok dengan jadwal tetap.'
            ]);

            if ($reservation->user && $reservation->user->email) {
                Mail::to($reservation->user->email)
                    ->send(new ReservationRejectedByFixedScheduleMail($reservation));
            }
        }
    }
}

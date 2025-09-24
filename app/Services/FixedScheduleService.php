<?php
namespace App\Services;

use App\Models\FixedSchedule;
use App\Models\Reservation;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class FixedScheduleService
{
    public function getAll()
    {
        return FixedSchedule::with(['room','user'])->get();
    }

    public function find($id)
    {
        return FixedSchedule::with(['room','user'])->findOrFail($id);
    }

    public function create(array $data)
    {
        $data['user_id'] = Auth::id();

        // ✅ otomatis isi hari dari tanggal
        $data['hari'] = Carbon::parse($data['tanggal'])->locale('id')->dayName;

        // 🔍 Cek bentrok dengan FixedSchedule lain
        $conflict = FixedSchedule::where('room_id', $data['room_id'])
            ->where('hari', $data['hari'])
            ->where(function ($q) use ($data) {
                $q->whereBetween('waktu_mulai', [$data['waktu_mulai'], $data['waktu_selesai']])
                  ->orWhereBetween('waktu_selesai', [$data['waktu_mulai'], $data['waktu_selesai']])
                  ->orWhere(function ($q2) use ($data) {
                      $q2->where('waktu_mulai', '<=', $data['waktu_mulai'])
                         ->where('waktu_selesai', '>=', $data['waktu_selesai']);
                  });
            })
            ->exists();

        if ($conflict) {
            throw ValidationException::withMessages([
                'schedule' => 'Jadwal tetap bentrok dengan jadwal lain pada ruangan ini.'
            ]);
        }

        $schedule = FixedSchedule::create($data);

        // ✅ REJECT semua reservasi bentrok
        $conflictReservations = Reservation::where('room_id', $data['room_id'])
            ->where('hari', $data['hari'])
            ->whereIn('status', ['pending', 'approved'])
            ->get();

        foreach ($conflictReservations as $reservation) {
            $reservation->update([
                'status' => 'rejected',
                'reason' => 'Ditolak otomatis karena bentrok dengan Fixed Schedule.'
            ]);
        }

        return $schedule;
    }

    public function update($id, array $data)
    {
        $schedule = FixedSchedule::findOrFail($id);

        // ✅ otomatis isi hari dari tanggal
        $data['hari'] = Carbon::parse($data['tanggal'])->locale('id')->dayName;

        $conflict = FixedSchedule::where('room_id', $data['room_id'])
            ->where('hari', $data['hari'])
            ->where('id', '!=', $id)
            ->where(function ($q) use ($data) {
                $q->whereBetween('waktu_mulai', [$data['waktu_mulai'], $data['waktu_selesai']])
                  ->orWhereBetween('waktu_selesai', [$data['waktu_mulai'], $data['waktu_selesai']])
                  ->orWhere(function ($q2) use ($data) {
                      $q2->where('waktu_mulai', '<=', $data['waktu_mulai'])
                         ->where('waktu_selesai', '>=', $data['waktu_selesai']);
                  });
            })
            ->exists();

        if ($conflict) {
            throw ValidationException::withMessages([
                'schedule' => 'Perubahan bentrok dengan jadwal lain pada ruangan ini.'
            ]);
        }

        $schedule->update($data);
        return $schedule;
    }

    public function delete($id)
    {
        $schedule = FixedSchedule::findOrFail($id);
        $schedule->delete();
    }
}

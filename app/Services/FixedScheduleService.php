<?php

namespace App\Services;

use App\Models\FixedSchedule;
use Illuminate\Validation\ValidationException;

class FixedScheduleService
{
    public function getAll()
    {
        return FixedSchedule::with('room')->get();
    }

    public function find($id)
    {
        return FixedSchedule::with('room')->findOrFail($id);
    }

    public function create(array $data)
    {
        // Cek apakah bentrok dengan jadwal tetap lain di hari & ruangan ini
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

        return FixedSchedule::create($data);
    }

    public function update($id, array $data)
    {
        $schedule = FixedSchedule::findOrFail($id);

        // Cek bentrok (kecuali dirinya sendiri)
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

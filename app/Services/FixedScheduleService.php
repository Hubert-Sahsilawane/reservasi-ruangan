<?php

namespace App\Services;

use App\Models\FixedSchedule;

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
        return FixedSchedule::create($data);
    }

    public function update($id, array $data)
    {
        $schedule = FixedSchedule::findOrFail($id);
        $schedule->update($data);
        return $schedule;
    }

    public function delete($id)
    {
        $schedule = FixedSchedule::findOrFail($id);
        $schedule->delete();
    }
}

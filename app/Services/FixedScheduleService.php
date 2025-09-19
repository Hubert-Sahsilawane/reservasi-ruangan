<?php
namespace App\Services;

use App\Models\FixedSchedule;

class FixedScheduleService
{
    public function getAll()
    {
        return FixedSchedule::with('room')->get();
    }

    public function create(array $data): FixedSchedule
    {
        return FixedSchedule::create($data);
    }

    public function getById(FixedSchedule $fixedSchedule): FixedSchedule
    {
        return $fixedSchedule->load('room');
    }

    public function update(FixedSchedule $fixedSchedule, array $data): FixedSchedule
    {
        $fixedSchedule->update($data);
        return $fixedSchedule->load('room');
    }

    public function delete(FixedSchedule $fixedSchedule): bool
    {
        return $fixedSchedule->delete();
    }
}

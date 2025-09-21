<?php

namespace App\Services;

use App\Models\Reservation;

class ReservationService
{
    public function getAll()
    {
        return Reservation::with(['user', 'room'])->get();
    }

    public function find($id)
    {
        return Reservation::with(['user', 'room'])->findOrFail($id);
    }

    public function create(array $data)
    {
        return Reservation::create($data);
    }

    public function update($id, array $data)
    {
        $reservation = Reservation::findOrFail($id);
        $reservation->update($data);
        return $reservation;
    }

    public function delete($id)
    {
        $reservation = Reservation::findOrFail($id);
        $reservation->delete();
    }
}

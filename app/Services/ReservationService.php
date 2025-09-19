<?php
namespace App\Services;

use App\Models\Reservation;

class ReservationService
{
    public function getAll()
    {
        return Reservation::with(['room', 'user'])->get();
    }

    public function create(array $data, $userId): Reservation
    {
        $data['user_id'] = $userId;
        return Reservation::create($data);
    }

    public function getById(Reservation $reservation): Reservation
    {
        return $reservation->load(['room', 'user']);
    }

    public function update(Reservation $reservation, array $data): Reservation
    {
        $reservation->update($data);
        return $reservation->load(['room', 'user']);
    }

    public function delete(Reservation $reservation): bool
    {
        return $reservation->delete();
    }
}

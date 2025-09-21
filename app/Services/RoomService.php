<?php

namespace App\Services;

use App\Models\Room;

class RoomService
{
    public function getAll()
    {
        return Room::with(['reservations', 'fixedSchedules'])->get();
    }

    public function find($id)
    {
        return Room::with(['reservations.user', 'fixedSchedules'])->findOrFail($id);
    }

    public function create(array $data)
    {
        return Room::create($data);
    }

    public function update($id, array $data)
    {
        $room = Room::findOrFail($id);
        $room->update($data);
        return $room;
    }

    public function delete($id)
    {
        $room = Room::findOrFail($id);

        // Cek apakah masih ada reservasi aktif
        $activeReservation = $room->reservations()
            ->where('status', 'aktif')
            ->exists();

        if ($activeReservation) {
            throw new \Exception("Ruangan tidak bisa dihapus karena masih ada reservasi aktif.");
        }

        $room->delete();
    }
}

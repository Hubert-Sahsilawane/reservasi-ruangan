<?php

namespace App\Services;

use App\Models\Room;

class RoomService
{
    public function getAll()
    {
        return Room::all();
    }

    public function create(array $data): Room
    {
        return Room::create($data);
    }

    public function getById(Room $room): Room
    {
        return $room;
    }

    public function update(Room $room, array $data): Room
    {
        $room->update($data);
        return $room;
    }

    public function delete(Room $room): bool
    {
        return $room->delete();
    }
}

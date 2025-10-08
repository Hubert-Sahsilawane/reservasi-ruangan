<?php

namespace App\Services;

use App\Models\Room;
use Illuminate\Validation\ValidationException;

class RoomService
{
    /**
     * Ambil semua ruangan dengan filter & pagination.
     */
    public function getAll(array $filters = [], int $perPage = 10)
    {
        $query = Room::with(['reservations', 'fixedSchedules'])
            ->orderBy('name', 'asc');

        // Filter berdasarkan nama (LIKE)
        if (!empty($filters['name'])) {
            $query->where('name', 'like', '%' . $filters['name'] . '%');
        }

        // Filter kapasitas (>=)
        if (!empty($filters['kapasitas'])) {
            $query->where('kapasitas', '>=', $filters['kapasitas']);
        }

        // Filter status (exact match)
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('id', 'desc')->paginate($perPage);

    }

    /**
     * Ambil satu ruangan berdasarkan ID.
     */
    public function find($id)
    {
        return Room::with(['reservations.user', 'fixedSchedules'])->findOrFail($id);
    }

    /**
     * Tambah ruangan baru.
     */
    public function create(array $data)
    {
        return Room::create($data);
    }

    /**
     * Update data ruangan.
     */
    public function update($id, array $data)
    {
        $room = Room::findOrFail($id);
        $room->update($data);
        return $room;
    }

    /**
     * Hapus ruangan (dengan validasi dependensi).
     */
    public function delete($id)
    {
        $room = Room::findOrFail($id);

        $activeReservation = $room->reservations()
            ->whereIn('status', ['pending', 'approved'])
            ->exists();

        $hasFixedSchedule = $room->fixedSchedules()->exists();

        if ($activeReservation || $hasFixedSchedule) {
            throw ValidationException::withMessages([
                'room' => 'Ruangan tidak dapat dihapus karena masih memiliki reservasi aktif atau jadwal tetap.',
            ]);
        }

        return $room->delete();
    }
}

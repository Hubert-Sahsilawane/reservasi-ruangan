<?php

namespace App\Services\Traits;

use App\Models\Reservation;

trait ReservationCommonTrait
{
    /**
     * Ambil semua reservasi (include relasi user & room).
     */
    public function getAll()
    {
        return Reservation::with(['user', 'room'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Ambil detail reservasi berdasarkan ID (include relasi user & room).
     */
    public function getById(int $id)
    {
        return Reservation::with(['user', 'room'])->findOrFail($id);
    }
}

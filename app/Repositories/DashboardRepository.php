<?php

namespace App\Repositories;

use App\Models\Room;
use App\Models\Reservation;

class DashboardRepository
{
    public function getTotalRooms()
    {
        return Room::count();
    }

    public function getTotalReservations()
    {
        return Reservation::count();
    }

    public function getApprovedReservations()
    {
        return Reservation::where('status', 'approved')->count();
    }

    public function getRejectedReservations()
    {
        return Reservation::where('status', 'rejected')->count();
    }

    public function getReservationsByMonth()
    {
        return Reservation::selectRaw('MONTH(tanggal) as month, COUNT(*) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');
    }
}

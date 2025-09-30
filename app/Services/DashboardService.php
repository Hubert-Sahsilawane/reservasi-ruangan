<?php

namespace App\Services;

use App\Repositories\DashboardRepository;

class DashboardService
{
    protected $repo;

    public function __construct(DashboardRepository $repo)
    {
        $this->repo = $repo;
    }

    public function getStats()
    {
        $totalRooms = $this->repo->getTotalRooms();
        $totalReservations = $this->repo->getTotalReservations();
        $approved = $this->repo->getApprovedReservations();
        $rejected = $this->repo->getRejectedReservations();

        $stats = $this->repo->getReservationsByMonth();

        $chartData = [];
        for ($i = 1; $i <= 12; $i++) {
            $chartData[] = $stats[$i] ?? 0;
        }

        return [
            'rooms' => $totalRooms,
            'reservations' => $totalReservations,
            'approved' => $approved,
            'rejected' => $rejected,
            'chart' => [
                'labels' => ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
                'data' => $chartData
            ]
        ];
    }
}

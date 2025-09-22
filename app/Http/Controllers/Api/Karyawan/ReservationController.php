<?php

namespace App\Http\Controllers\Api\Karyawan;

use App\Http\Controllers\Controller;
use App\Http\Requests\Karyawan\ReservationStoreRequest;
use App\Http\Resources\Karyawan\ReservationResource;
use App\Services\Karyawan\ReservationService;
use Illuminate\Support\Facades\Auth;

class ReservationController extends Controller
{
    private $service;

    public function __construct(ReservationService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $reservations = $this->service->getUserReservations(Auth::id());
        return ReservationResource::collection($reservations);
    }

    public function store(ReservationStoreRequest $request)
{
    $reservation = $this->service->create([
        'user_id'       => Auth::id(),
        'room_id'       => $request->room_id,
        'tanggal'       => $request->tanggal,
        'hari'          => $request->hari,   // ✅ tambahkan
        'waktu_mulai'   => $request->waktu_mulai,
        'waktu_selesai' => $request->waktu_selesai,
        'keterangan'    => $request->keterangan,
    ]);

    return new ReservationResource($reservation);
}
}

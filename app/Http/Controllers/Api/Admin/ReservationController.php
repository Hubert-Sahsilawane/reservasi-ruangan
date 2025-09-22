<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReservationUpdateRequest;
use App\Http\Resources\Admin\ReservationResource;
use App\Services\Admin\ReservationService;

class ReservationController extends Controller
{
    private $service;

    public function __construct(ReservationService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $reservations = $this->service->getAll();
        return ReservationResource::collection($reservations);
    }

    public function approve($id)
    {
        $reservation = $this->service->approve($id);
        return new ReservationResource($reservation);
    }

    public function reject(ReservationUpdateRequest $request, $id)
    {
        $reservation = $this->service->updateStatus($id, $request->status);
        return new ReservationResource($reservation);
    }

    public function destroy($id)
    {
        $this->service->delete($id);
        return response()->json(['message' => 'Reservasi berhasil dihapus']);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReservationRequest;
use App\Http\Resources\ReservationResource;
use App\Services\ReservationService;

class ReservationController extends Controller
{
    protected $reservationService;

    public function __construct(ReservationService $reservationService)
    {
        $this->reservationService = $reservationService;
    }

    public function index()
    {
        return ReservationResource::collection($this->reservationService->getAll());
    }

    public function store(ReservationRequest $request)
    {
        $reservation = $this->reservationService->create($request->validated());
        return new ReservationResource($reservation);
    }

    public function show($id)
    {
        return new ReservationResource($this->reservationService->find($id));
    }

    public function update(ReservationRequest $request, $id)
    {
        $reservation = $this->reservationService->update($id, $request->validated());
        return new ReservationResource($reservation);
    }

    public function destroy($id)
    {
        $this->reservationService->delete($id);
        return response()->json(['message' => 'Reservation deleted successfully']);
    }

        public function approve($id)
    {
        $reservation = $this->reservationService->approve($id);
        return new ReservationResource($reservation);
    }

    public function reject($id)
    {
        $reservation = $this->reservationService->reject($id);
        return new ReservationResource($reservation);
    }

}

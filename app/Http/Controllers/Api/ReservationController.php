<?php
namespace App\Http\Controllers;

use App\Http\Requests\StoreReservationRequest;
use App\Http\Requests\UpdateReservationRequest;
use App\Http\Resources\ReservationResource;
use App\Models\Reservation;
use App\Services\ReservationService;
use Illuminate\Support\Facades\Auth;

class ReservationController extends Controller
{
    protected $reservationService;

    public function __construct(ReservationService $reservationService)
    {
        $this->reservationService = $reservationService;
    }

    public function index()
    {
        $reservations = $this->reservationService->getAll();
        return ReservationResource::collection($reservations);
    }

    public function store(StoreReservationRequest $request)
    {
        $reservation = $this->reservationService->create($request->validated(), Auth::id());
        return new ReservationResource($reservation);
    }

    public function show(Reservation $reservation)
    {
        $reservation = $this->reservationService->getById($reservation);
        return new ReservationResource($reservation);
    }

    public function update(UpdateReservationRequest $request, Reservation $reservation)
    {
        $reservation = $this->reservationService->update($reservation, $request->validated());
        return new ReservationResource($reservation);
    }

    public function destroy(Reservation $reservation)
    {
        $this->reservationService->delete($reservation);
        return response()->json(null, 204);
    }
}

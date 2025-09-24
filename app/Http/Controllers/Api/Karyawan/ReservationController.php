<?php

namespace App\Http\Controllers\Api\Karyawan;

use App\Http\Controllers\Controller;
use App\Http\Requests\Karyawan\ReservationStoreRequest;
use App\Http\Requests\Karyawan\ReservationCancelRequest;
use App\Models\Reservation;
use App\Http\Resources\Karyawan\ReservationResource;
use App\Services\Karyawan\ReservationService;
use App\Mail\ReservationCanceledByUserMail;
use Illuminate\Support\Facades\Mail;
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
    ]);

    return new ReservationResource($reservation);
}

public function cancel(ReservationCancelRequest $request, $id)
{
    $reservation = Reservation::where('user_id', Auth::id())
        ->findOrFail($id);

    // ❌ Cegah cancel kalau status sudah final
    if (! in_array($reservation->status, ['pending', 'approved'])) {
        return response()->json([
            'message' => 'Reservasi sudah tidak bisa dibatalkan.'
        ], 422);
    }

$reservation->update([
    'status' => 'canceled',
    'reason' => $request->validated()['reason'],
]);

// ✅ Kirim email notifikasi ke admin
$adminEmail = "admin@reservasi.com";
Mail::to($adminEmail)->send(new ReservationCanceledByUserMail($reservation));

return response()->json([
    'message' => 'Reservasi berhasil dibatalkan & notifikasi dikirim ke admin.',
    'data'    => $reservation->fresh(),
]);
}
}

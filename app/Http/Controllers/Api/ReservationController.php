<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

// Services
use App\Services\Admin\ReservationService as AdminReservationService;
use App\Services\Karyawan\ReservationService as KaryawanReservationService;

// Requests
use App\Http\Requests\Admin\ReservationUpdateRequest;
use App\Http\Requests\Karyawan\ReservationStoreRequest;
use App\Http\Requests\Karyawan\ReservationCancelRequest;

// Resources
use App\Http\Resources\Admin\ReservationResource as AdminReservationResource;
use App\Http\Resources\Karyawan\ReservationResource as KaryawanReservationResource;

// Mail
use App\Mail\ReservationCanceledByUserMail;

class ReservationController extends Controller
{
    protected $adminService;
    protected $karyawanService;

    public function __construct(
        AdminReservationService $adminService,
        KaryawanReservationService $karyawanService
    ) {
        $this->adminService    = $adminService;
        $this->karyawanService = $karyawanService;
    }

    /**
     * GET /reservations
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->hasRole('admin')) {
            $reservations = $this->adminService->getAll();
            return AdminReservationResource::collection($reservations);
        }

        if ($user->hasRole('karyawan')) {
            $reservations = $this->karyawanService->getUserReservations($user->id);
            return KaryawanReservationResource::collection($reservations);
        }

        abort(403, 'Anda tidak punya akses.');
    }

    /**
     * GET /reservations/{id}
     */
    public function show($id)
    {
        $user = Auth::user();

        if ($user->hasRole('admin')) {
            $reservation = $this->adminService->getById($id);
            return new AdminReservationResource($reservation);
        }

        if ($user->hasRole('karyawan')) {
            $reservation = $this->karyawanService->getUserReservationById($user->id, $id);
            return new KaryawanReservationResource($reservation);
        }

        abort(403, 'Anda tidak punya akses.');
    }

    /**
     * POST /reservations
     * Hanya Karyawan
     */
    public function store(ReservationStoreRequest $request)
    {
        $user = Auth::user();

        if (! $user->hasRole('karyawan')) {
            abort(403, 'Hanya karyawan yang bisa membuat reservasi.');
        }

        $reservation = $this->karyawanService->create([
            'user_id'       => $user->id,
            'room_id'       => $request->room_id,
            'tanggal'       => $request->tanggal,
            'hari'          => Carbon::parse($request->tanggal)->locale('id')->dayName,
            'waktu_mulai'   => $request->waktu_mulai,
            'waktu_selesai' => $request->waktu_selesai,
        ]);

        return new KaryawanReservationResource($reservation);
    }

    /**
     * PUT /reservations/{id}
     * Admin update status (approve/reject)
     */
    public function update(ReservationUpdateRequest $request, $id)
    {
        $user = Auth::user();

        if (! $user->hasRole('admin')) {
            abort(403, 'Hanya admin yang bisa mengubah reservasi.');
        }

        $data = $request->validated();

        $reservation = $this->adminService->updateStatus($id, $data);

        return new AdminReservationResource($reservation);
    }

    /**
     * DELETE /reservations/{id}
     * Hanya Admin
     */
    public function destroy($id)
    {
        $user = Auth::user();

        if (! $user->hasRole('admin')) {
            abort(403, 'Hanya admin yang bisa menghapus reservasi.');
        }

        $this->adminService->delete($id);

        return response()->json([
            'message' => 'Reservasi berhasil dihapus.',
        ]);
    }

    /**
     * PUT /reservations/{id}/cancel
     * Hanya Karyawan
     */
    public function cancel(ReservationCancelRequest $request, $id)
    {
        $user = Auth::user();

        if (! $user->hasRole('karyawan')) {
            abort(403, 'Hanya karyawan yang bisa membatalkan reservasi.');
        }

        $reservation = $this->karyawanService->cancel($id, $user->id, $request->validated()['reason'] ?? null);

        $adminEmail = "admin@reservasi.com";
        Mail::to($adminEmail)->send(new ReservationCanceledByUserMail($reservation));

        return new KaryawanReservationResource($reservation);
    }
}

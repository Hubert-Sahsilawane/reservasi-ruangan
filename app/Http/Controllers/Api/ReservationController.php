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

    /* -------------------------------------------------------------------------- */
    /*  GET /reservations                                                        */
    /* -------------------------------------------------------------------------- */
    public function index(Request $request)
    {
        $user = Auth::user();

        $filters = [
            'tanggal'       => $request->query('tanggal'),
            'hari'          => $request->query('hari'),
            'waktu_mulai'   => $request->query('waktu_mulai'),
            'waktu_selesai' => $request->query('waktu_selesai'),
            'status'        => $request->query('status'),
        ];

        $perPage = $request->query('per_page', 10);

        try {
            if ($user->hasRole('admin')) {
                $query = \App\Models\Reservation::query()->orderBy('tanggal', 'desc');

                $query
                    ->when($filters['tanggal'], fn($q) => $q->whereDate('tanggal', $filters['tanggal']))
                    ->when($filters['hari'], fn($q) => $q->where('hari', $filters['hari']))
                    ->when($filters['waktu_mulai'], fn($q) => $q->whereTime('waktu_mulai', '>=', $filters['waktu_mulai']))
                    ->when($filters['waktu_selesai'], fn($q) => $q->whereTime('waktu_selesai', '<=', $filters['waktu_selesai']))
                    ->when($filters['status'], fn($q) => $q->where('status', $filters['status']));

                $reservations = $query->paginate($perPage)->appends($request->query());

                return $this->responseSuccess(
                    'Data reservasi berhasil diambil.',
                    AdminReservationResource::collection($reservations)
                );
            }

            if ($user->hasRole('karyawan')) {
                $query = \App\Models\Reservation::where('user_id', $user->id)
                    ->orderBy('tanggal', 'desc');

                $query
                    ->when($filters['tanggal'], fn($q) => $q->whereDate('tanggal', $filters['tanggal']))
                    ->when($filters['hari'], fn($q) => $q->where('hari', $filters['hari']))
                    ->when($filters['waktu_mulai'], fn($q) => $q->whereTime('waktu_mulai', '>=', $filters['waktu_mulai']))
                    ->when($filters['waktu_selesai'], fn($q) => $q->whereTime('waktu_selesai', '<=', $filters['waktu_selesai']))
                    ->when($filters['status'], fn($q) => $q->where('status', $filters['status']));

                $reservations = $query->paginate($perPage)->appends($request->query());

                return $this->responseSuccess(
                    'Data reservasi berhasil diambil.',
                    KaryawanReservationResource::collection($reservations)
                );
            }

            return $this->responseError('Anda tidak memiliki akses.', 403);
        } catch (\Throwable $th) {
            return $this->responseError('Terjadi kesalahan server: ' . $th->getMessage(), 500);
        }
    }

    /* -------------------------------------------------------------------------- */
    /*  GET /reservations/{id}                                                   */
    /* -------------------------------------------------------------------------- */
    public function show($id)
    {
        $user = Auth::user();

        try {
            if ($user->hasRole('admin')) {
                $reservation = $this->adminService->getById($id);
                return $this->responseSuccess(
                    'Detail reservasi berhasil diambil.',
                    new AdminReservationResource($reservation)
                );
            }

            if ($user->hasRole('karyawan')) {
                $reservation = $this->karyawanService->getUserReservationById($user->id, $id);
                return $this->responseSuccess(
                    'Detail reservasi berhasil diambil.',
                    new KaryawanReservationResource($reservation)
                );
            }

            return $this->responseError('Anda tidak memiliki akses.', 403);
        } catch (\Throwable $th) {
            return $this->responseError('Terjadi kesalahan: ' . $th->getMessage(), 500);
        }
    }

    /* -------------------------------------------------------------------------- */
    /*  POST /karyawan/reservations                                              */
    /* -------------------------------------------------------------------------- */
    public function store(ReservationStoreRequest $request)
    {
        $user = Auth::user();

        if (! $user->hasRole('karyawan')) {
            return $this->responseError('Hanya karyawan yang bisa membuat reservasi.', 403);
        }

        try {
            $reservation = $this->karyawanService->create([
                'user_id'       => $user->id,
                'room_id'       => $request->room_id,
                'tanggal'       => $request->tanggal,
                'hari'          => Carbon::parse($request->tanggal)->locale('id')->dayName,
                'waktu_mulai'   => $request->waktu_mulai,
                'waktu_selesai' => $request->waktu_selesai,
                'reason'        => $request->reason ?? '-',
            ]);

            return $this->responseSuccess(
                'Reservasi berhasil dibuat.',
                new KaryawanReservationResource($reservation)
            );
        } catch (\Throwable $th) {
            return $this->responseError('Gagal membuat reservasi: ' . $th->getMessage(), 500);
        }
    }

    /* -------------------------------------------------------------------------- */
    /*  PUT /admin/reservations/{id}/approve                                     */
    /* -------------------------------------------------------------------------- */
    public function approve(Request $request, $id)
    {
        $user = Auth::user();

        if (! $user->hasRole('admin')) {
            return $this->responseError('Hanya admin yang bisa menyetujui reservasi.', 403);
        }

        try {
            $reservation = $this->adminService->updateStatus($id, [
                'status' => 'approved',
                'reason' => $request->reason ?? 'Disetujui oleh admin',
            ]);

            return $this->responseSuccess(
                'Reservasi berhasil disetujui.',
                new AdminReservationResource($reservation)
            );
        } catch (\Throwable $th) {
            return $this->responseError('Gagal menyetujui reservasi: ' . $th->getMessage(), 500);
        }
    }

    /* -------------------------------------------------------------------------- */
    /*  PUT /admin/reservations/{id}/reject                                      */
    /* -------------------------------------------------------------------------- */
    public function rejected(Request $request, $id)
    {
        $user = Auth::user();

        if (! $user->hasRole('admin')) {
            return $this->responseError('Hanya admin yang bisa menolak reservasi.', 403);
        }

        try {
            $reservation = $this->adminService->updateStatus($id, [
                'status' => 'rejected',
                'reason' => $request->reason ?? 'Tidak ada alasan diberikan',
            ]);

            return $this->responseSuccess(
                'Reservasi berhasil ditolak.',
                new AdminReservationResource($reservation)
            );
        } catch (\Throwable $th) {
            return $this->responseError('Gagal menolak reservasi: ' . $th->getMessage(), 500);
        }
    }

    /* -------------------------------------------------------------------------- */
    /*  DELETE /admin/reservations/{id}                                          */
    /* -------------------------------------------------------------------------- */
    public function destroy($id)
    {
        $user = Auth::user();

        if (! $user->hasRole('admin')) {
            return $this->responseError('Hanya admin yang bisa menghapus reservasi.', 403);
        }

        try {
            $this->adminService->delete($id);
            return $this->responseSuccess('Reservasi berhasil dihapus.');
        } catch (\Throwable $th) {
            return $this->responseError('Gagal menghapus reservasi: ' . $th->getMessage(), 500);
        }
    }

    /* -------------------------------------------------------------------------- */
    /*  PUT /karyawan/reservations/{id}/cancel                                   */
    /* -------------------------------------------------------------------------- */
    public function cancel(ReservationCancelRequest $request, $id)
    {
        $user = Auth::user();

        if (! $user->hasRole('karyawan')) {
            return $this->responseError('Hanya karyawan yang bisa membatalkan reservasi.', 403);
        }

        try {
            $reservation = $this->karyawanService->cancel(
                $id,
                $user->id,
                $request->validated()['reason'] ?? 'Dibatalkan oleh pengguna'
            );

            Mail::to("admin@reservasi.com")->send(new ReservationCanceledByUserMail($reservation));

            return $this->responseSuccess(
                'Reservasi berhasil dibatalkan.',
                new KaryawanReservationResource($reservation)
            );
        } catch (\Throwable $th) {
            return $this->responseError('Gagal membatalkan reservasi: ' . $th->getMessage(), 500);
        }
    }

    /* -------------------------------------------------------------------------- */
    /*  Helper Response Methods                                                  */
    /* -------------------------------------------------------------------------- */
    private function responseSuccess(string $message, $data = null, int $statusCode = 200)
    {
        $response = ['status' => 'success', 'message' => $message];
        if (!is_null($data)) $response['data'] = $data;

        return response()->json($response, $statusCode);
    }

    private function responseError(string $message, int $statusCode = 400)
    {
        return response()->json([
            'status' => 'error',
            'message' => $message
        ], $statusCode);
    }
}

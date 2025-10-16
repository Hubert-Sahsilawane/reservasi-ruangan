<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Spatie\Activitylog\Models\Activity;
use function activity;

// Models
use App\Models\Reservation;

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
        if (! $user) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Token tidak valid atau kadaluarsa.'
            ], 401);
        }

        $filters = [
            'page'          => $request->query('page', 1),
            'per_page'      => $request->query('per_page', 10),
            'tanggal'       => $request->query('tanggal', null),
            'hari'          => $request->query('hari', null),
            'waktu_mulai'   => $request->query('waktu_mulai', null),
            'waktu_selesai' => $request->query('waktu_selesai', null),
            'status'        => $request->query('status', null),
        ];

        $page = max(1, (int) $request->query('page', 1));
        $perPage = (int) $request->query('per_page', 10);
        if ($perPage <= 0) $perPage = 10;
        $perPage = min(10, $perPage);

        $validDays = ['senin','selasa','rabu','kamis','jumat','sabtu','minggu'];
        if (!empty($filters['hari']) && !in_array(strtolower($filters['hari']), $validDays)) {
            return response()->json([
                'status'  => 'failed',
                'message' => 'Hari tidak valid. Gunakan Senin sampai Minggu.',
                'data'    => null
            ], 422);
        }

        $validStatus = ['approved', 'rejected', 'pending'];
        if (!empty($filters['status']) && !in_array(strtolower($filters['status']), $validStatus)) {
            return response()->json([
                'status'  => 'failed',
                'message' => 'Status tidak valid. Gunakan approved / rejected / pending.',
                'data'    => null
            ], 422);
        }

        if (!empty($filters['tanggal'])) {
            try {
                $dt = Carbon::createFromFormat('Y-m-d', $filters['tanggal']);
            } catch (\Throwable $e) {
                return response()->json([
                    'status'  => 'failed',
                    'message' => 'Tanggal tidak valid. Format harus YYYY-MM-DD.',
                    'data'    => null
                ], 422);
            }
        }

        $timeRegex = '/^([01]\d|2[0-3]):[0-5]\d$/';
        if (!empty($filters['waktu_mulai'])) {
            if (!preg_match($timeRegex, $filters['waktu_mulai'])) {
                return response()->json([
                    'status'  => 'failed',
                    'message' => 'Waktu mulai tidak valid (format HH:MM).',
                    'data'    => null
                ], 422);
            }
            if (empty($filters['waktu_selesai'])) {
                return response()->json([
                    'status'  => 'failed',
                    'message' => 'Waktu selesai harus diisi juga.',
                    'data'    => null
                ], 422);
            }
        }
        if (!empty($filters['waktu_selesai']) && !preg_match($timeRegex, $filters['waktu_selesai'])) {
            return response()->json([
                'status'  => 'failed',
                'message' => 'Waktu selesai tidak valid (format HH:MM).',
                'data'    => null
            ], 422);
        }

        try {
            $query = Reservation::query()
                ->when($user->hasRole('karyawan'), fn($q) => $q->where('user_id', $user->id))
                ->when(filter_var($request->query('scramble'), FILTER_VALIDATE_BOOLEAN), fn($q) => $q->inRandomOrder())
                ->orderBy('id', 'asc')
                ->when($filters['tanggal'], fn($q) => $q->whereDate('tanggal', $filters['tanggal']))
                ->when($filters['hari'], fn($q) => $q->where('hari', $filters['hari']))
                ->when($filters['waktu_mulai'], fn($q) => $q->whereTime('waktu_mulai', '>=', $filters['waktu_mulai']))
                ->when($filters['waktu_selesai'], fn($q) => $q->whereTime('waktu_selesai', '<=', $filters['waktu_selesai']))
                ->when($filters['status'], fn($q) => $q->where('status', $filters['status']));

            $reservations = $query->paginate($perPage, ['*'], 'page', $page)->appends($request->query());

            return response()->json([
                'status'  => 'success',
                'message' => 'Data reservasi berhasil diambil.',
                'data'    => $user->hasRole('admin')
                    ? AdminReservationResource::collection($reservations->items())
                    : KaryawanReservationResource::collection($reservations->items()),
                'meta' => [
                    'current_page' => $reservations->currentPage(),
                    'per_page'     => $reservations->perPage(),
                    'total'        => $reservations->total(),
                    'last_page'    => $reservations->lastPage(),
                    'from'         => $reservations->firstItem(),
                    'to'           => $reservations->lastItem(),
                ],
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Terjadi kesalahan server: ' . $th->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function show($id)
{
    try {
        $user = Auth::user();
        $reservation = Reservation::findOrFail($id);

        // Batasi akses: karyawan hanya bisa melihat reservasi miliknya sendiri
        if ($user->hasRole('karyawan') && $reservation->user_id !== $user->id) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Anda tidak memiliki akses ke reservasi ini.',
            ], 403);
        }

        $resource = $user->hasRole('admin')
            ? new AdminReservationResource($reservation)
            : new KaryawanReservationResource($reservation);

        return response()->json([
            'status' => 'success',
            'message' => 'Detail reservasi berhasil diambil.',
            'data' => $resource,
        ], 200);

    } catch (\Throwable $th) {
        return response()->json([
            'status' => 'failed',
            'message' => 'Terjadi kesalahan server: ' . $th->getMessage(),
        ], 500);
    }
}


    /* -------------------------------------------------------------------------- */
    /*  GET /reservations/history (Karyawan)                                     */
    /* -------------------------------------------------------------------------- */
    public function history(Request $request)
{
    try {
        $user = Auth::user();

        // 🔒 Hanya karyawan yang bisa lihat histori miliknya sendiri
        if (! $user->hasRole('karyawan')) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Hanya karyawan yang dapat melihat riwayat reservasi.',
                'data' => null,
            ], 403);
        }

        $perPage = (int) $request->query('per_page', 10);
        $page    = max(1, (int) $request->query('page', 1));

        $reservations = Reservation::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'status'  => 'success',
            'message' => 'Riwayat reservasi berhasil diambil.',
            'data'    => KaryawanReservationResource::collection($reservations->items()),
            'meta'    => [
                'current_page' => $reservations->currentPage(),
                'per_page'     => $reservations->perPage(),
                'total'        => $reservations->total(),
                'last_page'    => $reservations->lastPage(),
                'from'         => $reservations->firstItem(),
                'to'           => $reservations->lastItem(),
            ],
        ], 200);

    } catch (\Throwable $th) {
        return response()->json([
            'status'  => 'failed',
            'message' => 'Terjadi kesalahan server: ' . $th->getMessage(),
        ], 500);
    }
}

    /* -------------------------------------------------------------------------- */
    /*  GET /reservations/activity-logs                                          */
    /* -------------------------------------------------------------------------- */
    public function activityLogs(Request $request)
{
    $user = Auth::user();

    $query = Activity::query()
        ->where('log_name', 'reservasi')
        ->when($user->hasRole('karyawan'), fn($q) => $q->where('causer_id', $user->id))
        ->orderBy('created_at', 'desc');

    $logs = $query->paginate(10);

    return response()->json([
        'status'  => 'success',
        'message' => 'Riwayat aktivitas reservasi berhasil diambil.',
        'data'    => $logs->items(),
        'meta'    => [
            'current_page' => $logs->currentPage(),
            'per_page'     => $logs->perPage(),
            'total'        => $logs->total(),
            'last_page'    => $logs->lastPage(),
            'from'         => $logs->firstItem(),
            'to'           => $logs->lastItem(),
        ],
    ]);
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

            activity()
                ->performedOn($reservation)
                ->causedBy($user)
                ->withProperties(['room_id' => $reservation->room_id])
                ->log("Membuat reservasi baru untuk ruangan {$reservation->room->name}");

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
    public function update(Request $request, $id)
    {
        $user = Auth::user();

        if (!$user->hasRole('admin')) {
            return $this->responseError('Hanya admin yang bisa memperbarui reservasi.', 403);
        }

        $status = $request->input('status');
        $reason = $request->input('reason');

        if (!in_array($status, ['approved', 'rejected'])) {
            return $this->responseError('Status tidak valid. Gunakan approved atau rejected.', 422);
        }

        try {
            $reservation = $this->adminService->updateStatus($id, [
                'status' => $status,
                'reason' => $reason ?? ($status === 'approved' ? 'Disetujui oleh admin' : 'Ditolak oleh admin'),
            ]);

            activity()
                ->performedOn($reservation)
                ->causedBy($user)
                ->withProperties(['status' => $status])
                ->log("Reservasi diubah status menjadi {$status}");

            return $this->responseSuccess(
                'Reservasi berhasil diperbarui.',
                new AdminReservationResource($reservation)
            );
        } catch (\Throwable $th) {
            return $this->responseError('Gagal memperbarui reservasi: ' . $th->getMessage(), 500);
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
            $reservation = Reservation::findOrFail($id);
            $this->adminService->delete($id);

            activity()
                ->performedOn($reservation)
                ->causedBy($user)
                ->log("Menghapus reservasi untuk ruangan {$reservation->room->name}");

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

            activity()
                ->performedOn($reservation)
                ->causedBy($user)
                ->withProperties(['alasan' => $request->reason])
                ->log("Membatalkan reservasi untuk ruangan {$reservation->room->name}");

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

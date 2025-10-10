<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// Services
use App\Services\FixedScheduleService;

// Requests
use App\Http\Requests\FixedScheduleRequest;

// Resources
use App\Http\Resources\Admin\FixedScheduleResource as AdminResource;
use App\Http\Resources\Karyawan\FixedScheduleResource as KaryawanResource;

class FixedScheduleController extends Controller
{
    protected $service;

    public function __construct(FixedScheduleService $service)
    {
        $this->service = $service;
    }

    /* -------------------------------------------------------------------------- */
    /*  GET /fixed-schedules                                                     */
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

    // Ambil filter
    $filters = [
        'page'          => $request->query('page', 1),
        'per_page'      => $request->query('per_page', 10),
        'tanggal'       => $request->query('tanggal', null),
        'hari'          => $request->query('hari', null),
        'waktu_mulai'   => $request->query('waktu_mulai', null),
        'waktu_selesai' => $request->query('waktu_selesai', null),
    ];

    // Pagination setup
    $page = max(1, (int) $filters['page']);
    $perPage = (int) $filters['per_page'];
    if ($perPage <= 0) $perPage = 10;
    $perPage = min(10, $perPage); // batas maksimum 10 per halaman

    // Validasi hari, status, tanggal, waktu
    $validDays = ['senin','selasa','rabu','kamis','jumat','sabtu','minggu'];
    if (!empty($filters['hari']) && !in_array(strtolower($filters['hari']), $validDays)) {
        return response()->json([
            'status'  => 'failed',
            'message' => 'Hari tidak valid. Gunakan Senin sampai Minggu.',
            'data'    => null
        ], 422);
    }

    if (!empty($filters['tanggal'])) {
        try {
            \Carbon\Carbon::createFromFormat('Y-m-d', $filters['tanggal']);
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

    // -------------------------
    // QUERY + PAGINATION
    // -------------------------
    try {
        $query = \App\Models\FixedSchedule::query()
            ->with(['room', 'user'])
            ->when($user->hasRole('karyawan'), fn($q) => $q->where('user_id', $user->id))
            ->orderBy('id', 'asc')
            ->when($filters['tanggal'], fn($q) => $q->whereDate('tanggal', $filters['tanggal']))
            ->when($filters['hari'], fn($q) => $q->where('hari', $filters['hari']))
            ->when($filters['waktu_mulai'], fn($q) => $q->whereTime('waktu_mulai', '>=', $filters['waktu_mulai']))
            ->when($filters['waktu_selesai'], fn($q) => $q->whereTime('waktu_selesai', '<=', $filters['waktu_selesai']));

        $fixedSchedules = $query->paginate($perPage, ['*'], 'page', $page)->appends($request->query());

        // Resource sesuai role
        $resource = $user->hasRole('admin')
            ? \App\Http\Resources\Admin\FixedScheduleResource::collection($fixedSchedules->items())
            : \App\Http\Resources\Karyawan\FixedScheduleResource::collection($fixedSchedules->items());

        return response()->json([
            'status'  => 'success',
            'message' => 'Data jadwal tetap berhasil diambil.',
            'data'    => $resource,
            'meta' => [
                'current_page' => $fixedSchedules->currentPage(),
                'per_page'     => $fixedSchedules->perPage(),
                'total'        => $fixedSchedules->total(),
                'last_page'    => $fixedSchedules->lastPage(),
                'from'         => $fixedSchedules->firstItem(),
                'to'           => $fixedSchedules->lastItem(),
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



    /* -------------------------------------------------------------------------- */
    /*  GET /fixed-schedules/{id}                                                */
    /* -------------------------------------------------------------------------- */
    public function show($id)
    {
        $user = Auth::user();

        try {
            $schedule = $this->service->getById($id);

            return $this->responseSuccess(
                'Detail jadwal tetap berhasil diambil.',
                $user->hasRole('admin')
                    ? new AdminResource($schedule)
                    : new KaryawanResource($schedule)
            );
        } catch (\Throwable $th) {
            return $this->responseError('Data tidak ditemukan: ' . $th->getMessage(), 404);
        }
    }

    /* -------------------------------------------------------------------------- */
    /*  POST /fixed-schedules                                                    */
    /* -------------------------------------------------------------------------- */
    public function store(FixedScheduleRequest $request)
    {
        $user = Auth::user();

        if (! $user->hasRole('admin')) {
            return $this->responseError('Hanya admin yang bisa menambahkan jadwal tetap.', 403);
        }

        try {
            $schedule = $this->service->create($request->validated());

            return $this->responseSuccess(
                'Jadwal tetap berhasil dibuat.',
                new AdminResource($schedule)
            );
        } catch (\Throwable $th) {
            return $this->responseError('Gagal membuat jadwal tetap: ' . $th->getMessage(), 500);
        }
    }

    /* -------------------------------------------------------------------------- */
    /*  PUT /fixed-schedules/{id}                                                */
    /* -------------------------------------------------------------------------- */
    public function update(FixedScheduleRequest $request, $id)
    {
        $user = Auth::user();

        if (! $user->hasRole('admin')) {
            return $this->responseError('Hanya admin yang bisa memperbarui jadwal tetap.', 403);
        }

        try {
            $schedule = $this->service->update($id, $request->validated());

            return $this->responseSuccess(
                'Jadwal tetap berhasil diperbarui.',
                new AdminResource($schedule)
            );
        } catch (\Throwable $th) {
            return $this->responseError('Gagal memperbarui jadwal tetap: ' . $th->getMessage(), 500);
        }
    }

    /* -------------------------------------------------------------------------- */
    /*  DELETE /fixed-schedules/{id}                                             */
    /* -------------------------------------------------------------------------- */
    public function destroy($id)
    {
        $user = Auth::user();

        if (! $user->hasRole('admin')) {
            return $this->responseError('Hanya admin yang bisa menghapus jadwal tetap.', 403);
        }

        try {
            $this->service->delete($id);

            return $this->responseSuccess('Jadwal tetap berhasil dihapus.');
        } catch (\Throwable $th) {
            return $this->responseError('Gagal menghapus jadwal tetap: ' . $th->getMessage(), 500);
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
            'status'  => 'error',
            'message' => $message
        ], $statusCode);
    }
}

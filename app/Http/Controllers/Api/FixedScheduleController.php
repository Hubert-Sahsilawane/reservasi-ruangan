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

    $filters = [
        'room_id'       => $request->query('room_id'),
        'tanggal'       => $request->query('tanggal'),
        'waktu_mulai'   => $request->query('waktu_mulai'),
        'waktu_selesai' => $request->query('waktu_selesai'),
    ];

    // 🧭 Pagination
    $page = (int) max(1, $request->query('page', 1));
    $perPage = (int) $request->query('per_page', 10);
    $perPage = min(10, max(1, $perPage));

    /* --------------------------------------------------------------------------
       VALIDASI INPUT
    -------------------------------------------------------------------------- */

    // ✅ Validasi ROOM ID
    if (!empty($filters['room_id'])) {
        $roomExists = \App\Models\Room::find($filters['room_id']);
        if (!$roomExists) {
            return response()->json([
                'status'  => 'failed',
                'message' => 'Ruangan tidak ditemukan.',
                'data'    => null,
            ], 404);
        }
    }

    // ✅ Validasi TANGGAL (optional, tapi jika diisi harus format valid)
    if (!empty($filters['tanggal']) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $filters['tanggal'])) {
        return response()->json([
            'status'  => 'failed',
            'message' => 'Tanggal tidak valid.',
            'data'    => null,
        ], 400);
    }

    // ✅ Validasi WAKTU MULAI & SELESAI (harus berpasangan)
    if (!empty($filters['waktu_mulai']) && empty($filters['waktu_selesai'])) {
        return response()->json([
            'status'  => 'failed',
            'message' => 'Waktu selesai harus diisi juga.',
            'data'    => null,
        ], 400);
    }

    if (empty($filters['waktu_mulai']) && !empty($filters['waktu_selesai'])) {
        return response()->json([
            'status'  => 'failed',
            'message' => 'Waktu mulai harus diisi juga.',
            'data'    => null,
        ], 400);
    }

    // ✅ Validasi FORMAT JAM (jika keduanya diisi)
    $timeRegex = '/^(?:[01]\d|2[0-3]):[0-5]\d$/';
    if (!empty($filters['waktu_mulai']) && !preg_match($timeRegex, $filters['waktu_mulai'])) {
        return response()->json([
            'status'  => 'failed',
            'message' => 'Format waktu mulai tidak valid (gunakan format HH:mm).',
            'data'    => null,
        ], 400);
    }

    if (!empty($filters['waktu_selesai']) && !preg_match($timeRegex, $filters['waktu_selesai'])) {
        return response()->json([
            'status'  => 'failed',
            'message' => 'Format waktu selesai tidak valid (gunakan format HH:mm).',
            'data'    => null,
        ], 400);
    }

    /* --------------------------------------------------------------------------
       QUERY DATA
    -------------------------------------------------------------------------- */
    try {
        $query = \App\Models\FixedSchedule::with(['room', 'user'])
            ->when(!empty($filters['room_id']), fn($q) => $q->where('room_id', $filters['room_id']))
            ->when(!empty($filters['tanggal']), fn($q) => $q->whereDate('tanggal', $filters['tanggal']))
            ->when(!empty($filters['waktu_mulai']), fn($q) => $q->whereTime('waktu_mulai', '>=', $filters['waktu_mulai']))
            ->when(!empty($filters['waktu_selesai']), fn($q) => $q->whereTime('waktu_selesai', '<=', $filters['waktu_selesai']))
            ->orderBy('id', 'asc');

        $schedules = $query->paginate($perPage, ['*'], 'page', $page)->appends($request->query());

        if ($schedules->isEmpty()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Data tidak ditemukan.',
                'data'    => null,
            ]);
        }

        $resource = $user->hasRole('admin')
            ? \App\Http\Resources\Admin\FixedScheduleResource::collection($schedules)
            : \App\Http\Resources\Karyawan\FixedScheduleResource::collection($schedules);

        return response()->json([
            'status'  => 'success',
            'message' => 'Daftar jadwal tetap berhasil diambil.',
            'data'    => $resource,
        ]);
    } catch (\Throwable $th) {
        return response()->json([
            'status'  => 'failed',
            'message' => 'Terjadi kesalahan server: ' . $th->getMessage(),
            'data'    => null,
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

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
            'search'        => $request->query('search'),
            'room_id'       => $request->query('room_id'),
            'tanggal'       => $request->query('tanggal'),
            'waktu_mulai'   => $request->query('waktu_mulai'),
            'waktu_selesai' => $request->query('waktu_selesai'),
        ];

        $perPage = $request->query('per_page', 10);

        try {
            $schedules = $this->service->getAll($filters, $perPage);

            return $this->responseSuccess(
                'Daftar jadwal tetap berhasil diambil.',
                $user->hasRole('admin')
                    ? AdminResource::collection($schedules)
                    : KaryawanResource::collection($schedules)
            );
        } catch (\Throwable $th) {
            return $this->responseError('Terjadi kesalahan server: ' . $th->getMessage(), 500);
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

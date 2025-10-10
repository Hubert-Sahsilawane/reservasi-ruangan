<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RoomRequest;
use App\Http\Resources\Admin\RoomResource as AdminRoomResource;
use App\Http\Resources\Karyawan\RoomResource as KaryawanRoomResource;
use App\Services\RoomService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class RoomController extends Controller
{
    protected $roomService;

    public function __construct(RoomService $roomService)
    {
        $this->roomService = $roomService;
    }

public function index(Request $request)
{
    try {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'status'  => 'failed',
                'message' => 'Token tidak valid atau kadaluarsa.',
                'data'    => null,
            ], 422);
        }

        $filters = [
            'kapasitas' => $request->query('kapasitas'),
            'status'    => $request->query('status'),
            'page'      => $request->query('page', 1),
            'per_page'  => $request->query('per_page', 10),
        ];

        // ✅ Validasi STATUS (harus aktif / non-aktif)
        if (!empty($filters['status']) && !in_array(strtolower($filters['status']), ['aktif', 'non-aktif'])) {
            return response()->json([
                'status'  => 'failed',
                'message' => 'Statusnya wajib AKTIF dan NON-AKTIF',
                'data'    => null,
            ], 422);
        }

        // ✅ Validasi KAPASITAS (harus angka)
        if (!empty($filters['kapasitas']) && !is_numeric($filters['kapasitas'])) {
            return response()->json([
                'status'  => 'failed',
                'message' => 'Data tidak valid, kapasitas harus ditulis dengan angka',
                'data'    => null,
            ], 422);
        }

        // ✅ Gunakan per_page dari request jika dikirim, default 10
        $perPage = is_numeric($filters['per_page']) && $filters['per_page'] > 0
            ? (int) $filters['per_page']
            : 10;

        // ✅ Ambil data room dengan pagination
        $rooms = \App\Models\Room::query()
            ->when($filters['kapasitas'], fn($q) => $q->where('kapasitas', $filters['kapasitas']))
            ->when($filters['status'], fn($q) => $q->where('status', $filters['status']))
            ->orderBy('id', 'asc')
            ->paginate($perPage, ['*'], 'page', $filters['page']);

        // ✅ Jika data kosong
        if ($rooms->isEmpty()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Data tidak ditemukan',
                'data'    => null,
            ], 200);
        }

        // ✅ Tentukan resource sesuai role
        $resource = $user->hasRole('admin')
            ? AdminRoomResource::collection($rooms->items())
            : KaryawanRoomResource::collection($rooms->items());

        return response()->json([
            'status'  => 'success',
            'message' => 'Data ruangan berhasil ditampilkan',
            'data'    => $resource,
            'meta'    => [
                'current_page' => $rooms->currentPage(),
                'per_page'     => $rooms->perPage(),
                'total'        => $rooms->total(),
                'last_page'    => $rooms->lastPage(),
                'from'         => $rooms->firstItem(),
                'to'           => $rooms->lastItem(),
            ]
        ], 200);
    } catch (\Throwable $th) {
        return response()->json([
            'status'  => 'failed',
            'message' => 'Terjadi kesalahan server: ' . $th->getMessage(),
            'data'    => null,
        ], 500);
    }
}

    /**
     * GET /rooms/{id}
     * Menampilkan detail ruangan
     */
    public function show($id)
    {
        $room = $this->roomService->find($id);

        $resource = Auth::user()->hasRole('admin')
            ? new AdminRoomResource($room)
            : new KaryawanRoomResource($room);

        return $resource->additional([
            'status' => 'success',
        ]);
    }

    /**
     * POST /rooms
     * Menambahkan ruangan (admin only)
     */
    public function store(RoomRequest $request)
    {
        if (!Auth::user()->hasRole('admin')) {
            return response()->json(['status' => 'failed', 'message' => 'Hanya admin yang dapat menambah ruangan.'], 403);
        }

        $room = $this->roomService->create($request->validated());

        return (new AdminRoomResource($room))->additional([
            'status'  => 'success',
            'message' => 'Ruangan berhasil ditambahkan.',
        ]);
    }

    /**
     * PUT /rooms/{id}
     * Mengubah data ruangan (admin only)
     */
    public function update(RoomRequest $request, $id)
    {
        if (!Auth::user()->hasRole('admin')) {
            return response()->json(['status' => 'failed', 'message' => 'Hanya admin yang dapat mengubah ruangan.'], 403);
        }

        $room = $this->roomService->update($id, $request->validated());

        return (new AdminRoomResource($room))->additional([
            'status'  => 'success',
            'message' => 'Data ruangan berhasil diperbarui.',
        ]);
    }

    /**
     * DELETE /rooms/{id}
     * Menghapus ruangan (admin only)
     */
    public function destroy($id)
    {
        if (!Auth::user()->hasRole('admin')) {
            return response()->json(['status' => 'failed', 'message' => 'Hanya admin yang dapat menghapus ruangan.'], 403);
        }

        try {
            $this->roomService->delete($id);
            return response()->json([
                'status'  => 'success',
                'message' => 'Ruangan berhasil dihapus.',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'failed',
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}

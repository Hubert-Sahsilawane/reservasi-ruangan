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

    /**
     * GET /rooms
     * Menampilkan daftar ruangan dengan filter dan pagination
     */
    public function index(Request $request)
    {
        $filters = [
            'name'      => $request->query('name'),
            'kapasitas' => $request->query('kapasitas'),
            'status'    => $request->query('status'),
        ];

        $perPage = $request->query('per_page', 10);

        $rooms = $this->roomService->getAll($filters, $perPage);

        $resource = Auth::user()->hasRole('admin')
            ? AdminRoomResource::collection($rooms)
            : KaryawanRoomResource::collection($rooms);

        return $resource->additional([
            'status' => 'success',
            'meta' => [
                'current_page' => $rooms->currentPage(),
                'per_page'     => $rooms->perPage(),
                'total'        => $rooms->total(),
                'last_page'    => $rooms->lastPage(),
            ],
        ]);
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

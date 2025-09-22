<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RoomRequest;
use App\Http\Resources\Admin\RoomResource as AdminRoomResource;
use App\Http\Resources\Karyawan\RoomResource as KaryawanRoomResource;
use App\Services\RoomService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class RoomController extends Controller
{
    protected $roomService;

    public function __construct(RoomService $roomService)
    {
        $this->roomService = $roomService;
    }

    public function index()
    {
        $rooms = $this->roomService->getAll();

        return Auth::user()->hasRole('admin')
            ? AdminRoomResource::collection($rooms)
            : KaryawanRoomResource::collection($rooms);
    }

    public function show($id)
    {
        $room = $this->roomService->find($id);

        return Auth::user()->hasRole('admin')
            ? new AdminRoomResource($room)
            : new KaryawanRoomResource($room);
    }

    public function store(RoomRequest $request)
    {
        if (!Auth::user()->hasRole('admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $room = $this->roomService->create($request->validated());
        return new AdminRoomResource($room);
    }

    public function update(RoomRequest $request, $id)
    {
        if (!Auth::user()->hasRole('admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $room = $this->roomService->update($id, $request->validated());
        return new AdminRoomResource($room);
    }

    public function destroy($id)
    {
        if (!Auth::user()->hasRole('admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $this->roomService->delete($id);
            return response()->json(['message' => 'Room deleted successfully']);
        } catch (ValidationException $e) {
            return response()->json([
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }
}

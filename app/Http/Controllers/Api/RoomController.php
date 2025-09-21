<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRoomRequest;
use App\Http\Requests\UpdateRoomRequest;
use App\Http\Resources\RoomResource;
use App\Models\Room;
use App\Services\RoomService;

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
        return RoomResource::collection($rooms);
    }

    public function store(StoreRoomRequest $request)
    {
        $room = $this->roomService->create($request->validated());
        return new RoomResource($room);
    }

    public function show(Room $room)
    {
        $room = $this->roomService->getById($room);
        return new RoomResource($room);
    }

    public function update(UpdateRoomRequest $request, Room $room)
    {
        $room = $this->roomService->update($room, $request->validated());
        return new RoomResource($room);
    }

    public function destroy(Room $room)
    {
        $this->roomService->delete($room);
        return response()->json(null, 204);
    }
}

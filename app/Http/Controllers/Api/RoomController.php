<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RoomRequest;
use App\Http\Resources\RoomResource;
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
        return RoomResource::collection($this->roomService->getAll());
    }

    public function store(RoomRequest $request)
    {
        $room = $this->roomService->create($request->validated());
        return new RoomResource($room);
    }

    public function show($id)
    {
        $room = $this->roomService->find($id);
        return new RoomResource($room);
    }

    public function update(RoomRequest $request, $id)
    {
        $room = $this->roomService->update($id, $request->validated());
        return new RoomResource($room);
    }

    public function destroy($id)
    {
        $this->roomService->delete($id);
        return response()->json(['message' => 'Room deleted successfully']);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\FixedScheduleRequest;
use App\Http\Resources\FixedScheduleResource;
use App\Services\FixedScheduleService;

class FixedScheduleController extends Controller
{
    protected $fixedScheduleService;

    public function __construct(FixedScheduleService $fixedScheduleService)
    {
        $this->fixedScheduleService = $fixedScheduleService;
    }

    public function index()
    {
        return FixedScheduleResource::collection($this->fixedScheduleService->getAll());
    }

    public function store(FixedScheduleRequest $request)
    {
        $schedule = $this->fixedScheduleService->create($request->validated());
        return new FixedScheduleResource($schedule);
    }

    public function show($id)
    {
        return new FixedScheduleResource($this->fixedScheduleService->find($id));
    }

    public function update(FixedScheduleRequest $request, $id)
    {
        $schedule = $this->fixedScheduleService->update($id, $request->validated());
        return new FixedScheduleResource($schedule);
    }

    public function destroy($id)
    {
        $this->fixedScheduleService->delete($id);
        return response()->json(['message' => 'FixedSchedule deleted successfully']);
    }
}

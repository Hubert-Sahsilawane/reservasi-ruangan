<?php
namespace App\Http\Controllers;

use App\Http\Requests\StoreFixedScheduleRequest;
use App\Http\Requests\UpdateFixedScheduleRequest;
use App\Http\Resources\FixedScheduleResource;
use App\Models\FixedSchedule;
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
        $schedules = $this->fixedScheduleService->getAll();
        return FixedScheduleResource::collection($schedules);
    }

    public function store(StoreFixedScheduleRequest $request)
    {
        $schedule = $this->fixedScheduleService->create($request->validated());
        return new FixedScheduleResource($schedule);
    }

    public function show(FixedSchedule $fixedSchedule)
    {
        $schedule = $this->fixedScheduleService->getById($fixedSchedule);
        return new FixedScheduleResource($schedule);
    }

    public function update(UpdateFixedScheduleRequest $request, FixedSchedule $fixedSchedule)
    {
        $schedule = $this->fixedScheduleService->update($fixedSchedule, $request->validated());
        return new FixedScheduleResource($schedule);
    }

    public function destroy(FixedSchedule $fixedSchedule)
    {
        $this->fixedScheduleService->delete($fixedSchedule);
        return response()->json(null, 204);
    }
}

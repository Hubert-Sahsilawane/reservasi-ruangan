<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\FixedScheduleRequest;
use App\Http\Resources\Admin\FixedScheduleResource as AdminFixedScheduleResource;
use App\Http\Resources\Karyawan\FixedScheduleResource as KaryawanFixedScheduleResource;
use App\Services\FixedScheduleService;
use Illuminate\Support\Facades\Auth;

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

        return Auth::user()->hasRole('admin')
            ? AdminFixedScheduleResource::collection($schedules)
            : KaryawanFixedScheduleResource::collection($schedules);
    }

    public function store(FixedScheduleRequest $request)
    {
        if (!Auth::user()->hasRole('admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $schedule = $this->fixedScheduleService->create($request->validated());
        return new AdminFixedScheduleResource($schedule);
    }

    public function show($id)
    {
        $schedule = $this->fixedScheduleService->find($id);

        return Auth::user()->hasRole('admin')
            ? new AdminFixedScheduleResource($schedule)
            : new KaryawanFixedScheduleResource($schedule);
    }

    public function update(FixedScheduleRequest $request, $id)
    {
        if (!Auth::user()->hasRole('admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $schedule = $this->fixedScheduleService->update($id, $request->validated());
        return new AdminFixedScheduleResource($schedule);
    }

    public function destroy($id)
    {
        if (!Auth::user()->hasRole('admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $this->fixedScheduleService->delete($id);
        return response()->json(['message' => 'FixedSchedule deleted successfully']);
    }
}

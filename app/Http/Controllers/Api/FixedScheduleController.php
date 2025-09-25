<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\FixedScheduleRequest;
use App\Http\Resources\Admin\FixedScheduleResource as AdminResource;
use App\Http\Resources\Karyawan\FixedScheduleResource as KaryawanResource;
use App\Services\FixedScheduleService;
use Illuminate\Support\Facades\Auth;
use App\Models\FixedSchedule;

class FixedScheduleController extends Controller
{
    protected $service;

    public function __construct(FixedScheduleService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $schedules = $this->service->getAll();

        return Auth::user()->hasRole('admin')
            ? AdminResource::collection($schedules)
            : KaryawanResource::collection($schedules);
    }

    public function store(FixedScheduleRequest $request)
    {
        if (!Auth::user()->hasRole('admin')) {
            return response()->json(['message'=>'Unauthorized'],403);
        }

        $schedule = $this->service->create($request->validated());
        return new AdminResource($schedule);
    }

   public function show(FixedSchedule $schedule)
{
    return Auth::user()->hasRole('admin')
        ? new AdminResource($schedule->load(['room','user']))
        : new KaryawanResource($schedule->load(['room','user']));
}


    public function update(FixedScheduleRequest $request,$id)
    {
        if (!Auth::user()->hasRole('admin')) {
            return response()->json(['message'=>'Unauthorized'],403);
        }

        $schedule = $this->service->update($id,$request->validated());
        return new AdminResource($schedule);
    }

    public function destroy($id)
    {
        if (!Auth::user()->hasRole('admin')) {
            return response()->json(['message'=>'Unauthorized'],403);
        }

        $this->service->delete($id);
        return response()->json(['message'=>'FixedSchedule deleted successfully']);
    }
}

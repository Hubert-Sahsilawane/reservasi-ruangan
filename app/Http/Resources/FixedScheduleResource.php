<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FixedScheduleResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'         => $this->id,
            'room'       => [
                'id'   => $this->room->id,
                'name' => $this->room->name,
            ],
            'day_of_week'=> $this->day_of_week,
            'start_time' => $this->start_time,
            'end_time'   => $this->end_time,
        ];
    }
}

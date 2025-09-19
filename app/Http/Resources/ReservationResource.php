<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReservationResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'         => $this->id,
            'room'       => [
                'id'   => $this->room->id,
                'name' => $this->room->name,
            ],
            'user'       => [
                'id'   => $this->user->id,
                'name' => $this->user->name,
            ],
            'start_time' => $this->start_time->toDateTimeString(),
            'end_time'   => $this->end_time->toDateTimeString(),
            'status'     => $this->status,
        ];
    }
}

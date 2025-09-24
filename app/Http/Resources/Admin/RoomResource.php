<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class RoomResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'            => $this->id,
            'nama_ruangan'  => $this->nama_ruangan,
            'kapasitas'     => $this->kapasitas,
            'deskripsi'     => $this->deskripsi,
            'status'        => $this->status,        
            'reservations'  => ReservationResource::collection($this->whenLoaded('reservations')),
            'fixed_schedules' => FixedScheduleResource::collection($this->whenLoaded('fixedSchedules')),
        ];
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\RoomResource;

class FixedScheduleResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'room' => new RoomResource($this->whenLoaded('room')),
            'hari' => $this->hari,
            'waktu_mulai' => $this->waktu_mulai,
            'waktu_selesai' => $this->waktu_selesai,
            'keterangan' => $this->keterangan,
        ];
    }
}

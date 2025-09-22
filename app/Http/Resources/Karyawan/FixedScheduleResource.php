<?php

namespace App\Http\Resources\Karyawan;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class FixedScheduleResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'            => $this->id,
            'room'          => new RoomResource($this->whenLoaded('room')),
            'hari'          => $this->hari,
            'waktu_mulai'   => Carbon::parse($this->waktu_mulai)->format('H:i'),
            'waktu_selesai' => Carbon::parse($this->waktu_selesai)->format('H:i'),
            'keterangan'    => $this->keterangan,
        ];
    }
}

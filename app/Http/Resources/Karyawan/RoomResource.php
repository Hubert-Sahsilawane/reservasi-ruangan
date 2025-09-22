<?php
namespace App\Http\Resources\Karyawan;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Admin\ReservationResource;
use App\Http\Resources\Admin\FixedScheduleResource;

class RoomResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'          => $this->id,
            'nama_ruangan'=> $this->nama_ruangan,
            'kapasitas'   => $this->kapasitas,
            'deskripsi'   => $this->deskripsi,
            'status'      => $this->status,
            'reservations'=> ReservationResource::collection($this->whenLoaded('reservations')),
            'fixed_schedules' => FixedScheduleResource::collection($this->whenLoaded('fixedSchedules')),
        ];
    }
}

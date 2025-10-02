<?php

namespace App\Http\Resources\Karyawan;

use Illuminate\Http\Resources\Json\JsonResource;

class FixedScheduleResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'room' => [
                'id' => $this->room->id,
                'name' => $this->room->name,
            ],
            'tanggal'       => optional($this->tanggal)->format('Y-m-d'),
            'hari'          => optional($this->tanggal)->locale('id')->dayName,
            'waktu_mulai'   => $this->waktu_mulai,
            'waktu_selesai' => $this->waktu_selesai,
            'status'        => $this->status,
            'keterangan'    => $this->keterangan,
        ];
    }
}

<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class FixedScheduleResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
            ],
            'room' => [
                'id' => $this->room->id,
                'name' => $this->room->nama_ruangan,
            ],
            'tanggal'       => optional($this->tanggal)->format('Y-m-d'),
            'hari'          => optional($this->tanggal)->locale('id')->dayName,
            'waktu_mulai'   => $this->waktu_mulai,
            'waktu_selesai' => $this->waktu_selesai,
            'status'        => $this->status,
            'keterangan'    => $this->keterangan,
            'created_at'    => $this->created_at?->toDateTimeString(),
            'updated_at'    => $this->updated_at?->toDateTimeString(),
        ];
    }
}

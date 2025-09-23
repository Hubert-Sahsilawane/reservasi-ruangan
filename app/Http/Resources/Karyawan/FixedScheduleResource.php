<?php

namespace App\Http\Resources\Karyawan;

use Illuminate\Http\Resources\Json\JsonResource;

class FixedScheduleResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'room' => $this->room->nama_ruangan,
            'tanggal' => $this->tanggal?->format('Y-m-d'),
            'hari' => $this->hari,
            'waktu_mulai' => $this->waktu_mulai,
            'waktu_selesai' => $this->waktu_selesai,
            'status' => $this->status,
            'keterangan' => $this->keterangan,
        ];
    }
}

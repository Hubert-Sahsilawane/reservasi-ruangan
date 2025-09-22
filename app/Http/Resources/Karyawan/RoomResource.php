<?php

namespace App\Http\Resources\Karyawan;

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
            'status'        => $this->status,        // dari DB
            'status_aktual' => $this->status_aktual, // real-time
        ];
    }
}

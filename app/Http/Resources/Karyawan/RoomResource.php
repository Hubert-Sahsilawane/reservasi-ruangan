<?php

namespace App\Http\Resources\Karyawan;

use Illuminate\Http\Resources\Json\JsonResource;

class RoomResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'kapasitas'     => $this->kapasitas,
            'deskripsi'     => $this->deskripsi,
            'status'        => $this->status,        // dari DB
        ];
    }
}

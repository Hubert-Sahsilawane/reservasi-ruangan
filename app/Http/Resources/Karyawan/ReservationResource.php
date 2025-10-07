<?php

namespace App\Http\Resources\Karyawan;

use Illuminate\Http\Resources\Json\JsonResource;

class ReservationResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'status'        => $this->status,
            'id'            => $this->id,
            'room'          => [
                'id'   => $this->room->id,
                'name' => $this->room->name,
            ],
            'tanggal'       => $this->tanggal->format('Y-m-d'),
            'hari'          => $this->hari,
            'waktu_mulai'   => $this->waktu_mulai,
            'waktu_selesai' => $this->waktu_selesai,
            'created_at'    => $this->created_at->toDateTimeString(),
        ];
    }

    public function with($request): array
    {
        return [
            'status' => 'success',
        ];
    }
}

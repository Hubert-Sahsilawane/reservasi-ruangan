<?php

namespace App\Http\Resources\Karyawan;

use Illuminate\Http\Resources\Json\JsonResource;

class ReservationResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'            => $this->id,
            'room'          => [
                'id'   => $this->room->id,
                'name' => $this->room->nama_ruangan, // ✅ konsisten pakai nama_ruangan
            ],
            'tanggal'       => $this->tanggal->format('Y-m-d'),
            'hari'          => $this->hari, // ✅ tampilkan hari
            'waktu_mulai'   => $this->waktu_mulai,
            'waktu_selesai' => $this->waktu_selesai,
            'status'        => $this->status,
            'keterangan'    => $this->keterangan,
            'created_at'    => $this->created_at->toDateTimeString(),
        ];
    }
}

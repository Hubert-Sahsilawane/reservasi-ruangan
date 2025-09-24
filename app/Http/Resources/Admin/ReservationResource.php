<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class ReservationResource extends JsonResource
{
public function toArray($request): array
{
    return [
        'id'            => $this->id,
        'user'          => [
            'id'    => $this->user->id,
            'name'  => $this->user->name,
            'email' => $this->user->email,
        ],
        'room'          => [
            'id'   => $this->room->id,
            'name' => $this->room->nama_ruangan,
        ],
        'tanggal'       => $this->tanggal,
        'hari'          => $this->hari, // ✅ Tambah di sini
        'waktu_mulai'   => $this->waktu_mulai,
        'waktu_selesai' => $this->waktu_selesai,
        'status'        => $this->status,
        'reason'        => $this->reason, // ✅ Tambah di sini
        'created_at'    => $this->created_at->toDateTimeString(),
        'updated_at'    => $this->updated_at->toDateTimeString(),
    ];
}
}

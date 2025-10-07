<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class ReservationResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'status'        => $this->status, // tetap tampil di dalam data
            'id'            => $this->id,
            'user'          => [
                'id'    => $this->user->id,
                'name'  => $this->user->name,
                'email' => $this->user->email,
            ],
            'room'          => [
                'id'   => $this->room->id,
                'name' => $this->room->name,
            ],
            'tanggal'       => Carbon::parse($this->tanggal)->format('Y-m-d'),
            'hari'          => Carbon::parse($this->tanggal)->locale('id')->dayName,
            'waktu_mulai'   => $this->waktu_mulai,
            'waktu_selesai' => $this->waktu_selesai,
            'reason'        => $this->reason,
            'created_at'    => $this->created_at->toDateTimeString(),
            'updated_at'    => $this->updated_at->toDateTimeString(),
        ];
    }

    public function with($request): array
    {
        return [
            'status' => 'success',
        ];
    }
}

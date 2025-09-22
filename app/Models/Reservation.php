<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'room_id', 'wakttu_mulai', 'waktu_selesai', 'status'
    ];

    protected $casts = [
        'waktu_mulai' => 'datetime',
        'waktu_selesai'   => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function room()
    {
        return $this->belongsTo(\App\Models\Room::class);
    }

    // scope untuk mencari overlap
    public function scopeOverlapping($query, $roomId, $start, $end)
    {
        return $query->where('room_id', $roomId)
            ->where(function($q) use ($start, $end) {
                $q->where('waktu_mulai', '<', $end)
                  ->where('waktu_selesai', '>', $start);
            });
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;
use App\Models\Room;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'room_id',
        'tanggal',
        'hari',           // ✅ hari disimpan text: "Senin".."Minggu"
        'waktu_mulai',
        'waktu_selesai',
        'keterangan',
        'status',
    ];

    protected $casts = [
        'tanggal'       => 'date:Y-m-d', // ✅ cast jadi tanggal saja
        'waktu_mulai'   => 'string',     // ✅ simpan sebagai string (format H:i)
        'waktu_selesai' => 'string',     // ✅ simpan sebagai string (format H:i)
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Scope untuk mencari overlapping reservation
     */
    public function scopeOverlapping($query, $roomId, $mulai, $selesai)
    {
        return $query->where('room_id', $roomId)
            ->whereIn('status', ['pending','approved'])
            ->where(function ($q) use ($mulai, $selesai) {
                $q->whereBetween('waktu_mulai', [$mulai, $selesai])
                  ->orWhereBetween('waktu_selesai', [$mulai, $selesai])
                  ->orWhere(function ($q2) use ($mulai, $selesai) {
                      $q2->where('waktu_mulai', '<=', $mulai)
                         ->where('waktu_selesai', '>=', $selesai);
                  });
            });
    }
}

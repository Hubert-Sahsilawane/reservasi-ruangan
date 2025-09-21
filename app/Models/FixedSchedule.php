<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FixedSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_id',
        'hari',
        'waktu_mulai',
        'waktu_selesai',
        'keterangan',
    ];

    // Relasi ke Room (jadwal tetap dimiliki oleh satu ruangan)
    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function reservations()
{
    return $this->hasMany(Reservation::class);
}

}

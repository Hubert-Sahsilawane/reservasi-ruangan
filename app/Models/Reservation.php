<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'room_id',
        'tanggal',
        'waktu_mulai',
        'waktu_selesai',
        'status',
    ];

    // Relasi ke User (setiap reservasi dibuat oleh seorang user)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke Room (setiap reservasi untuk satu ruangan)
    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    // Jika reservasi terkait dengan fixed schedule tertentu
public function fixedSchedule()
{
    return $this->belongsTo(FixedSchedule::class, 'fixed_schedule_id');
}

}

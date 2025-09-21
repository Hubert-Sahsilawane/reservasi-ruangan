<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_ruangan',
        'kapasitas',
        'deskripsi',
        'status',
    ];

    // Relasi ke Reservations
    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    // Relasi ke FixedSchedules
    public function fixedSchedules()
    {
        return $this->hasMany(FixedSchedule::class);
    }

    // Semua user yang pernah booking ruangan ini
public function users()
{
    return $this->belongsToMany(User::class, 'reservations')
                ->withPivot(['tanggal', 'waktu_mulai', 'waktu_selesai', 'status'])
                ->withTimestamps();
}

}

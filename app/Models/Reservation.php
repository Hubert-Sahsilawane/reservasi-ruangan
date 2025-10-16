<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use App\Models\Room;
use Carbon\Carbon;
use Spatie\Activitylog\Traits\LogsActivity; // ✅ Tambahkan ini
use Spatie\Activitylog\LogOptions;           // ✅ Tambahkan ini

class Reservation extends Model
{
    use HasFactory, SoftDeletes, LogsActivity; // ✅ Tambahkan LogsActivity di sini

    protected $fillable = [
        'user_id',
        'room_id',
        'tanggal',
        'hari',
        'waktu_mulai',
        'waktu_selesai',
        'reason',
        'status',
    ];

    protected $casts = [
        'tanggal'       => 'date:Y-m-d',
        'waktu_mulai'   => 'string',
        'waktu_selesai' => 'string',
    ];

    protected $dates = ['deleted_at'];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */
    public function setTanggalAttribute($value)
    {
        $this->attributes['tanggal'] = $value;
        $carbon = Carbon::parse($value)->locale('id');
        $this->attributes['hari'] = ucfirst($carbon->dayName);
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */
    public function scopeOverlapping($query, $roomId, $mulai, $selesai)
    {
        $mulai   = Carbon::parse($mulai)->format('H:i');
        $selesai = Carbon::parse($selesai)->format('H:i');

        return $query->where('room_id', $roomId)
            ->whereIn('status', ['pending', 'approved'])
            ->where(function ($q) use ($mulai, $selesai) {
                $q->whereBetween('waktu_mulai', [$mulai, $selesai])
                  ->orWhereBetween('waktu_selesai', [$mulai, $selesai])
                  ->orWhere(function ($q2) use ($mulai, $selesai) {
                      $q2->where('waktu_mulai', '<=', $mulai)
                         ->where('waktu_selesai', '>=', $selesai);
                  });
            });
    }

    /*
    |--------------------------------------------------------------------------
    | ACTIVITY LOG CONFIGURATION (SPATIE)
    |--------------------------------------------------------------------------
    */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('reservasi')
            ->logOnly([
                'user_id',
                'room_id',
                'tanggal',
                'hari',
                'waktu_mulai',
                'waktu_selesai',
                'status',
                'reason'
            ])
            ->logOnlyDirty() // hanya log field yang berubah
            ->dontSubmitEmptyLogs(); // tidak log kalau tidak ada perubahan
    }
}

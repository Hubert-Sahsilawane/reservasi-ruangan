<?php

namespace App\Services\Karyawan;

use App\Models\Reservation;
use App\Models\FixedSchedule;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class ReservationService
{
    public function create(array $data)
    {
        $data['status'] = 'pending';

        $tanggal = Carbon::parse($data['tanggal'])->format('Y-m-d');
        $mulai   = Carbon::parse($tanggal . ' ' . $data['waktu_mulai']);
        $selesai = Carbon::parse($tanggal . ' ' . $data['waktu_selesai']);

        $data['waktu_mulai']   = $mulai;
        $data['waktu_selesai'] = $selesai;

        // cek bentrok fixed schedule
        $dayName = Carbon::parse($tanggal)->locale('id')->dayName;
        $conflictFixed = FixedSchedule::where('room_id', $data['room_id'])
            ->where('hari', ucfirst($dayName))
            ->where(function ($q) use ($mulai, $selesai) {
                $q->whereBetween('waktu_mulai', [$mulai, $selesai])
                  ->orWhereBetween('waktu_selesai', [$mulai, $selesai])
                  ->orWhere(function ($q2) use ($mulai, $selesai) {
                      $q2->where('waktu_mulai', '<=', $mulai)
                         ->where('waktu_selesai', '>=', $selesai);
                  });
            })
            ->exists();

        if ($conflictFixed) {
            throw ValidationException::withMessages([
                'reservation' => 'Bentrok dengan jadwal tetap.'
            ]);
        }

        return Reservation::create($data);
    }

    public function getUserReservations($userId)
    {
        return Reservation::with('room')
            ->where('user_id', $userId)
            ->get();
    }
}

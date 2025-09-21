<?php

namespace App\Services;

use App\Models\Reservation;
use App\Models\FixedSchedule;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class ReservationService
{
    public function getAll()
    {
        return Reservation::with(['user', 'room'])->get();
    }

    public function find($id)
    {
        return Reservation::with(['user', 'room'])->findOrFail($id);
    }

    public function create(array $data)
    {
        // default status = pending
        $data['status'] = 'pending';

        // cek bentrok dengan fixed schedule
        $dayName = Carbon::parse($data['tanggal'])->locale('id')->dayName;

        $conflictFixed = FixedSchedule::where('room_id', $data['room_id'])
            ->where('hari', ucfirst($dayName))
            ->where(function ($q) use ($data) {
                $q->whereBetween('waktu_mulai', [$data['waktu_mulai'], $data['waktu_selesai']])
                  ->orWhereBetween('waktu_selesai', [$data['waktu_mulai'], $data['waktu_selesai']])
                  ->orWhere(function ($q2) use ($data) {
                      $q2->where('waktu_mulai', '<=', $data['waktu_mulai'])
                         ->where('waktu_selesai', '>=', $data['waktu_selesai']);
                  });
            })
            ->exists();

        if ($conflictFixed) {
            throw ValidationException::withMessages([
                'reservation' => 'Bentrok dengan jadwal tetap.'
            ]);
        }

        // cek overlap dengan reservasi approved
        $conflictReservation = Reservation::where('room_id', $data['room_id'])
            ->where('tanggal', $data['tanggal'])
            ->where('status', 'approved')
            ->where(function ($q) use ($data) {
                $q->whereBetween('waktu_mulai', [$data['waktu_mulai'], $data['waktu_selesai']])
                  ->orWhereBetween('waktu_selesai', [$data['waktu_mulai'], $data['waktu_selesai']])
                  ->orWhere(function ($q2) use ($data) {
                      $q2->where('waktu_mulai', '<=', $data['waktu_mulai'])
                         ->where('waktu_selesai', '>=', $data['waktu_selesai']);
                  });
            })
            ->exists();

        if ($conflictReservation) {
            throw ValidationException::withMessages([
                'reservation' => 'Bentrok dengan reservasi lain yang sudah disetujui.'
            ]);
        }

        return Reservation::create($data);
    }

    public function update($id, array $data)
    {
        $reservation = Reservation::findOrFail($id);
        $reservation->update($data);
        return $reservation;
    }

    public function delete($id)
    {
        $reservation = Reservation::findOrFail($id);
        $reservation->delete();
    }

    public function approve($id)
    {
        $reservation = Reservation::findOrFail($id);
        $reservation->update(['status' => 'approved']);
        return $reservation;
    }

    public function reject($id)
    {
        $reservation = Reservation::findOrFail($id);
        $reservation->update(['status' => 'rejected']);
        return $reservation;
    }
}

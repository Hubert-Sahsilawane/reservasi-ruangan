<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Reservation;
use Carbon\Carbon;

class UpdateRoomStatus extends Command
{
    protected $signature = 'rooms:update-status';
    protected $description = 'Update status ruangan jadi non-aktif kalau reservasi sudah selesai';

    public function handle()
    {
        $now = Carbon::now()->format('Y-m-d H:i');

        $reservations = Reservation::with('room')
            ->where('status', 'approved')
            ->whereRaw("CONCAT(tanggal, ' ', waktu_selesai) < ?", [$now])
            ->get();

        foreach ($reservations as $reservation) {
            if ($reservation->room) {
                $reservation->room->update(['status' => 'non-aktif']);
                $this->info("Room {$reservation->room->name} di-nonaktifkan.");
            }
        }

        return 0;
    }
}

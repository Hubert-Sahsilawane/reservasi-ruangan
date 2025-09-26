<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\Reservation;
use Carbon\Carbon;

Artisan::command('rooms:update-status', function () {
    $now = Carbon::now();

    // Ambil semua reservasi approved untuk hari ini
    $reservations = Reservation::with('room')
        ->where('status', 'approved')
        ->whereDate('tanggal', $now->toDateString())
        ->get();

    foreach ($reservations as $res) {
        $tanggal = Carbon::parse($res->tanggal)->toDateString();

        $mulai   = Carbon::parse($tanggal . ' ' . $res->waktu_mulai);
        $selesai = Carbon::parse($tanggal . ' ' . $res->waktu_selesai);

        if ($now->between($mulai, $selesai)) {
            if ($res->room->status !== 'aktif') {
                $res->room->update(['status' => 'aktif']);
                $this->info("✅ Ruangan {$res->room->nama_ruangan} AKTIF (dipakai)");
            }
        } elseif ($now->greaterThan($selesai)) {
            if ($res->room->status !== 'non-aktif') {
                $res->room->update(['status' => 'non-aktif']);
                $this->info("⏰ Ruangan {$res->room->nama_ruangan} NON-AKTIF (selesai dipakai)");
            }
        } else {
            $this->info("⌛ Ruangan {$res->room->nama_ruangan} menunggu jam mulai");
        }
    }
})->purpose('Update status ruangan berdasarkan reservasi aktif');

// ✅ Scheduler jalan tiap menit
Schedule::command('rooms:update-status')->everyMinute();

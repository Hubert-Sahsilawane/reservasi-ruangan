<?php

use Illuminate\Support\Facades\Artisan;
use App\Models\Reservation;
use App\Models\FixedSchedule;
use App\Models\Room;
use Carbon\Carbon;

Artisan::command('rooms:update-status', function () {
    $now = Carbon::now();

    // 🔄 Reset semua ruangan jadi non-aktif dulu
    Room::query()->update(['status' => 'non-aktif']);
    $this->info("🔄 Semua ruangan direset ke NON-AKTIF");

    // 1️⃣ FixedSchedule (prioritas utama)
    $fixedSchedules = FixedSchedule::with('room')
        ->whereDate('tanggal', $now->toDateString())
        ->get();

    foreach ($fixedSchedules as $fs) {
        $tanggal = Carbon::parse($fs->tanggal)->toDateString();
        $mulai   = Carbon::parse($tanggal . ' ' . $fs->waktu_mulai);
        $selesai = Carbon::parse($tanggal . ' ' . $fs->waktu_selesai);

        if ($now->between($mulai, $selesai)) {
            $fs->room->update(['status' => 'aktif']);
            $this->info("✅ [FixedSchedule] Ruangan {$fs->room->name} AKTIF");
        }
    }

    // 2️⃣ Reservation (jika tidak ada bentrok fixed schedule)
    $reservations = Reservation::with('room')
        ->where('status', 'approved')
        ->whereDate('tanggal', $now->toDateString())
        ->get();

    foreach ($reservations as $res) {
        $tanggal = Carbon::parse($res->tanggal)->toDateString();
        $mulai   = Carbon::parse($tanggal . ' ' . $res->waktu_mulai);
        $selesai = Carbon::parse($tanggal . ' ' . $res->waktu_selesai);

        // Cek apakah ada fixed schedule aktif di ruangan ini
        $hasFixed = FixedSchedule::where('room_id', $res->room_id)
            ->where('tanggal', $res->tanggal)
            ->whereTime('waktu_mulai', '<=', $now->format('H:i:s'))
            ->whereTime('waktu_selesai', '>=', $now->format('H:i:s'))
            ->exists();

        if ($hasFixed) {
            $this->warn("⚡ [Reservasi] Ruangan {$res->room->name} diabaikan (ada FixedSchedule)");
            continue;
        }

        if ($now->between($mulai, $selesai)) {
            $res->room->update(['status' => 'aktif']);
            $this->info("✅ [Reservasi] Ruangan {$res->room->name} AKTIF");
        }
    }
})->purpose('Update status ruangan dengan prioritas FixedSchedule > Reservation');

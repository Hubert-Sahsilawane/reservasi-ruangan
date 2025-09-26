<?php

use Illuminate\Support\Facades\Artisan;
use App\Models\Reservation;
use App\Models\FixedSchedule;
use Carbon\Carbon;

Artisan::command('rooms:update-status', function () {
    $now = Carbon::now();

    // 1️⃣ Ambil semua fixed schedule untuk hari ini (prioritas utama)
    $fixedSchedules = FixedSchedule::with('room')
        ->whereDate('tanggal', $now->toDateString())
        ->get();

    foreach ($fixedSchedules as $fs) {
        $tanggal = Carbon::parse($fs->tanggal)->toDateString();
        $mulai   = Carbon::parse($tanggal . ' ' . $fs->waktu_mulai);
        $selesai = Carbon::parse($tanggal . ' ' . $fs->waktu_selesai);

        if ($now->between($mulai, $selesai)) {
            $fs->room->update(['status' => 'aktif']);
            $this->info("✅ [FixedSchedule] Ruangan {$fs->room->nama_ruangan} AKTIF");
            continue; // langsung skip reservasi, fixed schedule menang
        } elseif ($now->greaterThan($selesai)) {
            $fs->room->update(['status' => 'non-aktif']);
            $this->info("⏰ [FixedSchedule] Ruangan {$fs->room->nama_ruangan} NON-AKTIF");
        } else {
            $this->info("⌛ [FixedSchedule] Ruangan {$fs->room->nama_ruangan} menunggu jam mulai");
        }
    }

    // 2️⃣ Ambil semua reservasi yang approved untuk hari ini (hanya yang tidak kena fixed schedule)
    $reservations = Reservation::with('room')
        ->where('status', 'approved')
        ->whereDate('tanggal', $now->toDateString())
        ->get();

    foreach ($reservations as $res) {
        $tanggal = Carbon::parse($res->tanggal)->toDateString();
        $mulai   = Carbon::parse($tanggal . ' ' . $res->waktu_mulai);
        $selesai = Carbon::parse($tanggal . ' ' . $res->waktu_selesai);

        // 🔍 Cek apakah ada fixed schedule yang bentrok dengan reservasi ini
        $hasFixed = FixedSchedule::where('room_id', $res->room_id)
            ->where('tanggal', $res->tanggal)
            ->where(function ($q) use ($mulai, $selesai) {
                $q->whereBetween('waktu_mulai', [$mulai->format('H:i:s'), $selesai->format('H:i:s')])
                  ->orWhereBetween('waktu_selesai', [$mulai->format('H:i:s'), $selesai->format('H:i:s')])
                  ->orWhere(function ($q2) use ($mulai, $selesai) {
                      $q2->where('waktu_mulai', '<=', $mulai->format('H:i:s'))
                         ->where('waktu_selesai', '>=', $selesai->format('H:i:s'));
                  });
            })
            ->exists();

        if ($hasFixed) {
            $this->warn("⚡ [Reservasi] Ruangan {$res->room->nama_ruangan} diabaikan (ada FixedSchedule)");
            continue; // skip, karena fixed schedule lebih kuat
        }

        if ($now->between($mulai, $selesai)) {
            $res->room->update(['status' => 'aktif']);
            $this->info("✅ [Reservasi] Ruangan {$res->room->nama_ruangan} AKTIF");
        } elseif ($now->greaterThan($selesai)) {
            $res->room->update(['status' => 'non-aktif']);
            $this->info("⏰ [Reservasi] Ruangan {$res->room->nama_ruangan} NON-AKTIF");
        } else {
            $this->info("⌛ [Reservasi] Ruangan {$res->room->nama_ruangan} menunggu jam mulai");
        }
    }
})->purpose('Update status ruangan dengan prioritas FixedSchedule > Reservation');

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Reservation;
use Illuminate\Support\Facades\Mail;
use App\Mail\AdminReservationCanceledMail;

class SendAdminReservationCancelNotification extends Command
{
    protected $signature = 'app:send-admin-reservation-cancel-notification';

    protected $description = 'Kirim notifikasi email ke admin untuk reservasi yang dibatalkan user.';

    public function handle()
    {
        $reservations = Reservation::where('status', 'canceled')
            ->whereNull('admin_notified_at')
            ->with(['user', 'room'])
            ->get();

        if ($reservations->isEmpty()) {
            $this->info('Tidak ada reservasi dibatalkan yang perlu diberitahu ke admin.');
            return;
        }

        foreach ($reservations as $reservation) {
            // ✅ Kirim ke admin saja
            Mail::to('admin@reservasi.com')
                ->send(new AdminReservationCanceledMail($reservation));

            // update flag supaya tidak dikirim berulang
            $reservation->update(['admin_notified_at' => now()]);

            $this->info("Notifikasi reservasi #{$reservation->id} berhasil dikirim ke admin.");
        }
    }
}

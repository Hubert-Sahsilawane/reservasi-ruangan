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
            Mail::to('superadmin@example.com') // bisa ambil dari config/users table
                ->send(new AdminReservationCanceledMail($reservation));

            $reservation->update(['admin_notified_at' => now()]);

            $this->info("Notifikasi reservasi #{$reservation->id} berhasil dikirim ke admin.");
        }
    }
}

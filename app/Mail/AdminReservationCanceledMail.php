<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Reservation;

class AdminReservationCanceledMail extends Mailable
{
    use Queueable, SerializesModels;

    public $reservation;

    public function __construct(Reservation $reservation)
    {
        $this->reservation = $reservation;
    }

    public function build()
    {
        return $this->subject('Notifikasi: Reservasi Dibatalkan oleh User')
                    ->view('emails.admin_reservation_canceled')
                    ->with([
                        'reservation' => $this->reservation,
                    ]);
    }
}
    
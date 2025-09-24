<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Reservation;

class ReservationCanceledByUserMail extends Mailable
{
    use Queueable, SerializesModels;

    public $reservation;

    public function __construct(Reservation $reservation)
    {
        $this->reservation = $reservation;
    }

    public function build()
    {
        return $this->subject('Reservasi Dibatalkan oleh User')
                    ->view('emails.reservation_canceled_by_user')
                    ->with([
                        'reservation' => $this->reservation,
                    ]);
    }
}

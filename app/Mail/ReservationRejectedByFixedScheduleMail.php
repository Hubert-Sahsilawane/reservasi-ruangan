<?php

namespace App\Mail;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReservationRejectedByFixedScheduleMail extends Mailable
{
    use Queueable, SerializesModels;

    public $reservation;

    public function __construct(Reservation $reservation)
    {
        $this->reservation = $reservation;
    }

    public function build()
    {
        return $this->subject('Reservasi Anda dibatalkan')
                    ->view('emails.reservations.rejected_by_fixed_schedule')
                    ->with([
                        'reservation' => $this->reservation,
                    ]);
    }
}

<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Reservation;

class ReservationRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $reservation;
    public $reason;

    public function __construct(Reservation $reservation, $reason = null)
    {
        $this->reservation = $reservation;
        $this->reason = $reason;
    }

    public function build()
    {
        return $this->subject('Reservasi Anda DITOLAK')
                    ->view('emails.reservation_rejected')
                    ->with(['reservation' => $this->reservation, 'reason' => $this->reason]);
    }
}

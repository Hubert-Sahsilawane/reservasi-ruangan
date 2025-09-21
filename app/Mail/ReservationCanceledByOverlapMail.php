<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Reservation;

class ReservationCanceledByOverlapMail extends Mailable
{
    use Queueable, SerializesModels;

    public $reservation;
    public $approvedReservation; // reservation that caused the cancel

    public function __construct(Reservation $reservation, Reservation $approvedReservation)
    {
        $this->reservation = $reservation;
        $this->approvedReservation = $approvedReservation;
    }

    public function build()
    {
        return $this->subject('Reservasi Anda Dibatalkan karena Konflik Waktu')
                    ->view('emails.reservation_canceled_overlap')
                    ->with([
                        'reservation' => $this->reservation,
                        'approvedReservation' => $this->approvedReservation
                    ]);
    }
}

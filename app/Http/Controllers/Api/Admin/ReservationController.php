<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Reservation;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReservationApprovedMail;
use App\Mail\ReservationRejectedMail;

class ReservationController extends Controller
{
    public function approve($id)
    {
        $reservation = Reservation::findOrFail($id);
        $reservation->status = 'approved';
        $reservation->save();

        // Kirim Email ke user
        Mail::to($reservation->user->email)->send(new ReservationApprovedMail($reservation));

        return response()->json([
            'message' => 'Reservasi berhasil disetujui & notifikasi email terkirim.',
            'data'    => $reservation
        ]);
    }

    public function reject(Request $request, $id)
    {
        $reservation = Reservation::findOrFail($id);
        $reservation->status = 'rejected';
        $reservation->save();

        $reason = $request->input('reason', 'Tidak ada alasan diberikan.');

        // Kirim Email ke user
        Mail::to($reservation->user->email)->send(new ReservationRejectedMail($reservation, $reason));

        return response()->json([
            'message' => 'Reservasi berhasil ditolak & notifikasi email terkirim.',
            'data'    => $reservation,
            'reason'  => $reason
        ]);
    }
}

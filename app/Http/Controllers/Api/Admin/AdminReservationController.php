<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Reservation;
use App\Mail\ReservationApprovedMail;
use App\Mail\ReservationRejectedMail;
use App\Mail\ReservationCanceledByOverlapMail;
use Illuminate\Support\Facades\Mail;

class AdminReservationController extends Controller
{
    // Middleware auth & admin bisa ditambahkan di route group
    public function approve($id)
    {
        $reservation = Reservation::findOrFail($id);

        if ($reservation->status !== 'pending') {
            return response()->json(['message' => 'Reservasi bukan dalam status pending'], 400);
        }

        DB::beginTransaction();
        try {
            // set approved
            $reservation->status = 'approved';
            $reservation->save();

            // kirim email ke pemilik reservasi (boleh queue jika sudah setup)
            Mail::to($reservation->user->email)->queue(new ReservationApprovedMail($reservation));

            // temukan semua reservasi lain yang overlap di ruangan sama
            // di sini kita pilih yang status = pending (kamu bisa ubah untuk include approved)
            $overlaps = Reservation::overlapping(
                    $reservation->room_id,
                    $reservation->start_time,
                    $reservation->end_time
                )
                ->where('id', '!=', $reservation->id)
                ->whereIn('status', ['pending'])
                ->lockForUpdate()
                ->get();

            foreach ($overlaps as $other) {
                $other->status = 'canceled';
                $other->save();

                // notify user pemilik reservasi yang dibatalkan
                Mail::to($other->user->email)->queue(new ReservationCanceledByOverlapMail($other, $reservation));
            }

            DB::commit();
            return response()->json(['message' => 'Reservasi approved dan overlaps dibatalkan'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            // log error jika perlu
            return response()->json(['message' => 'Gagal approve: '.$e->getMessage()], 500);
        }
    }

    public function reject(Request $request, $id)
    {
        $reservation = Reservation::findOrFail($id);

        if ($reservation->status !== 'pending') {
            return response()->json(['message' => 'Reservasi bukan dalam status pending'], 400);
        }

        $reason = $request->input('reason', null);

        $reservation->status = 'rejected';
        $reservation->save();

        // kirim notifikasi/rejection email
        Mail::to($reservation->user->email)->queue(new ReservationRejectedMail($reservation, $reason));

        return response()->json(['message' => 'Reservasi ditolak dan user diberi tahu'], 200);
    }

    // optional: list pending reservations
    public function indexPending()
    {
        $list = Reservation::with(['user','room'])
                  ->where('status','pending')
                  ->orderBy('start_time')
                  ->get();

        return response()->json($list);
    }
}

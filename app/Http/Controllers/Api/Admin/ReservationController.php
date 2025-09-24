<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Admin\ReservationService;

class ReservationController extends Controller
{
    protected $service;

    public function __construct(ReservationService $service)
    {
        $this->service = $service;
    }

    /**
     * Menampilkan semua reservasi (untuk admin).
     */
    public function index()
    {
        $reservations = $this->service->getAll();

        return response()->json([
            'message' => 'Daftar semua reservasi berhasil diambil.',
            'data'    => $reservations,
        ]);
    }

    /**
     * Setujui reservasi.
     */
    public function approve($id)
    {
        $reservation = $this->service->approve($id);

        return response()->json([
            'message' => 'Reservasi berhasil disetujui & notifikasi email terkirim.',
            'data'    => $reservation,
        ]);
    }

    /**
     * Tolak reservasi dengan alasan.
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:255',
        ]);

        $reservation = $this->service->reject($id, $request->reason);

        return response()->json([
            'message' => 'Reservasi berhasil ditolak & notifikasi email terkirim.',
            'data'    => $reservation,
            'reason'  => $request->reason,
        ]);
    }

    /**
     * Hapus reservasi.
     */
    public function destroy($id)
    {
        $this->service->delete($id);

        return response()->json([
            'message' => 'Reservasi berhasil dihapus.',
        ]);
    }
}

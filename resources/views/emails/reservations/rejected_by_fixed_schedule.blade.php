<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reservasi Dibatalkan</title>
</head>
<body>
    <p>Halo {{ $reservation->user->name }},</p>

    <p>Reservasi Anda pada ruangan <strong>{{ $reservation->room->name }}</strong>
    tanggal <strong>{{ $reservation->tanggal->format('d M Y') }}</strong>
    pukul <strong>{{ $reservation->waktu_mulai }} - {{ $reservation->waktu_selesai }}</strong>
    telah <strong>{{ strtoupper($reservation->status) }}</strong>.</p>

    <p>Alasan: {{ $reservation->reason ?? 'Bentrok dengan jadwal tetap (Fixed Schedule).' }}</p>

    <p>Terima kasih.</p>
</body>
</html>

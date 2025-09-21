<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reservasi Ditolak</title>
</head>
<body>
    <h2>Halo {{ $reservation->user->name }},</h2>
    <p>
        Maaf, reservasi Anda untuk <strong>{{ $reservation->room->name }}</strong> pada:
    </p>
    <p>
        <b>{{ $reservation->start_time->format('d M Y H:i') }}</b>
        sampai
        <b>{{ $reservation->end_time->format('H:i') }}</b>
    </p>
    <p style="color:red; font-weight:bold;">
        Telah DITOLAK ❌
    </p>

    @if(!empty($reason))
        <p><strong>Alasan:</strong> {{ $reason }}</p>
    @endif

    <p>Silakan coba ajukan reservasi lain dengan jadwal yang berbeda.</p>
</body>
</html>

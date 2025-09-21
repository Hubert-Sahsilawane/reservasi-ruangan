<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reservasi Disetujui</title>
</head>
<body>
    <h2>Halo {{ $reservation->user->name }},</h2>
    <p>
        Reservasi Anda untuk <strong>{{ $reservation->room->name }}</strong> pada:
    </p>
    <p>
        <b>{{ $reservation->start_time->format('d M Y H:i') }}</b>
        sampai
        <b>{{ $reservation->end_time->format('H:i') }}</b>
    </p>
    <p style="color:green; font-weight:bold;">
        Telah DISETUJUI ✅
    </p>
    <p>Silakan hadir sesuai jadwal yang telah disetujui.</p>
</body>
</html>

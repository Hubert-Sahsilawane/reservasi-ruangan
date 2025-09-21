<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reservasi Dibatalkan (Konflik Jadwal)</title>
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
    <p style="color:orange; font-weight:bold;">
        Telah DIBATALKAN ⚠️
    </p>
    <p>
        Karena terdapat reservasi lain yang sudah disetujui pada waktu yang sama:
    </p>
    <ul>
        <li>Nama: {{ $approvedReservation->user->name }}</li>
        <li>Ruangan: {{ $approvedReservation->room->name }}</li>
        <li>Jadwal: {{ $approvedReservation->start_time->format('d M Y H:i') }} - {{ $approvedReservation->end_time->format('H:i') }}</li>
    </ul>
    <p>Silakan ajukan ulang dengan jadwal lain.</p>
</body>
</html>

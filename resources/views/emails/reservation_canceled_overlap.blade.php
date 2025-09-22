<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reservasi Dibatalkan (Konflik Jadwal)</title>
</head>
<body style="font-family: Arial, sans-serif; color: #333;">
    <h2>Halo {{ $reservation->user->name }},</h2>

    <p>
        Reservasi Anda untuk <strong>{{ $reservation->room->name }}</strong>
        pada hari <b>{{ $reservation->hari }}</b>:
    </p>

    <p>
        <b>
            {{ \Carbon\Carbon::parse($reservation->tanggal.' '.$reservation->waktu_mulai)->format('d M Y H:i') }}
        </b>
        sampai
        <b>
            {{ \Carbon\Carbon::parse($reservation->tanggal.' '.$reservation->waktu_selesai)->format('H:i') }}
        </b>
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
        <li>Jadwal:
            {{ \Carbon\Carbon::parse($approvedReservation->tanggal.' '.$approvedReservation->waktu_mulai)->format('d M Y H:i') }}
            -
            {{ \Carbon\Carbon::parse($approvedReservation->tanggal.' '.$approvedReservation->waktu_selesai)->format('H:i') }}
        </li>
    </ul>

    <p>Silakan ajukan ulang dengan jadwal lain.</p>
</body>
</html>

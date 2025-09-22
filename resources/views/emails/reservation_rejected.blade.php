<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reservasi Ditolak</title>
</head>
<body style="font-family: Arial, sans-serif; color: #333;">
    <h2>Halo {{ $reservation->user->name }},</h2>

    <p>
        Maaf, reservasi Anda untuk <strong>{{ $reservation->room->name }}</strong>
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

    <p style="color:red; font-weight:bold;">
        Telah DITOLAK ❌
    </p>

    @if(!empty($reason))
        <p><strong>Alasan:</strong> {{ $reason }}</p>
    @endif

    <p>Silakan coba ajukan reservasi lain dengan jadwal yang berbeda.</p>
</body>
</html>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservasi Dibatalkan</title>
</head>
<body>
    <h2>Halo, {{ $reservation->user->name }} 👋</h2>

    <p>Reservasi Anda <strong>DIBATALKAN</strong> karena jadwal bentrok ⚠️</p>

    <ul>
        <li><strong>Ruangan:</strong> {{ $reservation->room->name }}</li>
        <li><strong>Hari:</strong> {{ $reservation->hari }}</li>
        <li><strong>Tanggal:</strong> {{ $reservation->tanggal->format('d M Y') }} ({{ $reservation->hari }})</li>
        <li><strong>Waktu:</strong> {{ substr($reservation->waktu_mulai,0,5) }} - {{ substr($reservation->waktu_selesai,0,5) }}</li>
        <li><strong>Alasan Pembatalan:</strong> {{ $reason ?? 'Tidak ada alasan diberikan.' }}</li>
    </ul>

    <p>Silakan pilih jadwal lain agar tidak terjadi bentrok dengan reservasi yang sudah disetujui.</p>
</body>
</html>

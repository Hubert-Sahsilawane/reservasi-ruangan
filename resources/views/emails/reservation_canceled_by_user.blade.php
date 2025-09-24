<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reservasi Dibatalkan</title>
</head>
<body>
    <h2>Reservasi Dibatalkan oleh User</h2>
    <p>Halo Admin,</p>
    <p>Seorang user telah membatalkan reservasi dengan detail berikut:</p>

    <ul>
        <li>User: {{ $reservation->user->name }} ({{ $reservation->user->email }})</li>
        <li>Ruangan: {{ $reservation->room->nama_ruangan }}</li>
        <li><strong>Hari:</strong> {{ $reservation->hari }}</li>
        <li>Tanggal: {{ $reservation->tanggal }}</li>
        <li>Waktu: {{ $reservation->waktu_mulai }} - {{ $reservation->waktu_selesai }}</li>
        <li>Alasan Pembatalan: {{ $reservation->reason ?? '-' }}</li>
    </ul>

    <p>Terima kasih.</p>
</body>
</html>

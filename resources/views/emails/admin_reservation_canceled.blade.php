<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Reservasi Dibatalkan</title>
</head>
<body>
    <h2>Reservasi Dibatalkan</h2>
    <p>Halo Admin, ada reservasi yang dibatalkan oleh user:</p>

    <ul>
        <li><strong>Nama User:</strong> {{ $reservation->user->name }}</li>
        <li><strong>Email:</strong> {{ $reservation->user->email }}</li>
        <li><strong>Ruangan:</strong> {{ $reservation->room->nama_ruangan }}</li>
        <li><strong>Hari:</strong> {{ $reservation->hari }}</li>
        <li><strong>Tanggal:</strong> {{ $reservation->tanggal }}</li>
        <li><strong>Waktu:</strong> {{ $reservation->waktu_mulai }} - {{ $reservation->waktu_selesai }}</li>
        <li><strong>Alasan:</strong> {{ $reservation->reason ?? '-' }}</li>
    </ul>

    <p>Silakan cek dashboard admin untuk detail lebih lanjut.</p>
</body>
</html>

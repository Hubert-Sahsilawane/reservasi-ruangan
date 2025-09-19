<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Room;

class RoomSeeder extends Seeder
{
    public function run(): void
    {
        Room::create([
            'nama_ruangan' => 'Ruang Rapat 1',
            'kapasitas' => 20,
            'deskripsi' => 'Ruang rapat untuk meeting kecil',
            'status' => 'aktif',
        ]);

        Room::create([
            'nama_ruangan' => 'Aula Utama',
            'kapasitas' => 100,
            'deskripsi' => 'Ruang besar untuk acara',
            'status' => 'aktif',
        ]);
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Room;

class RoomSeeder extends Seeder
{
    public function run(): void
    {
        $rooms = [
            [
                'name' => 'Ruang Rapat 1',
                'kapasitas'    => 20,
                'deskripsi'    => 'Ruang rapat untuk meeting kecil',
            ],
            [
                'name' => 'Ruang Rapat 2',
                'kapasitas'    => 25,
                'deskripsi'    => 'Ruang rapat dengan proyektor',
            ],
            [
                'name' => 'Aula Utama',
                'kapasitas'    => 100,
                'deskripsi'    => 'Ruang besar untuk acara dan seminar',
            ],
            [
                'name' => 'Ruang Training',
                'kapasitas'    => 40,
                'deskripsi'    => 'Ruang pelatihan karyawan',
            ],
            [
                'name' => 'Ruang Diskusi A',
                'kapasitas'    => 10,
                'deskripsi'    => 'Ruang kecil untuk diskusi tim',
            ],
            [
                'name' => 'Ruang Diskusi B',
                'kapasitas'    => 12,
                'deskripsi'    => 'Ruang diskusi dengan papan tulis',
            ],
            [
                'name' => 'Ruang Presentasi',
                'kapasitas'    => 50,
                'deskripsi'    => 'Ruang untuk presentasi dan demo produk',
            ],
            [
                'name' => 'Ruang Kreatif',
                'kapasitas'    => 15,
                'deskripsi'    => 'Ruang dengan desain santai untuk brainstorming',
            ],
            [
                'name' => 'Ruang IT Support',
                'kapasitas'    => 8,
                'deskripsi'    => 'Ruang kerja tim IT support',
            ],
            [
                'name' => 'Ruang Manajemen',
                'kapasitas'    => 30,
                'deskripsi'    => 'Ruang meeting manajemen perusahaan',
            ],
        ];

        foreach ($rooms as $room) {
            Room::create([
                'name'         => $room['name'],
                'kapasitas'    => $room['kapasitas'],
                'deskripsi'    => $room['deskripsi'],
                'status'       => 'non-aktif', // default
            ]);
        }
    }
}

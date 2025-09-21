<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create([
            'name' => 'Admin Utama',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
        ]);

        // assign role admin (spatie)
        $admin->assignRole('admin');

        // simpan juga di kolom users.role supaya sinkron
        $admin->role = 'admin';
        $admin->save();
    }
}

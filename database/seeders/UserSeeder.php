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

$admin->assignRole('admin');
$admin->role = 'admin';
$admin->save();

    }
}


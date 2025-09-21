<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RegisterService
{
    public function register(array $data): array
    {
        $user = User::create([
    'name' => $data['name'],
    'email' => $data['email'],
    'password' => Hash::make($data['password']),
]);

// default role karyawan
$user->assignRole('karyawan');
$user->role = 'karyawan';
$user->save();


        // Buat token login
        $token = $user->createToken('TokenLogin')->accessToken;

        return [
            'user'  => $user,
            'token' => $token,
        ];
    }
}

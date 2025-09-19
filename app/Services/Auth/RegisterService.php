<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class RegisterService
{
    public function register(array $data): array
{

    $user = User::create([
        'name' => $data['name'],
        'email' => $data['email'],
        'password' => Hash::make($data['password']),
    ]);

    $user->assignRole('Karyawan'); // default role
    $user->role = 'Karyawan';
    $user->save();

    $token = $user->createToken('TokenLogin')->accessToken;

    return [
        'user' => $user,
        'token' => $token,
    ];
}

}

<?php

namespace App\Services\User;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function getAll()
    {
        // Ambil semua user dengan role-nya
        return User::with('roles')->get();
    }

    public function find($id)
    {
        return User::with('roles')->findOrFail($id);
    }

    public function create(array $data)
    {
        // Hash password
        $data['password'] = Hash::make($data['password']);

        // Buat user baru
        $user = User::create($data);

        // Assign role jika ada
        if (!empty($data['role'])) {
            $user->assignRole($data['role']);
        }

        return $user->load('roles');
    }

    public function update($id, array $data)
    {
        $user = User::findOrFail($id);

        // Kalau ada password baru → hash
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        // Update data user
        $user->update($data);

        // Kalau ada role → ganti role di Spatie
        if (!empty($data['role'])) {
            $user->syncRoles([$data['role']]);
        }

        return $user->load('roles');
    }

    public function delete($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
    }
}

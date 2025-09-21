<?php

namespace App\Services\User;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function getAll()
    {
        return User::with('roles')->get();
    }

    public function find($id)
    {
        return User::with('roles')->findOrFail($id);
    }

   public function create(array $data)
{
    $data['password'] = Hash::make($data['password']);
    $user = User::create($data);

    if (!empty($data['role'])) {
        $user->assignRole($data['role']);
        $user->role = $data['role'];
        $user->save();
    }

    return $user;
}


    public function update($id, array $data)
{
    $user = User::findOrFail($id);

    if (!empty($data['password'])) {
        $data['password'] = Hash::make($data['password']);
    } else {
        unset($data['password']);
    }

    $user->update($data);

    if (!empty($data['role'])) {
        $user->syncRoles([$data['role']]); // ganti role di Spatie
        $user->role = $data['role'];
        $user->save();
    }

    return $user;
}


    public function delete($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
    }
}

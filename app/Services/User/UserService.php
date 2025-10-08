<?php

namespace App\Services\User;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Builder;

class UserService
{
    public function getAll(array $filters = [], int $perPage = 10)
{
    $query = User::with('roles')->orderBy('id', 'asc'); // 🔁 urut berdasarkan ID

    // 🔍 Filter nama
    if (!empty($filters['search'])) {
        $query->where('name', 'like', "%{$filters['search']}%");
    }

    // 🎭 Filter role
    if (!empty($filters['role'])) {
        $query->whereHas('roles', function (Builder $q) use ($filters) {
            $q->where('name', $filters['role']);
        });
    }

    return $query->paginate($perPage);
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
        }

        return $user->load('roles');
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

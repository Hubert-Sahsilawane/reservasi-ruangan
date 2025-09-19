<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        // bikin permission
        Permission::create(['name' => 'manage reservations']);
        Permission::create(['name' => 'view rooms']);

        // role admin
        $admin = Role::create(['name' => 'admin']);
        $admin->givePermissionTo(['manage reservations', 'view rooms']);

        // role karyawan
        $karyawan = Role::create(['name' => 'karyawan']);
        $karyawan->givePermissionTo(['view rooms']);
    }
}


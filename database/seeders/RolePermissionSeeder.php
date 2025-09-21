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
        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo(['manage reservations', 'view rooms']);

        // role karyawan
        $karyawanRole = Role::create(['name' => 'karyawan']);
        $karyawanRole->givePermissionTo(['view rooms']);
    }
}

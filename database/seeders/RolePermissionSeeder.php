<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        // bikin permission untuk guard api
        Permission::create(['name' => 'manage reservations', 'guard_name' => 'api']);
        Permission::create(['name' => 'view rooms', 'guard_name' => 'api']);

        // role admin (guard api)
        $adminRole = Role::create(['name' => 'admin', 'guard_name' => 'api']);
        $adminRole->givePermissionTo(['manage reservations', 'view rooms']);

        // role karyawan (guard api)
        $karyawanRole = Role::create(['name' => 'karyawan', 'guard_name' => 'api']);
        $karyawanRole->givePermissionTo(['view rooms']);
    }
}

<?php

namespace Database\Seeders;

use App\Models\Outlet;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class RoleAndUserSeeder extends Seeder
{
    public function run(): void
    {
        $gudang = Outlet::query()->firstOrCreate(
            ['name' => 'Gudang Sukun'],
            ['address' => 'Sukun', 'type' => 'gudang'],
        );

        $pusat = Outlet::query()->firstOrCreate(
            ['name' => 'Pusat Sumberpucung'],
            ['address' => 'Sumberpucung', 'type' => 'pusat'],
        );

        $cabang = Outlet::query()->firstOrCreate(
            ['name' => 'Cabang 1'],
            ['address' => 'Outlet cabang 1', 'type' => 'cabang'],
        );

        foreach (['owner', 'administrator_sistem', 'staff_gudang', 'karyawan_outlet'] as $role) {
            Role::query()->firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        $users = [
            ['name' => 'Owner', 'email' => 'owner@gmail.com', 'username' => 'owner', 'role' => 'owner', 'outlet_id' => null],
            ['name' => 'Administrator Sistem', 'email' => 'administrator@gmail.com', 'username' => 'administrator', 'role' => 'administrator_sistem', 'outlet_id' => null],
            ['name' => 'Staff Gudang', 'email' => 'staff.gudang@gmail.com', 'username' => 'staff_gudang', 'role' => 'staff_gudang', 'outlet_id' => $gudang->id],
            ['name' => 'Karyawan Outlet', 'email' => 'karyawan.outlet@gmail.com', 'username' => 'karyawan_outlet', 'role' => 'karyawan_outlet', 'outlet_id' => $cabang->id],
        ];

        foreach ($users as $data) {
            $user = User::query()->updateOrCreate(
                ['email' => $data['email']],
                $data + ['password' => 'password'],
            );

            $user->syncRoles([$data['role']]);
        }
    }
}

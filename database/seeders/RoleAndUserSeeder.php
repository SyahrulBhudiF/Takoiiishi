<?php

namespace Database\Seeders;

use App\Models\Outlet;
use App\Models\User;
use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleAndUserSeeder extends Seeder
{
    public function run(): void
    {
        $pusat = Outlet::query()->firstOrCreate(
            ['name' => 'Pusat'],
            ['address' => 'Outlet pusat', 'type' => 'pusat'],
        );

        $cabang = Outlet::query()->firstOrCreate(
            ['name' => 'Cabang 1'],
            ['address' => 'Outlet cabang 1', 'type' => 'cabang'],
        );

        foreach (['admin_pusat', 'admin_cabang', 'pemilik_pusat', 'pemilik_cabang'] as $role) {
            Role::query()->firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        $users = [
            ['name' => 'Admin Pusat', 'email' => 'admin.pusat@takoyaki.test', 'username' => 'admin_pusat', 'role' => 'admin_pusat', 'outlet_id' => null],
            ['name' => 'Admin Cabang', 'email' => 'admin.cabang@takoyaki.test', 'username' => 'admin_cabang', 'role' => 'admin_cabang', 'outlet_id' => $cabang->id],
            ['name' => 'Pemilik Pusat', 'email' => 'pemilik.pusat@takoyaki.test', 'username' => 'pemilik_pusat', 'role' => 'pemilik_pusat', 'outlet_id' => null],
            ['name' => 'Pemilik Cabang', 'email' => 'pemilik.cabang@takoyaki.test', 'username' => 'pemilik_cabang', 'role' => 'pemilik_cabang', 'outlet_id' => $cabang->id],
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

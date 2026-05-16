<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $all = Permission::query()->pluck('name')->all();

        Role::findByName('admin_pusat')->syncPermissions($all);

        Role::findByName('pemilik_pusat')->syncPermissions(array_filter($all, fn (string $permission): bool => str_contains($permission, ':Purchase') || str_starts_with($permission, 'View')));

        Role::findByName('admin_cabang')->syncPermissions([]);
        Role::findByName('pemilik_cabang')->syncPermissions([]);
    }
}

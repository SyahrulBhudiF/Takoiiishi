<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $resources = ['Outlet', 'Ingredient', 'User', 'Stock', 'StockMovement', 'Purchase', 'Distribution', 'Sale'];
        $actions = ['ViewAny', 'View', 'Create', 'Update', 'Delete', 'DeleteAny', 'Restore', 'ForceDelete', 'ForceDeleteAny', 'RestoreAny', 'Replicate', 'Reorder'];

        foreach ($resources as $resource) {
            foreach ($actions as $action) {
                Permission::query()->firstOrCreate(['name' => "{$action}:{$resource}", 'guard_name' => 'web']);
            }
        }

        Permission::query()->firstOrCreate([
            'name' => 'view:App\\Filament\\Widgets\\LowStockOverview',
            'guard_name' => 'web',
        ]);

        $all = Permission::query()->pluck('name')->all();

        Role::findByName('admin_pusat')->syncPermissions($all);

        Role::findByName('admin_cabang')->syncPermissions([
            'ViewAny:Stock', 'View:Stock',
            'ViewAny:StockMovement', 'View:StockMovement',
            'ViewAny:Sale', 'View:Sale', 'Create:Sale',
            'view:App\\Filament\\Widgets\\LowStockOverview',
        ]);

        Role::findByName('pemilik_pusat')->syncPermissions([
            'ViewAny:Stock', 'View:Stock',
            'ViewAny:StockMovement', 'View:StockMovement',
            'ViewAny:Purchase', 'View:Purchase',
            'ViewAny:Distribution', 'View:Distribution',
            'ViewAny:Sale', 'View:Sale',
            'view:App\\Filament\\Widgets\\LowStockOverview',
        ]);

        Role::findByName('pemilik_cabang')->syncPermissions([
            'ViewAny:Stock', 'View:Stock',
            'ViewAny:StockMovement', 'View:StockMovement',
            'ViewAny:Sale', 'View:Sale',
            'view:App\\Filament\\Widgets\\LowStockOverview',
        ]);
    }
}

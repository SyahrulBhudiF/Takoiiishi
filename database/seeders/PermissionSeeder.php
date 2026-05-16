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

        // Widget permissions - Filament Shield format
        $widgetPermissions = [
            'widget_DashboardOverview',
            'widget_LowStockOverview',
            'widget_DailySalesChart',
            'widget_StockByOutletChart',
        ];

        foreach ($widgetPermissions as $permission) {
            Permission::query()->firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $all = Permission::query()->pluck('name')->all();

        Role::findByName('admin_pusat')->syncPermissions($all);

        Role::findByName('admin_cabang')->syncPermissions([
            'ViewAny:Stock', 'View:Stock',
            'ViewAny:StockMovement', 'View:StockMovement',
            'ViewAny:Sale', 'View:Sale', 'Create:Sale',
            ...$widgetPermissions,
        ]);

        Role::findByName('pemilik_pusat')->syncPermissions([
            'ViewAny:Stock', 'View:Stock',
            'ViewAny:StockMovement', 'View:StockMovement',
            'ViewAny:Purchase', 'View:Purchase',
            'ViewAny:Distribution', 'View:Distribution',
            'ViewAny:Sale', 'View:Sale',
            ...$widgetPermissions,
        ]);

        Role::findByName('pemilik_cabang')->syncPermissions([
            'ViewAny:Stock', 'View:Stock',
            'ViewAny:StockMovement', 'View:StockMovement',
            'ViewAny:Sale', 'View:Sale',
            ...$widgetPermissions,
        ]);
    }
}

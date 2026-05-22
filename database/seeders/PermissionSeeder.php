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

        $widgetPermissions = [
            'widget_DashboardOverview',
            'widget_LowStockOverview',
            'widget_DailySalesChart',
            'widget_StockByOutletChart',
        ];

        foreach ($widgetPermissions as $permission) {
            Permission::query()->firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $manageActions = ['ViewAny', 'View', 'Create', 'Update', 'Delete', 'DeleteAny'];
        $viewActions = ['ViewAny', 'View'];
        $outletSaleActions = ['ViewAny', 'View', 'Create', 'Update', 'Delete'];

        $permissions = [
            'owner' => [
                ...$this->resourcePermissions($resources, $manageActions),
                ...$widgetPermissions,
            ],
            'administrator_sistem' => [
                ...$this->resourcePermissions($resources, $manageActions),
                ...$widgetPermissions,
            ],
            'staff_gudang' => [
                ...$this->resourcePermissions(['Ingredient', 'Purchase', 'Distribution'], $manageActions),
                ...$this->resourcePermissions(['Stock', 'StockMovement', 'Sale'], $viewActions),
                ...$widgetPermissions,
            ],
            'karyawan_outlet' => [
                ...$this->resourcePermissions(['Sale'], $outletSaleActions),
                ...$this->resourcePermissions(['Stock', 'StockMovement'], $viewActions),
                ...$widgetPermissions,
            ],
        ];

        foreach ($permissions as $role => $rolePermissions) {
            Role::findByName($role)->syncPermissions($rolePermissions);
        }
    }

    private function resourcePermissions(array $resources, array $actions): array
    {
        $permissions = [];

        foreach ($resources as $resource) {
            foreach ($actions as $action) {
                $permissions[] = "{$action}:{$resource}";
            }
        }

        return $permissions;
    }
}

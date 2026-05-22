<?php

namespace Tests\Feature;

use App\Filament\Resources\Outlets\OutletResource;
use App\Filament\Resources\Purchases\PurchaseResource;
use App\Filament\Resources\Sales\SaleResource;
use App\Filament\Resources\Stocks\StockResource;
use App\Models\Outlet;
use App\Models\Sale;
use App\Models\Stock;
use App\Models\User;
use App\Policies\SalePolicy;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RbacTest extends TestCase
{
    use RefreshDatabase;

    public function test_seed_assigns_permissions_to_roles(): void
    {
        $this->seed(DatabaseSeeder::class);

        $owner = User::query()->where('role', 'owner')->first();
        $administrator = User::query()->where('role', 'administrator_sistem')->first();
        $staffGudang = User::query()->where('role', 'staff_gudang')->first();
        $karyawanOutlet = User::query()->where('role', 'karyawan_outlet')->first();

        $this->assertTrue($administrator->can('Create:Outlet'));
        $this->assertTrue($administrator->can('Create:User'));
        $this->assertTrue($administrator->can('ViewAny:Stock'));
        $this->assertTrue($staffGudang->can('Create:Purchase'));
        $this->assertTrue($staffGudang->can('Create:Distribution'));
        $this->assertFalse($staffGudang->can('Create:Sale'));
        $this->assertTrue($owner->can('ViewAny:Purchase'));
        $this->assertTrue($owner->can('Create:Purchase'));
        $this->assertTrue($karyawanOutlet->can('Create:Sale'));
        $this->assertTrue($karyawanOutlet->can('Update:Sale'));
        $this->assertTrue($karyawanOutlet->can('Delete:Sale'));
        $this->assertFalse($karyawanOutlet->can('DeleteAny:Sale'));
        $this->assertFalse($karyawanOutlet->can('ViewAny:Purchase'));
    }

    public function test_resource_create_permissions_follow_matrix(): void
    {
        $this->seed(DatabaseSeeder::class);

        $administrator = User::query()->where('role', 'administrator_sistem')->first();
        $staffGudang = User::query()->where('role', 'staff_gudang')->first();
        $karyawanOutlet = User::query()->where('role', 'karyawan_outlet')->first();
        $owner = User::query()->where('role', 'owner')->first();

        $this->actingAs($administrator);
        $this->assertTrue(OutletResource::canCreate());
        $this->assertTrue(PurchaseResource::canCreate());
        $this->assertTrue(SaleResource::canCreate());

        $this->actingAs($staffGudang);
        $this->assertFalse(OutletResource::canCreate());
        $this->assertTrue(PurchaseResource::canCreate());
        $this->assertFalse(SaleResource::canCreate());

        $this->actingAs($karyawanOutlet);
        $this->assertFalse(OutletResource::canCreate());
        $this->assertFalse(PurchaseResource::canCreate());
        $this->assertTrue(SaleResource::canCreate());

        $this->actingAs($owner);
        $this->assertTrue(PurchaseResource::canCreate());
        $this->assertTrue(SaleResource::canCreate());
    }

    public function test_branch_stock_query_is_scoped_to_own_outlet(): void
    {
        $this->seed(DatabaseSeeder::class);

        $ownOutlet = Outlet::query()->where('type', 'cabang')->first();
        $otherOutlet = Outlet::query()->create(['name' => 'Cabang 2', 'address' => 'Outlet cabang 2', 'type' => 'cabang']);
        $adminCabang = User::query()->where('role', 'karyawan_outlet')->first();

        Stock::query()->create(['outlet_id' => $ownOutlet->id, 'ingredient_id' => \App\Models\Ingredient::query()->create(['name' => 'A', 'unit' => 'kg', 'minimum_stock' => 1, 'usage_per_portion' => 1])->id, 'quantity' => 1]);
        Stock::query()->create(['outlet_id' => $otherOutlet->id, 'ingredient_id' => \App\Models\Ingredient::query()->create(['name' => 'B', 'unit' => 'kg', 'minimum_stock' => 1, 'usage_per_portion' => 1])->id, 'quantity' => 1]);

        $this->actingAs($adminCabang);

        $this->assertSame([$ownOutlet->id], StockResource::getEloquentQuery()->pluck('outlet_id')->unique()->values()->all());
    }

    public function test_outlet_employee_can_only_update_and_delete_own_sales(): void
    {
        $this->seed(DatabaseSeeder::class);

        $employee = User::query()->where('role', 'karyawan_outlet')->first();
        $otherEmployee = User::query()->create([
            'name' => 'Karyawan Lain',
            'email' => 'karyawan.lain@gmail.com',
            'username' => 'karyawan_lain',
            'password' => 'password',
            'role' => 'karyawan_outlet',
            'outlet_id' => $employee->outlet_id,
        ]);
        $ownSale = Sale::query()->create(['sale_date' => now(), 'outlet_id' => $employee->outlet_id, 'portion_qty' => 1, 'created_by' => $employee->id]);
        $otherSale = Sale::query()->create(['sale_date' => now(), 'outlet_id' => $employee->outlet_id, 'portion_qty' => 1, 'created_by' => $otherEmployee->id]);
        $policy = new SalePolicy();

        $this->assertTrue($policy->update($employee, $ownSale));
        $this->assertTrue($policy->delete($employee, $ownSale));
        $this->assertFalse($policy->update($employee, $otherSale));
        $this->assertFalse($policy->delete($employee, $otherSale));
        $this->assertFalse($policy->deleteAny($employee));
    }

    public function test_branch_sale_query_is_scoped_to_own_outlet(): void
    {
        $this->seed(DatabaseSeeder::class);

        $ownOutlet = Outlet::query()->where('type', 'cabang')->first();
        $otherOutlet = Outlet::query()->create(['name' => 'Cabang 2', 'address' => 'Outlet cabang 2', 'type' => 'cabang']);
        $adminCabang = User::query()->where('role', 'karyawan_outlet')->first();

        Sale::query()->create(['sale_date' => now(), 'outlet_id' => $ownOutlet->id, 'portion_qty' => 1, 'created_by' => $adminCabang->id]);
        Sale::query()->create(['sale_date' => now(), 'outlet_id' => $otherOutlet->id, 'portion_qty' => 1, 'created_by' => $adminCabang->id]);

        $this->actingAs($adminCabang);

        $this->assertSame([$ownOutlet->id], SaleResource::getEloquentQuery()->pluck('outlet_id')->unique()->values()->all());
    }
}

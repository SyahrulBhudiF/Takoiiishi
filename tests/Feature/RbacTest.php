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
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RbacTest extends TestCase
{
    use RefreshDatabase;

    public function test_seed_assigns_permissions_to_roles(): void
    {
        $this->seed(DatabaseSeeder::class);

        $adminPusat = User::query()->where('role', 'admin_pusat')->first();
        $adminCabang = User::query()->where('role', 'admin_cabang')->first();
        $pemilikPusat = User::query()->where('role', 'pemilik_pusat')->first();
        $pemilikCabang = User::query()->where('role', 'pemilik_cabang')->first();

        $this->assertTrue($adminPusat->can('Create:Purchase'));
        $this->assertTrue($adminCabang->can('Create:Sale'));
        $this->assertFalse($adminCabang->can('Create:Purchase'));
        $this->assertTrue($pemilikPusat->can('ViewAny:Purchase'));
        $this->assertFalse($pemilikPusat->can('Create:Purchase'));
        $this->assertTrue($pemilikCabang->can('ViewAny:Sale'));
        $this->assertFalse($pemilikCabang->can('ViewAny:Purchase'));
    }

    public function test_resource_create_permissions_follow_matrix(): void
    {
        $this->seed(DatabaseSeeder::class);

        $adminPusat = User::query()->where('role', 'admin_pusat')->first();
        $adminCabang = User::query()->where('role', 'admin_cabang')->first();
        $pemilikPusat = User::query()->where('role', 'pemilik_pusat')->first();

        $this->actingAs($adminPusat);
        $this->assertTrue(OutletResource::canCreate());
        $this->assertTrue(PurchaseResource::canCreate());
        $this->assertTrue(SaleResource::canCreate());

        $this->actingAs($adminCabang);
        $this->assertFalse(OutletResource::canCreate());
        $this->assertFalse(PurchaseResource::canCreate());
        $this->assertTrue(SaleResource::canCreate());

        $this->actingAs($pemilikPusat);
        $this->assertFalse(PurchaseResource::canCreate());
        $this->assertFalse(SaleResource::canCreate());
    }

    public function test_branch_stock_query_is_scoped_to_own_outlet(): void
    {
        $this->seed(DatabaseSeeder::class);

        $ownOutlet = Outlet::query()->where('type', 'cabang')->first();
        $otherOutlet = Outlet::query()->create(['name' => 'Cabang 2', 'address' => 'Outlet cabang 2', 'type' => 'cabang']);
        $adminCabang = User::query()->where('role', 'admin_cabang')->first();

        Stock::query()->create(['outlet_id' => $ownOutlet->id, 'ingredient_id' => \App\Models\Ingredient::query()->create(['name' => 'A', 'unit' => 'kg', 'minimum_stock' => 1, 'usage_per_portion' => 1])->id, 'quantity' => 1]);
        Stock::query()->create(['outlet_id' => $otherOutlet->id, 'ingredient_id' => \App\Models\Ingredient::query()->create(['name' => 'B', 'unit' => 'kg', 'minimum_stock' => 1, 'usage_per_portion' => 1])->id, 'quantity' => 1]);

        $this->actingAs($adminCabang);

        $this->assertSame([$ownOutlet->id], StockResource::getEloquentQuery()->pluck('outlet_id')->unique()->values()->all());
    }

    public function test_branch_sale_query_is_scoped_to_own_outlet(): void
    {
        $this->seed(DatabaseSeeder::class);

        $ownOutlet = Outlet::query()->where('type', 'cabang')->first();
        $otherOutlet = Outlet::query()->create(['name' => 'Cabang 2', 'address' => 'Outlet cabang 2', 'type' => 'cabang']);
        $adminCabang = User::query()->where('role', 'admin_cabang')->first();

        Sale::query()->create(['sale_date' => now(), 'outlet_id' => $ownOutlet->id, 'portion_qty' => 1, 'created_by' => $adminCabang->id]);
        Sale::query()->create(['sale_date' => now(), 'outlet_id' => $otherOutlet->id, 'portion_qty' => 1, 'created_by' => $adminCabang->id]);

        $this->actingAs($adminCabang);

        $this->assertSame([$ownOutlet->id], SaleResource::getEloquentQuery()->pluck('outlet_id')->unique()->values()->all());
    }
}

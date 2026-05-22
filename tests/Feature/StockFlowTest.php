<?php

namespace Tests\Feature;

use App\Models\Distribution;
use App\Models\Ingredient;
use App\Models\Outlet;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\User;
use App\Services\StockService;
use Database\Seeders\RoleAndUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class StockFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_purchase_adds_stock_to_warehouse(): void
    {
        $this->seed(RoleAndUserSeeder::class);

        $warehouse = Outlet::warehouse();
        $ingredient = Ingredient::query()->create([
            'name' => 'Tepung',
            'unit' => 'kg',
            'minimum_stock' => 1,
            'usage_per_portion' => 0.1,
        ]);
        $purchase = Purchase::query()->create([
            'purchase_date' => now(),
            'created_by' => User::query()->first()->id,
            'total' => 10000,
        ]);

        app(StockService::class)->add($warehouse->id, $ingredient->id, 5, 'purchase_in', $purchase->id);

        $this->assertDatabaseHas('stocks', [
            'outlet_id' => $warehouse->id,
            'ingredient_id' => $ingredient->id,
            'quantity' => 5,
        ]);
        $this->assertDatabaseHas('stock_movements', [
            'outlet_id' => $warehouse->id,
            'ingredient_id' => $ingredient->id,
            'type' => 'purchase_in',
            'qty_in' => 5,
            'reference' => $purchase->id,
        ]);
    }

    public function test_distribution_moves_stock_from_warehouse_to_outlet(): void
    {
        $this->seed(RoleAndUserSeeder::class);

        $warehouse = Outlet::warehouse();
        $cabang = Outlet::query()->where('type', 'cabang')->first();
        $ingredient = Ingredient::query()->create([
            'name' => 'Saus',
            'unit' => 'liter',
            'minimum_stock' => 1,
            'usage_per_portion' => 0.05,
        ]);
        $stock = app(StockService::class);
        $stock->add($warehouse->id, $ingredient->id, 10, 'purchase_in', 'seed');
        $distribution = Distribution::query()->create([
            'distribution_date' => now(),
            'from_outlet_id' => $warehouse->id,
            'to_outlet_id' => $cabang->id,
            'created_by' => User::query()->first()->id,
        ]);

        $stock->subtract($warehouse->id, $ingredient->id, 3, 'distribution_out', $distribution->id);
        $stock->add($cabang->id, $ingredient->id, 3, 'distribution_in', $distribution->id);

        $this->assertEquals(7, Stock::query()->where('outlet_id', $warehouse->id)->where('ingredient_id', $ingredient->id)->first()->quantity);
        $this->assertEquals(3, Stock::query()->where('outlet_id', $cabang->id)->where('ingredient_id', $ingredient->id)->first()->quantity);
        $this->assertDatabaseHas('stock_movements', ['type' => 'distribution_out', 'reference' => $distribution->id, 'qty_out' => 3]);
        $this->assertDatabaseHas('stock_movements', ['type' => 'distribution_in', 'reference' => $distribution->id, 'qty_in' => 3]);
    }

    public function test_sale_deducts_usage_per_portion_from_branch_stock(): void
    {
        $this->seed(RoleAndUserSeeder::class);

        $cabang = Outlet::query()->where('type', 'cabang')->first();
        $ingredient = Ingredient::query()->create([
            'name' => 'Adonan',
            'unit' => 'kg',
            'minimum_stock' => 1,
            'usage_per_portion' => 0.2,
        ]);
        $stock = app(StockService::class);
        $stock->add($cabang->id, $ingredient->id, 10, 'distribution_in', 'seed');
        $sale = Sale::query()->create([
            'sale_date' => now(),
            'outlet_id' => $cabang->id,
            'portion_qty' => 5,
            'created_by' => User::query()->first()->id,
        ]);

        $stock->subtract($cabang->id, $ingredient->id, 5 * 0.2, 'sale_out', $sale->id);

        $this->assertEquals(9, Stock::query()->where('outlet_id', $cabang->id)->where('ingredient_id', $ingredient->id)->first()->quantity);
        $this->assertDatabaseHas('stock_movements', ['type' => 'sale_out', 'reference' => $sale->id, 'qty_out' => 1]);
    }

    public function test_stock_cannot_go_negative(): void
    {
        $this->seed(RoleAndUserSeeder::class);

        $cabang = Outlet::query()->where('type', 'cabang')->first();
        $ingredient = Ingredient::query()->create([
            'name' => 'Bonito',
            'unit' => 'kg',
            'minimum_stock' => 1,
            'usage_per_portion' => 0.1,
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Stok tidak cukup.');

        app(StockService::class)->subtract($cabang->id, $ingredient->id, 1, 'sale_out', 'test');
    }

    public function test_low_stock_detection(): void
    {
        $this->seed(RoleAndUserSeeder::class);

        $cabang = Outlet::query()->where('type', 'cabang')->first();
        $ingredient = Ingredient::query()->create([
            'name' => 'Mayones',
            'unit' => 'liter',
            'minimum_stock' => 5,
            'usage_per_portion' => 0.1,
        ]);

        app(StockService::class)->add($cabang->id, $ingredient->id, 5, 'distribution_in', 'seed');

        $this->assertTrue(Stock::query()->with('ingredient')->first()->isLow());
    }
}

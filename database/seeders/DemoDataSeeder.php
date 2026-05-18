<?php

namespace Database\Seeders;

use App\Models\Distribution;
use App\Models\Ingredient;
use App\Models\Outlet;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\User;
use App\Services\StockService;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $pusat = Outlet::query()->firstOrCreate(
            ['name' => 'Pusat'],
            ['address' => 'Outlet pusat', 'type' => 'pusat'],
        );

        $cabangSatu = Outlet::query()->firstOrCreate(
            ['name' => 'Cabang 1'],
            ['address' => 'Outlet cabang 1', 'type' => 'cabang'],
        );

        $cabangDua = Outlet::query()->firstOrCreate(
            ['name' => 'Cabang 2'],
            ['address' => 'Outlet cabang 2', 'type' => 'cabang'],
        );

        $ingredients = collect([
            ['name' => 'Tepung Takoyaki', 'unit' => 'kg', 'minimum_stock' => 5, 'usage_per_portion' => 0.08, 'purchase_qty' => 50, 'price' => 28000, 'dist_one' => 12, 'dist_two' => 8],
            ['name' => 'Gurita', 'unit' => 'kg', 'minimum_stock' => 3, 'usage_per_portion' => 0.05, 'purchase_qty' => 25, 'price' => 95000, 'dist_one' => 8, 'dist_two' => 5],
            ['name' => 'Saus Takoyaki', 'unit' => 'liter', 'minimum_stock' => 4, 'usage_per_portion' => 0.03, 'purchase_qty' => 30, 'price' => 32000, 'dist_one' => 10, 'dist_two' => 6],
            ['name' => 'Mayones', 'unit' => 'liter', 'minimum_stock' => 4, 'usage_per_portion' => 0.02, 'purchase_qty' => 24, 'price' => 30000, 'dist_one' => 8, 'dist_two' => 5],
            ['name' => 'Katsuobushi', 'unit' => 'kg', 'minimum_stock' => 2, 'usage_per_portion' => 0.01, 'purchase_qty' => 10, 'price' => 120000, 'dist_one' => 3, 'dist_two' => 2],
        ])->map(fn (array $data): array => [
            'model' => Ingredient::query()->updateOrCreate(
                ['name' => $data['name']],
                [
                    'unit' => $data['unit'],
                    'minimum_stock' => $data['minimum_stock'],
                    'usage_per_portion' => $data['usage_per_portion'],
                ],
            ),
            'seed' => $data,
        ]);

        $admin = User::query()->where('role', 'staff_gudang')->first();
        $stock = app(StockService::class);

        $purchase = Purchase::query()->firstOrCreate(
            ['purchase_date' => now()->subDays(7)->toDateString(), 'created_by' => $admin->id],
            ['total' => 0],
        );

        $total = 0;

        foreach ($ingredients as $item) {
            /** @var Ingredient $ingredient */
            $ingredient = $item['model'];
            $seed = $item['seed'];
            $subtotal = $seed['purchase_qty'] * $seed['price'];
            $total += $subtotal;

            $purchase->items()->firstOrCreate(
                ['ingredient_id' => $ingredient->id],
                ['quantity' => $seed['purchase_qty'], 'price' => $seed['price'], 'subtotal' => $subtotal],
            );

            $stock->add($pusat->id, $ingredient->id, $seed['purchase_qty'], 'purchase_in', $purchase->id);
        }

        $purchase->update(['total' => $total]);

        $distributionOne = Distribution::query()->firstOrCreate(
            ['distribution_date' => now()->subDays(5)->toDateString(), 'to_outlet_id' => $cabangSatu->id],
            ['from_outlet_id' => $pusat->id, 'created_by' => $admin->id],
        );

        $distributionTwo = Distribution::query()->firstOrCreate(
            ['distribution_date' => now()->subDays(4)->toDateString(), 'to_outlet_id' => $cabangDua->id],
            ['from_outlet_id' => $pusat->id, 'created_by' => $admin->id],
        );

        foreach ($ingredients as $item) {
            /** @var Ingredient $ingredient */
            $ingredient = $item['model'];
            $seed = $item['seed'];

            $distributionOne->items()->firstOrCreate(['ingredient_id' => $ingredient->id], ['quantity' => $seed['dist_one']]);
            $stock->subtract($pusat->id, $ingredient->id, $seed['dist_one'], 'distribution_out', $distributionOne->id);
            $stock->add($cabangSatu->id, $ingredient->id, $seed['dist_one'], 'distribution_in', $distributionOne->id);

            $distributionTwo->items()->firstOrCreate(['ingredient_id' => $ingredient->id], ['quantity' => $seed['dist_two']]);
            $stock->subtract($pusat->id, $ingredient->id, $seed['dist_two'], 'distribution_out', $distributionTwo->id);
            $stock->add($cabangDua->id, $ingredient->id, $seed['dist_two'], 'distribution_in', $distributionTwo->id);
        }

        $this->seedSale($cabangSatu, 80, now()->subDays(2), $admin);
        $this->seedSale($cabangDua, 55, now()->subDay(), $admin);
    }

    private function seedSale(Outlet $outlet, int $portionQty, mixed $date, User $admin): void
    {
        $sale = Sale::query()->firstOrCreate(
            ['sale_date' => $date->toDateString(), 'outlet_id' => $outlet->id],
            ['portion_qty' => $portionQty, 'created_by' => $admin->id],
        );

        Ingredient::query()
            ->where('usage_per_portion', '>', 0)
            ->each(function (Ingredient $ingredient) use ($outlet, $portionQty, $sale): void {
                app(StockService::class)->subtract(
                    $outlet->id,
                    $ingredient->id,
                    $portionQty * (float) $ingredient->usage_per_portion,
                    'sale_out',
                    $sale->id,
                );
            });
    }
}

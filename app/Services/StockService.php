<?php

namespace App\Services;

use App\Models\Stock;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class StockService
{
    public function add(string $outletId, string $ingredientId, float $quantity, string $type, ?string $reference = null): Stock
    {
        $this->assertPositive($quantity);

        return DB::transaction(function () use ($outletId, $ingredientId, $quantity, $type, $reference): Stock {
            $stock = $this->lockStock($outletId, $ingredientId);
            $stock->quantity += $quantity;
            $stock->save();

            $this->movement($outletId, $ingredientId, $type, $quantity, 0, $reference);

            return $stock;
        });
    }

    public function subtract(string $outletId, string $ingredientId, float $quantity, string $type, ?string $reference = null): Stock
    {
        $this->assertPositive($quantity);

        return DB::transaction(function () use ($outletId, $ingredientId, $quantity, $type, $reference): Stock {
            $stock = $this->lockStock($outletId, $ingredientId);

            if ((float) $stock->quantity < $quantity) {
                throw new InvalidArgumentException('Stok tidak cukup.');
            }

            $stock->quantity -= $quantity;
            $stock->save();

            $this->movement($outletId, $ingredientId, $type, 0, $quantity, $reference);

            return $stock;
        });
    }

    private function lockStock(string $outletId, string $ingredientId): Stock
    {
        $stock = Stock::query()
            ->where('outlet_id', $outletId)
            ->where('ingredient_id', $ingredientId)
            ->lockForUpdate()
            ->first();

        if ($stock) {
            return $stock;
        }

        return Stock::query()->create([
            'outlet_id' => $outletId,
            'ingredient_id' => $ingredientId,
            'quantity' => 0,
        ]);
    }

    private function movement(string $outletId, string $ingredientId, string $type, float $qtyIn, float $qtyOut, ?string $reference): void
    {
        StockMovement::query()->create([
            'outlet_id' => $outletId,
            'ingredient_id' => $ingredientId,
            'type' => $type,
            'qty_in' => $qtyIn,
            'qty_out' => $qtyOut,
            'reference' => $reference,
        ]);
    }

    private function assertPositive(float $quantity): void
    {
        if ($quantity <= 0) {
            throw new InvalidArgumentException('Jumlah stok harus lebih dari 0.');
        }
    }
}

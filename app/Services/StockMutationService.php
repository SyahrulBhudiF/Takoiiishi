<?php

namespace App\Services;

use App\Models\StockMovement;
use App\Models\StockMutation;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class StockMutationService
{
    public function __construct(private readonly StockService $stock) {}

    public function canModify(StockMutation $mutation): bool
    {
        if (! $mutation->isCompleted()) {
            return false;
        }

        $ingredientIds = $mutation->items()->pluck('ingredient_id');

        if ($ingredientIds->isEmpty()) {
            return true;
        }

        $cutoffTime = $mutation->created_at;
        $relatedOutletIds = [$mutation->from_outlet_id, $mutation->to_outlet_id];
        $currentMovementIds = StockMovement::query()
            ->where('reference', $mutation->id)
            ->pluck('id');

        return ! StockMovement::query()
            ->whereIn('ingredient_id', $ingredientIds)
            ->whereIn('outlet_id', $relatedOutletIds)
            ->whereNotIn('id', $currentMovementIds)
            ->where(function ($query) use ($cutoffTime): void {
                $query->where('created_at', '>', $cutoffTime)
                    ->orWhere('created_at', $cutoffTime);
            })
            ->exists();
    }

    public function assertCanModify(StockMutation $mutation): void
    {
        if (! $this->canModify($mutation)) {
            throw new InvalidArgumentException('Mutasi stok tidak dapat diubah karena sudah ada transaksi stok setelah mutasi ini.');
        }
    }

    public function create(array $data, array $items): StockMutation
    {
        return DB::transaction(function () use ($data, $items): StockMutation {
            $this->assertDifferentOutlets($data);

            $mutation = StockMutation::query()->create($data + ['status' => 'completed']);
            $this->replaceItems($mutation, $items);
            $this->apply($mutation);

            return $mutation;
        });
    }

    public function update(StockMutation $mutation, array $data, array $items): StockMutation
    {
        return DB::transaction(function () use ($mutation, $data, $items): StockMutation {
            $this->assertDifferentOutlets($data);
            $mutation->refresh();
            $this->assertCanModify($mutation);
            $this->reverse($mutation);
            $mutation->update($data);
            $this->replaceItems($mutation, $items);
            $this->apply($mutation->refresh());

            return $mutation;
        });
    }

    public function cancel(StockMutation $mutation, ?string $reason = null): StockMutation
    {
        return DB::transaction(function () use ($mutation, $reason): StockMutation {
            $mutation->refresh();
            $this->assertCanModify($mutation);
            $this->reverse($mutation);
            $mutation->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancelled_by' => auth()->id(),
                'cancel_reason' => $reason,
            ]);

            return $mutation;
        });
    }

    public function apply(StockMutation $mutation): void
    {
        foreach ($mutation->items as $item) {
            $this->stock->subtract(
                $mutation->from_outlet_id,
                $item->ingredient_id,
                (float) $item->quantity,
                'mutation_out',
                $mutation->id,
            );

            $this->stock->add(
                $mutation->to_outlet_id,
                $item->ingredient_id,
                (float) $item->quantity,
                'mutation_in',
                $mutation->id,
            );
        }
    }

    public function reverse(StockMutation $mutation): void
    {
        foreach ($mutation->items as $item) {
            $this->stock->add(
                $mutation->from_outlet_id,
                $item->ingredient_id,
                (float) $item->quantity,
                'mutation_reverse_in',
                $mutation->id,
            );

            $this->stock->subtract(
                $mutation->to_outlet_id,
                $item->ingredient_id,
                (float) $item->quantity,
                'mutation_reverse_out',
                $mutation->id,
            );
        }
    }

    private function replaceItems(StockMutation $mutation, array $items): void
    {
        $mutation->items()->delete();

        foreach ($items as $item) {
            $mutation->items()->create($item);
        }

        $mutation->load('items');
    }

    private function assertDifferentOutlets(array $data): void
    {
        if (($data['from_outlet_id'] ?? null) === ($data['to_outlet_id'] ?? null)) {
            throw new InvalidArgumentException('Outlet asal dan tujuan tidak boleh sama.');
        }
    }
}

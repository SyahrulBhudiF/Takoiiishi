<?php

namespace App\Services;

use App\Models\Distribution;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class DistributionStockService
{
    public function __construct(private readonly StockService $stock) {}

    public function canModify(Distribution $distribution): bool
    {
        if (! $distribution->isCompleted()) {
            return false;
        }

        $ingredientIds = $distribution->items()->pluck('ingredient_id');

        if ($ingredientIds->isEmpty()) {
            return true;
        }

        $cutoffTime = $distribution->created_at;
        $relatedOutletIds = [$distribution->from_outlet_id, $distribution->to_outlet_id];
        $currentDistributionMovementIds = StockMovement::query()
            ->where('reference', $distribution->id)
            ->pluck('id');

        return ! StockMovement::query()
            ->whereIn('ingredient_id', $ingredientIds)
            ->whereIn('outlet_id', $relatedOutletIds)
            ->whereNotIn('id', $currentDistributionMovementIds)
            ->where(function ($query) use ($cutoffTime): void {
                $query->where('created_at', '>', $cutoffTime)
                    ->orWhere('created_at', $cutoffTime);
            })
            ->exists();
    }

    public function assertCanModify(Distribution $distribution): void
    {
        if (! $this->canModify($distribution)) {
            throw new InvalidArgumentException('Distribusi tidak dapat diubah karena sudah ada transaksi stok setelah distribusi ini.');
        }
    }

    public function create(array $data, array $items): Distribution
    {
        return DB::transaction(function () use ($data, $items): Distribution {
            $distribution = Distribution::query()->create($data + ['status' => 'completed']);
            $this->replaceItems($distribution, $items);
            $this->apply($distribution);

            return $distribution;
        });
    }

    public function update(Distribution $distribution, array $data, array $items): Distribution
    {
        return DB::transaction(function () use ($distribution, $data, $items): Distribution {
            $distribution->refresh();
            $this->assertCanModify($distribution);
            $this->reverse($distribution);
            $distribution->update($data);
            $this->replaceItems($distribution, $items);
            $this->apply($distribution->refresh());

            return $distribution;
        });
    }

    public function cancel(Distribution $distribution, ?string $reason = null): Distribution
    {
        return DB::transaction(function () use ($distribution, $reason): Distribution {
            $distribution->refresh();
            $this->assertCanModify($distribution);
            $this->reverse($distribution);
            $distribution->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancelled_by' => auth()->id(),
                'cancel_reason' => $reason,
            ]);

            return $distribution;
        });
    }

    public function apply(Distribution $distribution): void
    {
        foreach ($distribution->items as $item) {
            $this->stock->subtract(
                $distribution->from_outlet_id,
                $item->ingredient_id,
                (float) $item->quantity,
                'distribution_out',
                $distribution->id,
            );

            $this->stock->add(
                $distribution->to_outlet_id,
                $item->ingredient_id,
                (float) $item->quantity,
                'distribution_in',
                $distribution->id,
            );
        }
    }

    public function reverse(Distribution $distribution): void
    {
        foreach ($distribution->items as $item) {
            $this->stock->add(
                $distribution->from_outlet_id,
                $item->ingredient_id,
                (float) $item->quantity,
                'distribution_reverse_in',
                $distribution->id,
            );

            $this->stock->subtract(
                $distribution->to_outlet_id,
                $item->ingredient_id,
                (float) $item->quantity,
                'distribution_reverse_out',
                $distribution->id,
            );
        }
    }

    private function replaceItems(Distribution $distribution, array $items): void
    {
        $distribution->items()->delete();

        foreach ($items as $item) {
            $distribution->items()->create($item);
        }

        $distribution->load('items');
    }
}

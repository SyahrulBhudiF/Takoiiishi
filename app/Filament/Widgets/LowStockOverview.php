<?php

namespace App\Filament\Widgets;

use App\Enums\UserRole;
use App\Models\Stock;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class LowStockOverview extends StatsOverviewWidget
{
    use HasWidgetShield;

    protected function getStats(): array
    {
        $query = Stock::query()
            ->join('ingredients', 'stocks.ingredient_id', '=', 'ingredients.id')
            ->whereColumn('stocks.quantity', '<=', 'ingredients.minimum_stock');

        $user = auth()->user();

        if ($user && UserRole::tryFrom($user->role->value ?? $user->role)?->isBranchScoped()) {
            $query->where('stocks.outlet_id', $user->outlet_id);
        }

        return [
            Stat::make('Stok minimum', $query->count())
                ->description('Bahan perlu restock')
                ->color('danger'),
        ];
    }
}

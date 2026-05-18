<?php

namespace App\Filament\Widgets;

use App\Enums\UserRole;
use App\Models\Stock;
use Filament\Widgets\ChartWidget;
use Livewire\Attributes\On;

class StockByOutletChart extends ChartWidget
{
    protected static ?int $sort = 3;

    protected ?string $heading = 'Komposisi Stok Outlet';

    protected ?string $description = 'Total kuantitas stok per outlet';

    protected string $color = 'primary';

    protected int|string|array $columnSpan = ['default' => 12, 'lg' => 4];

    protected ?string $maxHeight = '280px';

    public ?string $outletId = null;

    public function mount(): void
    {
        $this->outletId = request()->query('outletId');
    }

    #[On('dashboard-filter-updated')]
    public function updateFilter(?string $startDate = null, ?string $endDate = null, ?string $outletId = null): void
    {
        $this->outletId = $outletId;
        $this->cachedData = null;
    }

    protected function getData(): array
    {
        $user = auth()->user();
        $role = UserRole::parse($user?->role);

        $query = Stock::query()
            ->join('outlets', 'stocks.outlet_id', '=', 'outlets.id')
            ->selectRaw('outlets.name as outlet_name, SUM(stocks.quantity) as total_stock')
            ->groupBy('outlets.id', 'outlets.name')
            ->orderBy('outlets.type')
            ->orderBy('outlets.name');

        if ($role?->isOutletScoped()) {
            $query->where('stocks.outlet_id', $user->outlet_id);
        } elseif ($this->outletId) {
            $query->where('stocks.outlet_id', $this->outletId);
        }

        $rows = $query->get();

        return [
            'datasets' => [
                [
                    'label' => 'Total stok',
                    'data' => $rows->pluck('total_stock')->map(fn($total) => round((float) $total, 2))->all(),
                    'backgroundColor' => ['#9333ea', '#a855f7', '#c084fc', '#d8b4fe'],
                    'borderColor' => '#ffffff',
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $rows->pluck('outlet_name')->all(),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                    'labels' => ['boxWidth' => 12, 'padding' => 16],
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}

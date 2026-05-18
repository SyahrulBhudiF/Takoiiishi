<?php

namespace App\Filament\Widgets;

use App\Enums\UserRole;
use App\Models\Sale;
use Carbon\CarbonPeriod;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Livewire\Attributes\On;

class DailySalesChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected ?string $heading = 'Tren Penjualan';

    protected ?string $description = 'Porsi terjual dalam periode yang dipilih';

    protected string $color = 'primary';

    protected int|string|array $columnSpan = ['default' => 12, 'lg' => 8];

    protected ?string $maxHeight = '280px';

    public ?string $startDate = null;
    public ?string $endDate = null;
    public ?string $outletId = null;

    public function mount(): void
    {
        $this->startDate = request()->query('startDate', now()->subDays(6)->toDateString());
        $this->endDate = request()->query('endDate', now()->toDateString());
        $this->outletId = request()->query('outletId');
    }

    #[On('dashboard-filter-updated')]
    public function updateFilter(?string $startDate = null, ?string $endDate = null, ?string $outletId = null): void
    {
        $this->startDate = $startDate ?? now()->subDays(6)->toDateString();
        $this->endDate = $endDate ?? now()->toDateString();
        $this->outletId = $outletId;
        $this->cachedData = null;
    }

    protected function getData(): array
    {
        $user = auth()->user();
        $role = UserRole::parse($user?->role);

        $outletId = $this->outletId;
        if ($role?->isOutletScoped()) {
            $outletId = $user->outlet_id;
        }

        $startDate = Carbon::parse($this->startDate)->startOfDay();
        $endDate = Carbon::parse($this->endDate)->endOfDay();

        $query = Sale::query()
            ->selectRaw('DATE(sale_date) as sale_day, SUM(portion_qty) as total_portions')
            ->whereBetween('sale_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->groupBy('sale_day')
            ->orderBy('sale_day');

        if ($outletId) {
            $query->where('outlet_id', $outletId);
        }

        $totals = $query
            ->pluck('total_portions', 'sale_day')
            ->map(fn($total) => (int) $total);

        $labels = [];
        $data = [];

        foreach (CarbonPeriod::create($startDate, $endDate) as $date) {
            $key = $date->toDateString();
            $labels[] = Carbon::parse($date)->translatedFormat('d M');
            $data[] = $totals->get($key, 0);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Porsi',
                    'data' => $data,
                    'borderColor' => '#9333ea',
                    'backgroundColor' => 'rgba(147, 51, 234, 0.12)',
                    'pointBackgroundColor' => '#9333ea',
                    'pointBorderColor' => '#ffffff',
                    'pointRadius' => 4,
                    'fill' => true,
                    'tension' => 0.32,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => ['display' => false],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => ['precision' => 0],
                    'grid' => ['color' => 'rgba(148, 163, 184, 0.18)'],
                ],
                'x' => [
                    'grid' => ['display' => false],
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}

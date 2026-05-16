<?php

namespace App\Filament\Widgets;

use App\Enums\UserRole;
use App\Models\Distribution;
use App\Models\Ingredient;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Stock;
use App\Models\StockMovement;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Livewire\Attributes\On;

class DashboardOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    public ?string $startDate = null;
    public ?string $endDate = null;
    public ?string $outletId = null;

    public function mount(): void
    {
        $this->startDate = request()->query('startDate', now()->startOfMonth()->toDateString());
        $this->endDate = request()->query('endDate', now()->toDateString());
        $this->outletId = request()->query('outletId');
    }

    #[On('dashboard-filter-updated')]
    public function updateFilter(?string $startDate = null, ?string $endDate = null, ?string $outletId = null): void
    {
        $this->startDate = $startDate ?? now()->startOfMonth()->toDateString();
        $this->endDate = $endDate ?? now()->toDateString();
        $this->outletId = $outletId;
        $this->cachedStats = null;
    }

    protected function getColumns(): int|array
    {
        return [
            'default' => 1,
            'md' => 2,
            'xl' => 3,
        ];
    }

    protected function getStats(): array
    {
        $user = auth()->user();
        $role = UserRole::parse($user?->role);

        $outletId = $this->outletId;
        if ($role?->isBranchScoped()) {
            $outletId = $user->outlet_id;
        }

        $startDate = $this->startDate;
        $endDate = $this->endDate;

        // Base queries
        $stockQuery = Stock::query();
        $saleQuery = Sale::query()->whereBetween('sale_date', [$startDate, $endDate]);
        $movementQuery = StockMovement::query()->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        $lowStockQuery = Stock::query()
            ->join('ingredients', 'stocks.ingredient_id', '=', 'ingredients.id')
            ->whereColumn('stocks.quantity', '<=', 'ingredients.minimum_stock');

        // Apply outlet filter
        if ($outletId) {
            $stockQuery->where('outlet_id', $outletId);
            $saleQuery->where('outlet_id', $outletId);
            $movementQuery->where('outlet_id', $outletId);
            $lowStockQuery->where('stocks.outlet_id', $outletId);
        }

        $totalSales = (int) (clone $saleQuery)->sum('portion_qty');
        $totalSalesCount = (clone $saleQuery)->count();
        $lowStockCount = (clone $lowStockQuery)->count();
        $totalStock = (float) (clone $stockQuery)->sum('quantity');
        $totalIngredients = Ingredient::count();
        $movementCount = (clone $movementQuery)->count();

        // Calculate trends (compare with previous period)
        $periodDays = max(1, now()->parse($startDate)->diffInDays(now()->parse($endDate)) + 1);
        $prevStartDate = now()->parse($startDate)->subDays($periodDays)->toDateString();
        $prevEndDate = now()->parse($startDate)->subDay()->toDateString();

        $prevSaleQuery = Sale::query()->whereBetween('sale_date', [$prevStartDate, $prevEndDate]);
        if ($outletId) {
            $prevSaleQuery->where('outlet_id', $outletId);
        }
        $prevTotalSales = (int) $prevSaleQuery->sum('portion_qty');

        $salesTrend = $prevTotalSales > 0
            ? round((($totalSales - $prevTotalSales) / $prevTotalSales) * 100, 1)
            : ($totalSales > 0 ? 100 : 0);

        $stats = [
            Stat::make('Total Penjualan', number_format($totalSales) . ' porsi')
                ->description($totalSalesCount . ' transaksi | ' . ($salesTrend >= 0 ? '+' : '') . $salesTrend . '% dari periode sebelumnya')
                ->descriptionIcon($salesTrend >= 0 ? Heroicon::OutlinedArrowTrendingUp : Heroicon::OutlinedArrowTrendingDown)
                ->color($salesTrend >= 0 ? 'success' : 'danger')
                ->chart($this->getSalesChartData($outletId, $startDate, $endDate)),

            Stat::make('Stok Menipis', $lowStockCount . ' bahan')
                ->description($lowStockCount > 0 ? 'Perlu restock segera!' : 'Semua stok aman')
                ->descriptionIcon($lowStockCount > 0 ? Heroicon::OutlinedExclamationTriangle : Heroicon::OutlinedCheckCircle)
                ->color($lowStockCount > 0 ? 'danger' : 'success'),

            Stat::make('Total Stok', number_format($totalStock, 2))
                ->description($totalIngredients . ' jenis bahan terdaftar')
                ->descriptionIcon(Heroicon::OutlinedCircleStack)
                ->color('info'),

            Stat::make('Aktivitas Stok', $movementCount . ' mutasi')
                ->description('Dalam periode yang dipilih')
                ->descriptionIcon(Heroicon::OutlinedArrowsRightLeft)
                ->color('gray'),
        ];

        // Additional stats for pusat roles
        if ($role === UserRole::AdminPusat || $role === UserRole::PemilikPusat) {
            $distQuery = Distribution::query()->whereBetween('distribution_date', [$startDate, $endDate]);
            $distCount = $distQuery->count();

            $stats[] = Stat::make('Distribusi', $distCount . ' pengiriman')
                ->description('Pusat ke cabang')
                ->descriptionIcon(Heroicon::OutlinedTruck)
                ->color('warning');
        }

        if ($role === UserRole::AdminPusat) {
            $purchaseQuery = Purchase::query()->whereBetween('purchase_date', [$startDate, $endDate]);
            $purchaseCount = $purchaseQuery->count();
            $purchaseTotal = $purchaseQuery->sum('total');

            $stats[] = Stat::make('Pembelian', $purchaseCount . ' transaksi')
                ->description('Total: Rp ' . number_format($purchaseTotal, 0, ',', '.'))
                ->descriptionIcon(Heroicon::OutlinedShoppingCart)
                ->color('primary');
        }

        return $stats;
    }

    protected function getSalesChartData(?string $outletId, string $startDate, string $endDate): array
    {
        $query = Sale::query()
            ->selectRaw('DATE(sale_date) as date, SUM(portion_qty) as total')
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date');

        if ($outletId) {
            $query->where('outlet_id', $outletId);
        }

        $data = $query->pluck('total', 'date')->toArray();

        $period = new \DatePeriod(
            new \DateTime($startDate),
            new \DateInterval('P1D'),
            (new \DateTime($endDate))->modify('+1 day')
        );

        $chartData = [];
        foreach ($period as $date) {
            $key = $date->format('Y-m-d');
            $chartData[] = (int) ($data[$key] ?? 0);
        }

        return array_slice($chartData, -7);
    }
}

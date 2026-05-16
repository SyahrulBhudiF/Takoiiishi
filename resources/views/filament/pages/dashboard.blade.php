<x-filament-panels::page>
    <div class="takoyaki-dashboard">
        <div class="takoyaki-dashboard__full">
            @livewire(\App\Filament\Widgets\DashboardFilter::class)
        </div>

        <div class="takoyaki-dashboard__full">
            @livewire(\App\Filament\Widgets\DashboardOverview::class)
        </div>

        <div class="takoyaki-dashboard__charts">
            <div class="takoyaki-dashboard__chart-main">
                @livewire(\App\Filament\Widgets\DailySalesChart::class)
            </div>

            <div class="takoyaki-dashboard__chart-side">
                @livewire(\App\Filament\Widgets\StockByOutletChart::class)
            </div>
        </div>
    </div>
</x-filament-panels::page>

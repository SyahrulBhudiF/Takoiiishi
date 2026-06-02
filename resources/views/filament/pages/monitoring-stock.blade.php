<x-filament-panels::page>
    <div class="flex flex-col gap-2">
        <div>
            <x-filament::tabs :contained="true">
                <x-filament::tabs.item
                    :active="$activeTab === 'current'"
                    wire:click="setActiveTab('current')"
                    icon="heroicon-o-circle-stack"
                    :badge="$this->getStockCount()"
                    :badge-color="$activeTab === 'current' ? 'primary' : 'gray'"
                >
                    Stok Saat Ini
                </x-filament::tabs.item>

                <x-filament::tabs.item
                    :active="$activeTab === 'history'"
                    wire:click="setActiveTab('history')"
                    icon="heroicon-o-clock"
                    :badge="$this->getMovementCount()"
                    :badge-color="$activeTab === 'history' ? 'primary' : 'gray'"
                >
                    Riwayat Stok
                </x-filament::tabs.item>
            </x-filament::tabs>
        </div>

        @if ($activeTab === 'current' && $this->getLowWarehouseStockCount() > 0)
            <div class="rounded-lg border border-danger-200 bg-danger-50 px-4 py-3 text-sm text-danger-700 dark:border-danger-800 dark:bg-danger-950 dark:text-danger-300">
                <div class="font-semibold">Stok gudang menipis</div>
                <div>{{ $this->getLowWarehouseStockCount() }} bahan di gudang sudah mencapai / di bawah stok minimum: {{ $this->getLowWarehouseStockSummary() }}.</div>
            </div>
        @endif

        {{ $this->table }}
    </div>
</x-filament-panels::page>

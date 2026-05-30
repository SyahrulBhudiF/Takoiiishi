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

        {{ $this->table }}
    </div>
</x-filament-panels::page>

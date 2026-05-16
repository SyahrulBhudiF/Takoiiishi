<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Filter Dashboard
        </x-slot>

        <x-slot name="headerEnd">
            <x-filament::button
                color="gray"
                size="sm"
                wire:click="resetFilter"
            >
                Reset
            </x-filament::button>
        </x-slot>

        {{ $this->form }}
    </x-filament::section>
</x-filament-widgets::widget>

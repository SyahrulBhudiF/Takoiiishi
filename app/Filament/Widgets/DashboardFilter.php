<?php

namespace App\Filament\Widgets;

use App\Enums\UserRole;
use App\Models\Outlet;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Filament\Widgets\Widget;
use Livewire\Attributes\Url;

class DashboardFilter extends Widget implements HasForms
{
    use InteractsWithForms;
    protected string $view = 'filament.widgets.dashboard-filter';

    protected static ?int $sort = 0;

    protected int|string|array $columnSpan = 'full';

    #[Url]
    public ?string $startDate = null;

    #[Url]
    public ?string $endDate = null;

    #[Url]
    public ?string $outletId = null;

    public function mount(): void
    {
        $this->startDate = $this->startDate ?? now()->startOfMonth()->toDateString();
        $this->endDate = $this->endDate ?? now()->toDateString();

        $user = auth()->user();
        $role = UserRole::parse($user?->role);

        if ($role?->isBranchScoped()) {
            $this->outletId = (string) $user->outlet_id;
        }
    }

    public function form(Schema $schema): Schema
    {
        $user = auth()->user();
        $role = UserRole::parse($user?->role);

        return $schema
            ->components([
                DatePicker::make('startDate')
                    ->label('Dari Tanggal')
                    ->native(false)
                    ->displayFormat('d M Y')
                    ->live()
                    ->afterStateUpdated(fn () => $this->dispatchFilterUpdated()),

                DatePicker::make('endDate')
                    ->label('Sampai Tanggal')
                    ->native(false)
                    ->displayFormat('d M Y')
                    ->live()
                    ->afterStateUpdated(fn () => $this->dispatchFilterUpdated()),

                Select::make('outletId')
                    ->label('Outlet')
                    ->options(fn () => Outlet::pluck('name', 'id')->toArray())
                    ->placeholder('Semua Outlet')
                    ->native(false)
                    ->live()
                    ->visible(fn () => !$role?->isBranchScoped())
                    ->afterStateUpdated(fn () => $this->dispatchFilterUpdated()),
            ])
            ->columns([
                'default' => 1,
                'md' => 2,
                'xl' => 3,
            ]);
    }

    public function dispatchFilterUpdated(): void
    {
        $this->dispatch(
            'dashboard-filter-updated',
            startDate: $this->startDate,
            endDate: $this->endDate,
            outletId: $this->outletId,
        );
    }

    public function resetFilter(): void
    {
        $this->startDate = now()->startOfMonth()->toDateString();
        $this->endDate = now()->toDateString();

        $user = auth()->user();
        $role = UserRole::parse($user?->role);

        if (!$role?->isBranchScoped()) {
            $this->outletId = null;
        }

        $this->dispatchFilterUpdated();
    }
}

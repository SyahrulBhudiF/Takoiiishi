<?php

namespace App\Filament\Pages;

use App\Enums\UserRole;
use App\Filament\Exports\StockExporter;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\User;
use App\Support\DateFormat;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ExportAction;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class MonitoringStock extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBarSquare;

    protected static ?string $navigationLabel = 'Monitoring Stok';

    protected static ?string $title = 'Monitoring Stok';

    protected static ?int $navigationSort = 60;

    protected string $view = 'filament.pages.monitoring-stock';

    public string $activeTab = 'current';

    protected $queryString = ['activeTab'];

    protected function getHeaderActions(): array
    {
        return [
            ExportAction::make()
                ->label('Ekspor Stok')
                ->exporter(StockExporter::class)
                ->formats([ExportFormat::Csv, ExportFormat::Xlsx])
                ->fileName(fn () => 'laporan-stok-' . now()->format('Y-m-d-His'))
                ->visible(fn () => $this->activeTab === 'current'),
        ];
    }

    private const TYPE_LABELS = [
        'purchase_in' => 'Pembelian',
        'distribution_out' => 'Distribusi Keluar',
        'distribution_in' => 'Distribusi Masuk',
        'distribution_reverse_in' => 'Batal Masuk',
        'distribution_reverse_out' => 'Batal Keluar',
        'mutation_out' => 'Mutasi Keluar',
        'mutation_in' => 'Mutasi Masuk',
        'mutation_reverse_in' => 'Batal Mutasi Masuk',
        'mutation_reverse_out' => 'Batal Mutasi Keluar',
        'sale_out' => 'Penjualan',
    ];

    private const TYPE_COLORS = [
        'purchase_in' => 'success',
        'distribution_out' => 'warning',
        'distribution_in' => 'info',
        'distribution_reverse_in' => 'gray',
        'distribution_reverse_out' => 'gray',
        'mutation_out' => 'warning',
        'mutation_in' => 'info',
        'mutation_reverse_in' => 'gray',
        'mutation_reverse_out' => 'gray',
        'sale_out' => 'danger',
    ];

    public function mount(): void
    {
        $this->syncLowWarehouseStockNotification();
    }

    public function setActiveTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetTable();
    }

    public function table(Table $table): Table
    {
        if ($this->activeTab === 'history') {
            return $this->stockMovementTable($table);
        }

        return $this->stockTable($table);
    }

    protected function stockTable(Table $table): Table
    {
        return $table
            ->query($this->getStockQuery())
            ->columns([
                TextColumn::make('outlet.name')
                    ->label('Outlet')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('ingredient.name')
                    ->label('Bahan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('quantity')
                    ->label('Stok')
                    ->numeric()
                    ->sortable()
                    ->color(fn ($record): string => $record->isLow() ? 'danger' : 'success')
                    ->weight(fn ($record): string => $record->isLow() ? 'bold' : 'normal'),
                TextColumn::make('ingredient.unit')
                    ->label('Satuan'),
                TextColumn::make('ingredient.minimum_stock')
                    ->label('Minimum')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_enough')
                    ->label('Status Minimum')
                    ->state(fn ($record): bool => ! $record->isLow())
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('danger'),
                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime(DateFormat::DATE_TIME)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('outlet')
                    ->label('Outlet')
                    ->schema([
                        Select::make('outlet_id')
                            ->label('Outlet')
                            ->relationship('outlet', 'name')
                            ->native(false)
                            ->searchable()
                            ->preload(),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        $data['outlet_id'] ?? null,
                        fn (Builder $query, string $outletId): Builder => $query->where('stocks.outlet_id', $outletId)
                    )),
                Filter::make('low_stock')
                    ->label('Stok minimum')
                    ->query(fn (Builder $query): Builder => $query->whereColumn('stocks.quantity', '<', 'ingredients.minimum_stock')),
            ])
            ->modifyQueryUsing(fn (Builder $query): Builder => $query
                ->join('ingredients', 'stocks.ingredient_id', '=', 'ingredients.id')
                ->select('stocks.*'));
    }

    protected function stockMovementTable(Table $table): Table
    {
        return $table
            ->query($this->getStockMovementQuery())
            ->columns([
                TextColumn::make('outlet.name')
                    ->label('Outlet')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('ingredient.name')
                    ->label('Bahan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Jenis Pergerakan')
                    ->formatStateUsing(fn (string $state): string => self::TYPE_LABELS[$state] ?? ucfirst(str_replace('_', ' ', $state)))
                    ->badge()
                    ->color(fn (string $state): string => self::TYPE_COLORS[$state] ?? 'gray')
                    ->searchable(),
                TextColumn::make('qty_in')
                    ->label('Masuk')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('qty_out')
                    ->label('Keluar')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime(DateFormat::DATE_TIME)
                    ->sortable(),
                TextColumn::make('reference')
                    ->label('Referensi')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('outlet_id')
                    ->label('Outlet')
                    ->relationship('outlet', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('type')
                    ->label('Jenis Pergerakan')
                    ->options(self::TYPE_LABELS),
            ])
            ->defaultSort('created_at', 'desc');
    }

    protected function getStockQuery(): Builder
    {
        $query = Stock::query();
        $user = auth()->user();

        if ($user && UserRole::parse($user->role)?->isOutletScoped()) {
            $query->where('stocks.outlet_id', $user->outlet_id);
        }

        return $query;
    }

    protected function getStockMovementQuery(): Builder
    {
        $query = StockMovement::query();
        $user = auth()->user();

        if ($user && UserRole::parse($user->role)?->isOutletScoped()) {
            $query->where('outlet_id', $user->outlet_id);
        }

        return $query;
    }

    public function getStockCount(): int
    {
        return $this->getStockQuery()->count();
    }

    public function getMovementCount(): int
    {
        return $this->getStockMovementQuery()->count();
    }

    public function getLowWarehouseStockCount(): int
    {
        return $this->getLowWarehouseStocksQuery()->count();
    }

    public function getLowWarehouseStockSummary(): string
    {
        return $this->getLowWarehouseStocksQuery()
            ->limit(5)
            ->get()
            ->map(fn (Stock $stock): string => "{$stock->outlet->name} - {$stock->ingredient->name}: {$stock->quantity} {$stock->ingredient->unit} (min {$stock->ingredient->minimum_stock})")
            ->implode(', ');
    }

    private function syncLowWarehouseStockNotification(): void
    {
        $count = $this->getLowWarehouseStockCount();

        if ($count === 0) {
            return;
        }

        $title = 'Stok gudang menipis';
        $summary = $this->getLowWarehouseStockSummary();
        $more = $count > 5 ? " dan " . ($count - 5) . ' bahan lain' : '';

        $users = User::query()
            ->whereIn('role', [
                UserRole::Owner->value,
                UserRole::AdministratorSistem->value,
                UserRole::StaffGudang->value,
            ])
            ->whereDoesntHave('notifications', fn (Builder $query): Builder => $query
                ->whereNull('read_at')
                ->where('data->title', $title))
            ->get();

        if ($users->isEmpty()) {
            return;
        }

        Notification::make()
            ->title($title)
            ->body("{$summary}{$more}. Klik untuk lihat Monitoring Stok.")
            ->danger()
            ->actions([
                Action::make('Lihat Monitoring Stok')
                    ->url(static::getUrl(['activeTab' => 'current']))
                    ->markAsRead(),
            ])
            ->sendToDatabase($users, isEventDispatched: true);
    }

    private function getLowWarehouseStocksQuery(): Builder
    {
        return Stock::query()
            ->with(['ingredient', 'outlet'])
            ->join('ingredients', 'stocks.ingredient_id', '=', 'ingredients.id')
            ->join('outlets', 'stocks.outlet_id', '=', 'outlets.id')
            ->where('outlets.type', 'gudang')
            ->whereColumn('stocks.quantity', '<', 'ingredients.minimum_stock')
            ->select('stocks.*')
            ->orderBy('ingredients.name');
    }
}

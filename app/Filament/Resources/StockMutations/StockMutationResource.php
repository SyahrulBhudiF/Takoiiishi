<?php

namespace App\Filament\Resources\StockMutations;

use App\Enums\UserRole;
use App\Filament\Resources\StockMutations\Pages\CreateStockMutation;
use App\Filament\Resources\StockMutations\Pages\EditStockMutation;
use App\Filament\Resources\StockMutations\Pages\ListStockMutations;
use App\Filament\Resources\StockMutations\Pages\ViewStockMutation;
use App\Filament\Resources\StockMutations\Schemas\StockMutationForm;
use App\Filament\Resources\StockMutations\Schemas\StockMutationInfolist;
use App\Filament\Resources\StockMutations\Tables\StockMutationsTable;
use App\Models\StockMutation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StockMutationResource extends Resource
{
    protected static ?string $model = StockMutation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowsRightLeft;

    protected static ?string $navigationLabel = 'Mutasi Stok';

    protected static ?string $modelLabel = 'Mutasi Stok';

    protected static ?string $pluralModelLabel = 'Mutasi Stok';

    public static function form(Schema $schema): Schema
    {
        return StockMutationForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return StockMutationInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StockMutationsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if ($user && UserRole::parse($user->role)?->isOutletScoped()) {
            $query->where(function (Builder $query) use ($user): void {
                $query->where('from_outlet_id', $user->outlet_id)
                    ->orWhere('to_outlet_id', $user->outlet_id);
            });
        }

        return $query;
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStockMutations::route('/'),
            'create' => CreateStockMutation::route('/create'),
            'view' => ViewStockMutation::route('/{record}'),
            'edit' => EditStockMutation::route('/{record}/edit'),
        ];
    }
}

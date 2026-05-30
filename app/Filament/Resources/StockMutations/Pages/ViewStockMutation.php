<?php

namespace App\Filament\Resources\StockMutations\Pages;

use App\Filament\Resources\StockMutations\StockMutationResource;
use App\Services\StockMutationService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Pages\ViewRecord;

class ViewStockMutation extends ViewRecord
{
    protected static string $resource = StockMutationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->visible(fn (): bool => auth()->user()?->can('Update:StockMutation') && app(StockMutationService::class)->canModify($this->record)),
            Action::make('cancel')
                ->label('Batalkan')
                ->color('danger')
                ->requiresConfirmation()
                ->schema([
                    Textarea::make('cancel_reason')
                        ->label('Alasan pembatalan')
                        ->required(),
                ])
                ->visible(fn (): bool => auth()->user()?->can('Delete:StockMutation') && app(StockMutationService::class)->canModify($this->record))
                ->action(function (array $data): void {
                    app(StockMutationService::class)->cancel($this->record, $data['cancel_reason'] ?? null);
                    $this->redirect($this->getResource()::getUrl('view', ['record' => $this->record]));
                }),
        ];
    }
}

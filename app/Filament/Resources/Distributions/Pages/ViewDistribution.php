<?php

namespace App\Filament\Resources\Distributions\Pages;

use App\Filament\Resources\Distributions\DistributionResource;
use App\Services\DistributionStockService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Pages\ViewRecord;

class ViewDistribution extends ViewRecord
{
    protected static string $resource = DistributionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->visible(fn (): bool => auth()->user()?->can('Update:Distribution') && app(DistributionStockService::class)->canModify($this->record)),
            Action::make('cancel')
                ->label('Batalkan')
                ->color('danger')
                ->requiresConfirmation()
                ->schema([
                    Textarea::make('cancel_reason')
                        ->label('Alasan pembatalan')
                        ->required(),
                ])
                ->visible(fn (): bool => auth()->user()?->can('Delete:Distribution') && app(DistributionStockService::class)->canModify($this->record))
                ->action(function (array $data): void {
                    app(DistributionStockService::class)->cancel($this->record, $data['cancel_reason'] ?? null);
                    $this->redirect($this->getResource()::getUrl('view', ['record' => $this->record]));
                }),
        ];
    }
}

<?php

namespace App\Filament\Resources\Ingredients\Pages;

use App\Filament\Resources\Ingredients\IngredientResource;
use Filament\Resources\Pages\CreateRecord;

class CreateIngredient extends CreateRecord
{
    protected static string $resource = IngredientResource::class;

    protected static bool $canCreateAnother = false;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

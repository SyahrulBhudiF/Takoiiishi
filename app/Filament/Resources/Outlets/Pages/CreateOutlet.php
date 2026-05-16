<?php

namespace App\Filament\Resources\Outlets\Pages;

use App\Filament\Resources\Outlets\OutletResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOutlet extends CreateRecord
{
    protected static string $resource = OutletResource::class;

    protected static bool $canCreateAnother = false;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

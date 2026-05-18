<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\UserRole;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama')
                    ->required(),
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required(),
                TextInput::make('username')
                    ->label('Username')
                    ->required(),
                TextInput::make('password')
                    ->password()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (?string $state): bool => filled($state)),
                Select::make('role')
                    ->label('Role')
                    ->options(UserRole::options())
                    ->live()
                    ->required(),
                Select::make('outlet_id')
                    ->label('Outlet')
                    ->relationship('outlet', 'name')
                    ->visible(fn (callable $get): bool => UserRole::tryFrom($get('role'))?->requiresOutlet() ?? false)
                    ->required(fn (callable $get): bool => UserRole::tryFrom($get('role'))?->requiresOutlet() ?? false),
            ]);
    }
}

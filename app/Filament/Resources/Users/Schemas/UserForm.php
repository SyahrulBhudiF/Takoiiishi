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
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                TextInput::make('username')
                    ->required(),
                DateTimePicker::make('email_verified_at'),
                TextInput::make('password')
                    ->password()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (?string $state): bool => filled($state)),
                Select::make('role')
                    ->options(UserRole::options())
                    ->live()
                    ->required(),
                Select::make('outlet_id')
                    ->relationship('outlet', 'name')
                    ->visible(fn (callable $get): bool => UserRole::tryFrom($get('role'))?->isBranchScoped() ?? false)
                    ->required(fn (callable $get): bool => UserRole::tryFrom($get('role'))?->isBranchScoped() ?? false),
            ]);
    }
}

<?php

namespace App\Enums;

enum UserRole: string
{
    case AdminPusat = 'admin_pusat';
    case AdminCabang = 'admin_cabang';
    case PemilikPusat = 'pemilik_pusat';
    case PemilikCabang = 'pemilik_cabang';

    public function label(): string
    {
        return match ($this) {
            self::AdminPusat => 'Admin Pusat',
            self::AdminCabang => 'Admin Cabang',
            self::PemilikPusat => 'Pemilik Pusat',
            self::PemilikCabang => 'Pemilik Cabang',
        };
    }

    public function isBranchScoped(): bool
    {
        return in_array($this, [self::AdminCabang, self::PemilikCabang], true);
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $role): array => [$role->value => $role->label()])
            ->all();
    }
}

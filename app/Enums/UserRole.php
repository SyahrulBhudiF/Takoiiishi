<?php

namespace App\Enums;

enum UserRole: string
{
    case Owner = 'owner';
    case AdministratorSistem = 'administrator_sistem';
    case StaffGudang = 'staff_gudang';
    case KaryawanOutlet = 'karyawan_outlet';

    public function label(): string
    {
        return match ($this) {
            self::Owner => 'Owner',
            self::AdministratorSistem => 'Administrator Sistem',
            self::StaffGudang => 'Staff Gudang',
            self::KaryawanOutlet => 'Karyawan Outlet',
        };
    }

    public function requiresOutlet(): bool
    {
        return $this === self::KaryawanOutlet;
    }

    public function isOutletScoped(): bool
    {
        return $this === self::KaryawanOutlet;
    }

    public function canFilterOutlet(): bool
    {
        return in_array($this, [self::Owner, self::StaffGudang], true);
    }

    public static function parse(self|string|null $role): ?self
    {
        return $role instanceof self ? $role : self::tryFrom((string) $role);
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $role): array => [$role->value => $role->label()])
            ->all();
    }
}

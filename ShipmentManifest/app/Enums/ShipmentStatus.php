<?php

namespace App\Enums;

enum ShipmentStatus: string
{
    case Draf = 'DRAF';
    case DalamPengiriman = 'DALAM_PENGIRIMAN';
    case Tiba = 'TIBA';
    case Dibatalkan = 'DIBATALKAN';

    public function label(): string
    {
        return match ($this) {
            self::Draf => 'Draf',
            self::DalamPengiriman => 'Dalam Pengiriman',
            self::Tiba => 'Tiba',
            self::Dibatalkan => 'Dibatalkan',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Draf => 'bg-zinc-100 text-zinc-700 ring-zinc-200',
            self::DalamPengiriman => 'bg-sky-50 text-sky-700 ring-sky-200',
            self::Tiba => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
            self::Dibatalkan => 'bg-red-50 text-red-700 ring-red-200',
        };
    }

    public static function active(): array
    {
        return [self::Draf, self::DalamPengiriman, self::Tiba];
    }
}

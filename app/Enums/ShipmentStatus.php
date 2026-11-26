<?php

namespace App\Enums;

use App\Support\StatusMap;

/**
 * Status manifes pengiriman, sesuai ENUM DDL `shipments_manifests.status` (PBI-11 / SYM-15).
 * "Dalam Perjalanan" khusus manifes; "Dalam Pengiriman" tetap label status order (03_STATUS_MAP).
 */
enum ShipmentStatus: string
{
    case DRAF = 'DRAF';
    case DALAM_PERJALANAN = 'DALAM_PERJALANAN';
    case TIBA = 'TIBA';
    case DIBATALKAN = 'DIBATALKAN';

    public function label(): string
    {
        return StatusMap::label('shipments_manifests.status', $this->value);
    }

    public function badge(): string
    {
        return StatusMap::badge('shipments_manifests.status', $this->value);
    }

    /**
     * Status yang menandai order masih punya pengiriman berjalan (satu order, satu pengiriman aktif).
     *
     * @return list<string>
     */
    public static function activeValues(): array
    {
        return [self::DRAF->value, self::DALAM_PERJALANAN->value, self::TIBA->value];
    }
}

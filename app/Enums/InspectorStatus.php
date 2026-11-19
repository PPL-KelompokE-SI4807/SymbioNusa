<?php

namespace App\Enums;

use App\Support\StatusMap;
use Carbon\CarbonInterface;

/**
 * Status turunan Inspector (PBI-01 / SYM-5). Bukan ENUM DDL: dihitung dari
 * inspector_profiles.status_aktif dan berlaku_sampai (03_STATUS_MAP.md › inspector_profiles).
 */
enum InspectorStatus: string
{
    case AKTIF = 'AKTIF';
    case SEGERA_BERAKHIR = 'SEGERA_BERAKHIR';
    case NONAKTIF = 'NONAKTIF';

    /** Sertifikat dianggap "segera berakhir" bila sisa masa berlaku ≤ sekian hari. */
    public const EXPIRING_WITHIN_DAYS = 30;

    public static function resolve(bool $statusAktif, ?CarbonInterface $berlakuSampai, CarbonInterface $today): self
    {
        $today = $today->copy()->startOfDay();

        if (! $statusAktif || $berlakuSampai === null || $berlakuSampai->copy()->startOfDay()->lt($today)) {
            return self::NONAKTIF;
        }

        if ($berlakuSampai->copy()->startOfDay()->lte($today->copy()->addDays(self::EXPIRING_WITHIN_DAYS))) {
            return self::SEGERA_BERAKHIR;
        }

        return self::AKTIF;
    }

    public function label(): string
    {
        return StatusMap::label('inspector_profiles.status', $this->value);
    }

    public function badge(): string
    {
        return StatusMap::badge('inspector_profiles.status', $this->value);
    }
}

<?php

namespace App\Support;

/**
 * Pilihan wilayah tugas Inspector (PBI-01 / SYM-5): satu provinsi per Inspector,
 * disimpan apa adanya di inspector_profiles.wilayah_tugas (VARCHAR 100).
 * Dipakai juga untuk menyaring penugasan mode Lapangan (PBI-18).
 */
final class Wilayah
{
    /** @var list<string> 38 provinsi Indonesia. */
    public const PROVINSI = [
        'Aceh',
        'Sumatera Utara',
        'Sumatera Barat',
        'Riau',
        'Kepulauan Riau',
        'Jambi',
        'Bengkulu',
        'Sumatera Selatan',
        'Kepulauan Bangka Belitung',
        'Lampung',
        'Banten',
        'DKI Jakarta',
        'Jawa Barat',
        'Jawa Tengah',
        'DI Yogyakarta',
        'Jawa Timur',
        'Bali',
        'Nusa Tenggara Barat',
        'Nusa Tenggara Timur',
        'Kalimantan Barat',
        'Kalimantan Tengah',
        'Kalimantan Selatan',
        'Kalimantan Timur',
        'Kalimantan Utara',
        'Sulawesi Utara',
        'Gorontalo',
        'Sulawesi Tengah',
        'Sulawesi Barat',
        'Sulawesi Selatan',
        'Sulawesi Tenggara',
        'Maluku',
        'Maluku Utara',
        'Papua',
        'Papua Barat',
        'Papua Barat Daya',
        'Papua Tengah',
        'Papua Pegunungan',
        'Papua Selatan',
    ];

    /**
     * @return list<string>
     */
    public static function provinsi(): array
    {
        return self::PROVINSI;
    }
}

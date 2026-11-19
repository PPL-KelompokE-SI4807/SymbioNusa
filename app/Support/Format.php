<?php

namespace App\Support;

/**
 * Format angka sesuai DESIGN.md: Rp 3.000.000, 5.000 kg, 128,40 ton (koma desimal, titik ribuan).
 */
final class Format
{
    public static function number(float|int|string|null $value, int $decimals = 0): string
    {
        return number_format((float) $value, $decimals, ',', '.');
    }

    public static function rupiah(float|int|string|null $value): string
    {
        return 'Rp '.self::number($value);
    }

    /**
     * Bentuk ringkas untuk kartu KPI: "Rp 214,6 jt", "Rp 1,2 M". Nilai lengkap tetap pakai rupiah().
     */
    public static function rupiahShort(float|int|string|null $value): string
    {
        $value = (float) $value;

        return match (true) {
            abs($value) >= 1_000_000_000 => 'Rp '.self::number($value / 1_000_000_000, 1).' M',
            abs($value) >= 1_000_000 => 'Rp '.self::number($value / 1_000_000, 1).' jt',
            abs($value) >= 1_000 => 'Rp '.self::number($value / 1_000, 1).' rb',
            default => self::rupiah($value),
        };
    }

    public static function kg(float|int|string|null $value): string
    {
        return self::number($value).' kg';
    }

    public static function ton(float|int|string|null $value, int $decimals = 2): string
    {
        return self::number($value, $decimals).' ton';
    }

    /**
     * Tanggal Indonesia ringkas, mis. "27 Okt 2026".
     */
    public static function date(\DateTimeInterface|string|null $value): string
    {
        if ($value === null || $value === '') {
            return '–';
        }

        $date = $value instanceof \DateTimeInterface ? $value : new \DateTimeImmutable($value);
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

        return $date->format('j').' '.$months[(int) $date->format('n') - 1].' '.$date->format('Y');
    }

    /**
     * Tanggal dan waktu Indonesia ringkas, mis. "27 Okt 2026 14:30 WIB".
     */
    public static function datetime(\DateTimeInterface|string|null $value): string
    {
        if ($value === null || $value === '') {
            return '–';
        }

        $date = $value instanceof \DateTimeInterface ? $value : new \DateTimeImmutable($value);

        return self::date($date).' '.$date->format('H:i').' WIB';
    }
}

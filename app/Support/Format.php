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
        [$amount, $unit] = self::rupiahParts($value);

        return trim($amount.' '.$unit);
    }

    /**
     * Bentuk ringkas yang dipecah untuk tile KPI: ["Rp 214,6", "jt"]. Desimal ",0" dibuang ("Rp 24 jt").
     *
     * @return array{0: string, 1: string}
     */
    public static function rupiahParts(float|int|string|null $value): array
    {
        $value = (float) $value;
        [$scaled, $unit] = match (true) {
            abs($value) >= 1_000_000_000 => [$value / 1_000_000_000, 'M'],
            abs($value) >= 1_000_000 => [$value / 1_000_000, 'jt'],
            abs($value) >= 1_000 => [$value / 1_000, 'rb'],
            default => [$value, ''],
        };

        if ($unit === '') {
            return [self::rupiah($value), ''];
        }

        $text = self::number($scaled, 1);

        return ['Rp '.(str_ends_with($text, ',0') ? substr($text, 0, -2) : $text), $unit];
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
     * Tanggal tanpa tahun untuk daftar, mis. "20 Nov".
     */
    public static function dayMonth(\DateTimeInterface|string|null $value): string
    {
        $full = self::date($value);

        return $full === '–' ? $full : implode(' ', array_slice(explode(' ', $full), 0, 2));
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
}

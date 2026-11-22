<?php

namespace App\Support;

/**
 * Foto material per kategori (DESIGN.md › Components › Material photos). Dipilih lewat kata kunci nama kategori;
 * kategori tanpa foto mengembalikan null dan tampil sebagai monogram. Sumber dan lisensi: docs/design/CREDITS-FOTO.md.
 */
final class MaterialPhoto
{
    /** @var array<string, list<string>> berkas di public/images/material => kata kunci */
    private const KEYWORDS = [
        'plastik.jpg' => ['plastik', 'pp', 'pe', 'pet', 'hdpe', 'polimer'],
        'kain.jpg' => ['kain', 'tekstil', 'perca', 'garmen'],
        'serbuk-kayu.jpg' => ['serbuk', 'kayu', 'gergaji'],
        'ampas-tahu.jpg' => ['ampas', 'tahu', 'okara', 'kedelai'],
        'kardus.jpg' => ['kertas', 'kardus', 'karton'],
    ];

    public static function forCategory(?string $name): ?string
    {
        $haystack = ' '.strtolower((string) $name).' ';

        foreach (self::KEYWORDS as $file => $words) {
            foreach ($words as $word) {
                if (preg_match('/\b'.preg_quote($word, '/').'\b/', $haystack)) {
                    return asset('images/material/'.$file);
                }
            }
        }

        return null;
    }
}

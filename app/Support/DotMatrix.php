<?php

namespace App\Support;

/**
 * Angka matriks titik untuk band dampak varian Kaca (dari SymbioNusa Lab, charts/Panels.jsx › DotNumber).
 * Mengembalikan atribut SVG statis; tidak ada animasi hitung naik (DESIGN.md › Motion).
 */
final class DotMatrix
{
    private const GLYPHS = [
        '0' => ['01110', '10001', '10011', '10101', '11001', '10001', '01110'],
        '1' => ['00100', '01100', '00100', '00100', '00100', '00100', '01110'],
        '2' => ['01110', '10001', '00001', '00010', '00100', '01000', '11111'],
        '3' => ['11110', '00001', '00001', '01110', '00001', '00001', '11110'],
        '4' => ['00010', '00110', '01010', '10010', '11111', '00010', '00010'],
        '5' => ['11111', '10000', '11110', '00001', '00001', '10001', '01110'],
        '6' => ['00110', '01000', '10000', '11110', '10001', '10001', '01110'],
        '7' => ['11111', '00001', '00010', '00100', '01000', '01000', '01000'],
        '8' => ['01110', '10001', '10001', '01110', '10001', '10001', '01110'],
        '9' => ['01110', '10001', '10001', '01111', '00001', '00010', '01100'],
        ',' => ['000', '000', '000', '000', '000', '010', '100'],
    ];

    /**
     * @return array{width: float, height: float, dots: list<array{x: float, y: float, on: bool}>}
     */
    public static function layout(string $text, float $dot = 6, float $gap = 2.2): array
    {
        $step = $dot + $gap;
        $x = 0.0;
        $dots = [];
        foreach (mb_str_split($text) as $ch) {
            $glyph = self::GLYPHS[$ch] ?? null;
            if (! $glyph) {
                continue;
            }
            foreach ($glyph as $ry => $row) {
                foreach (str_split($row) as $rx => $c) {
                    $dots[] = ['x' => $x + $rx * $step + $dot / 2, 'y' => $ry * $step + $dot / 2, 'on' => $c === '1'];
                }
            }
            $x += strlen($glyph[0]) * $step + $step;
        }

        return ['width' => max(1, $x - $step), 'height' => 7 * $step - $gap, 'dots' => $dots];
    }
}

<?php

namespace App\Support;

/**
 * Aturan toleransi tonase (proposal 3.1.1-C).
 *   Bmin = V x (1 - t), Bmax = V x (1 + t)
 *   W_kirim > Bmax  -> DITOLAK
 *   W_kirim < Bmin  -> BOLEH dengan peringatan + alasan Seller
 * Kelas murni PHP (tanpa Laravel) supaya mudah diuji.
 */
final class ShipmentTolerance
{
    public const OK = 'OK';
    public const UNDER = 'UNDER';
    public const OVER = 'OVER';

    public function __construct(
        public readonly float $nominalKg,
        public readonly float $tolerancePercent,
    ) {
    }

    public function min(): float
    {
        return round($this->nominalKg * (1 - $this->tolerancePercent / 100), 2);
    }

    public function max(): float
    {
        return round($this->nominalKg * (1 + $this->tolerancePercent / 100), 2);
    }

    public function evaluate(float $weightKg): string
    {
        $weightKg = round($weightKg, 2);

        if ($weightKg > $this->max()) {
            return self::OVER;
        }

        if ($weightKg < $this->min()) {
            return self::UNDER;
        }

        return self::OK;
    }
}

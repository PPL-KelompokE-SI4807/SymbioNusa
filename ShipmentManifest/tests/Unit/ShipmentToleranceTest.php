<?php

namespace Tests\Unit;

use App\Support\ShipmentTolerance;
use PHPUnit\Framework\TestCase;

class ShipmentToleranceTest extends TestCase
{
    public function test_contoh_proposal_ampas_tahu(): void
    {
        // V = 5.000 kg, t = 5% -> Bmin 4.750, Bmax 5.250 (proposal 3.1.1-C)
        $t = new ShipmentTolerance(5000, 5);

        $this->assertSame(4750.0, $t->min());
        $this->assertSame(5250.0, $t->max());
        $this->assertSame(ShipmentTolerance::OK, $t->evaluate(4820));
        $this->assertSame(ShipmentTolerance::OK, $t->evaluate(5250));
        $this->assertSame(ShipmentTolerance::OVER, $t->evaluate(5250.01));
        $this->assertSame(ShipmentTolerance::UNDER, $t->evaluate(4600));
    }
}

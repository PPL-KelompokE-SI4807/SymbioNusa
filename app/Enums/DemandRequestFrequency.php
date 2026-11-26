<?php

namespace App\Enums;

use App\Support\StatusMap;

enum DemandRequestFrequency: string
{
    case SEKALI = 'SEKALI';
    case MINGGUAN = 'MINGGUAN';
    case BULANAN = 'BULANAN';

    public function label(): string
    {
        return StatusMap::label('demand_requests.frekuensi', $this->value);
    }
}

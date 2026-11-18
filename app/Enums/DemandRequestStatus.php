<?php

namespace App\Enums;

use App\Support\StatusMap;

enum DemandRequestStatus: string
{
    case DRAF = 'DRAF';
    case TERBUKA = 'TERBUKA';
    case TERPENUHI = 'TERPENUHI';
    case KEDALUWARSA = 'KEDALUWARSA';

    public function label(): string
    {
        return StatusMap::label('demand_requests.status', $this->value);
    }

    public function badge(): string
    {
        return StatusMap::badge('demand_requests.status', $this->value);
    }
}

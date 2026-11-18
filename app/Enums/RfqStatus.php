<?php

namespace App\Enums;

use App\Support\StatusMap;

enum RfqStatus: string
{
    case TERBUKA = 'TERBUKA';
    case NEGOSIASI = 'NEGOSIASI';
    case DISEPAKATI = 'DISEPAKATI';
    case DITOLAK = 'DITOLAK';
    case KEDALUWARSA = 'KEDALUWARSA';
    case DITARIK = 'DITARIK';

    public function label(): string
    {
        return StatusMap::label('rfq_contracts.status', $this->value);
    }

    public function badge(): string
    {
        return StatusMap::badge('rfq_contracts.status', $this->value);
    }
}

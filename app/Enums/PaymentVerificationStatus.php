<?php

namespace App\Enums;

use App\Support\StatusMap;

enum PaymentVerificationStatus: string
{
    case MENUNGGU = 'MENUNGGU';
    case VALID = 'VALID';
    case DITOLAK = 'DITOLAK';

    public function label(): string
    {
        return StatusMap::label('payments.status_verifikasi', $this->value);
    }

    public function badge(): string
    {
        return StatusMap::badge('payments.status_verifikasi', $this->value);
    }
}

<?php

namespace App\Enums;

use App\Support\StatusMap;

enum OrderStatus: string
{
    case DRAFT = 'DRAFT';
    case MENUNGGU_KONFIRMASI = 'MENUNGGU_KONFIRMASI';
    case DIKONFIRMASI = 'DIKONFIRMASI';
    case INSPEKSI = 'INSPEKSI';
    case LOLOS_INSPEKSI = 'LOLOS_INSPEKSI';
    case GAGAL_INSPEKSI = 'GAGAL_INSPEKSI';
    case INVOICED = 'INVOICED';
    case DIBAYAR = 'DIBAYAR';
    case DALAM_PENGIRIMAN = 'DALAM_PENGIRIMAN';
    case DITERIMA = 'DITERIMA';
    case SELESAI = 'SELESAI';
    case SENGKETA = 'SENGKETA';
    case DIBATALKAN = 'DIBATALKAN';
    case DITOLAK = 'DITOLAK';

    public function label(): string
    {
        return StatusMap::label('purchase_orders.status', $this->value);
    }

    public function badge(): string
    {
        return StatusMap::badge('purchase_orders.status', $this->value);
    }
}

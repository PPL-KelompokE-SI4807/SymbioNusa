<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Draf = 'DRAF';
    case MenungguKonfirmasi = 'MENUNGGU_KONFIRMASI';
    case Dikonfirmasi = 'DIKONFIRMASI';
    case Inspeksi = 'INSPEKSI';
    case LolosInspeksi = 'LOLOS_INSPEKSI';
    case GagalInspeksi = 'GAGAL_INSPEKSI';
    case Dibayar = 'DIBAYAR';
    case DalamPengiriman = 'DALAM_PENGIRIMAN';
    case Selesai = 'SELESAI';
    case Sengketa = 'SENGKETA';
    case Ditolak = 'DITOLAK';
    case Dibatalkan = 'DIBATALKAN';

    public function label(): string
    {
        return match ($this) {
            self::Draf => 'Draf',
            self::MenungguKonfirmasi => 'Menunggu Konfirmasi',
            self::Dikonfirmasi => 'Dikonfirmasi',
            self::Inspeksi => 'Inspeksi',
            self::LolosInspeksi => 'Lolos Inspeksi',
            self::GagalInspeksi => 'Gagal Inspeksi',
            self::Dibayar => 'Dibayar',
            self::DalamPengiriman => 'Dalam Pengiriman',
            self::Selesai => 'Selesai',
            self::Sengketa => 'Sengketa',
            self::Ditolak => 'Ditolak',
            self::Dibatalkan => 'Dibatalkan',
        };
    }
}

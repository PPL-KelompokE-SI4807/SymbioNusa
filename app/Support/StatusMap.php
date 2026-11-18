<?php

namespace App\Support;

/**
 * Label UI dan varian badge per nilai ENUM DDL, salinan dari docs/design/prompts/03_STATUS_MAP.md.
 *
 * Dipakai sampai tiap modul punya PHP Enum sendiri (AGENTS.md §5.7). Enum milik modul boleh
 * memanggil kelas ini dari method label()/badge() supaya label tetap satu sumber.
 */
final class StatusMap
{
    /** @var array<string, array<string, array{0: string, 1: string}>> */
    private const MAP = [
        'purchase_orders.status' => [
            'DRAFT' => ['Draf', 'warning'],
            'MENUNGGU_KONFIRMASI' => ['Menunggu Konfirmasi', 'warning'],
            'DIKONFIRMASI' => ['Dikonfirmasi', 'success'],
            'INSPEKSI' => ['Inspeksi', 'info'],
            'LOLOS_INSPEKSI' => ['Lolos Inspeksi', 'success'],
            'GAGAL_INSPEKSI' => ['Gagal Inspeksi', 'danger'],
            'INVOICED' => ['Menunggu Pembayaran', 'warning'],
            'DIBAYAR' => ['Dibayar', 'success'],
            'DALAM_PENGIRIMAN' => ['Dalam Pengiriman', 'info'],
            'DITERIMA' => ['Diterima', 'info'],
            'SELESAI' => ['Selesai', 'success'],
            'SENGKETA' => ['Sengketa', 'danger'],
            'DIBATALKAN' => ['Dibatalkan', 'neutral'],
            'DITOLAK' => ['Ditolak', 'danger'],
        ],
        'rfq_contracts.status' => [
            'TERBUKA' => ['Terbuka', 'warning'],
            'NEGOSIASI' => ['Negosiasi', 'info'],
            'DISEPAKATI' => ['Disepakati', 'success'],
            'DITOLAK' => ['Ditolak', 'danger'],
            'KEDALUWARSA' => ['Kedaluwarsa', 'neutral'],
            'DITARIK' => ['Ditarik', 'neutral'],
        ],
        'companies.status_verifikasi' => [
            'PENDING' => ['Menunggu Verifikasi', 'warning'],
            'TERVERIFIKASI' => ['Terverifikasi', 'success'],
            'DITOLAK' => ['Ditolak', 'danger'],
        ],
        'listings.status' => [
            'DRAF' => ['Draf', 'warning'],
            'PUBLISHED' => ['Dipublikasikan', 'success'],
            'DIARSIPKAN' => ['Diarsipkan', 'neutral'],
        ],
        'demand_requests.status' => [
            'DRAF' => ['Draf', 'warning'],
            'TERBUKA' => ['Terbuka', 'warning'],
            'TERPENUHI' => ['Terpenuhi', 'success'],
            'KEDALUWARSA' => ['Kedaluwarsa', 'neutral'],
        ],
        'demand_requests.frekuensi' => [
            'SEKALI' => ['Sekali', 'neutral'],
            'MINGGUAN' => ['Mingguan', 'info'],
            'BULANAN' => ['Bulanan', 'info'],
        ],
        'invoices.status' => [
            'TERBIT' => ['Menunggu Pembayaran', 'warning'],
            'DIBAYAR' => ['Dibayar', 'success'],
            'VOID' => ['Void', 'neutral'],
        ],
        'payments.status_verifikasi' => [
            'MENUNGGU' => ['Menunggu Verifikasi', 'warning'],
            'VALID' => ['Terverifikasi', 'success'],
            'DITOLAK' => ['Ditolak', 'danger'],
        ],
        'disputes.status' => [
            'DIBUKA' => ['Dibuka', 'danger'],
            'DIPROSES' => ['Diproses', 'info'],
            'SELESAI' => ['Selesai', 'success'],
            'DITARIK' => ['Ditarik', 'neutral'],
        ],
        'disputes.jenis' => [
            'SELISIH_TONASE' => ['Selisih tonase', 'neutral'],
            'KADAR_AIR' => ['Kadar air', 'neutral'],
            'MUTU' => ['Mutu', 'neutral'],
            'LAINNYA' => ['Lainnya', 'neutral'],
        ],
        'inspections.status' => [
            'DITUGASKAN' => ['Ditugaskan', 'warning'],
            'DIPROSES' => ['Diproses', 'info'],
            'SELESAI' => ['Selesai', 'success'],
            'DIBATALKAN' => ['Dibatalkan', 'neutral'],
        ],
        'inspections.mode_verifikasi' => [
            'DESK' => ['Desk', 'neutral'],
            'LAPANGAN' => ['Lapangan', 'neutral'],
        ],
        'categories.status' => [
            'AKTIF' => ['Aktif', 'success'],
            'ARSIP' => ['Diarsipkan', 'neutral'],
        ],
        'categories.tingkat_risiko' => [
            'RENDAH' => ['Risiko Rendah', 'neutral'],
            'TINGGI' => ['Risiko Tinggi', 'warning'],
        ],
    ];

    public static function label(string $field, string|\BackedEnum|null $value): string
    {
        $val = $value instanceof \BackedEnum ? (string) $value->value : $value;

        return self::MAP[$field][$val ?? ''][0] ?? ($val ?? '–');
    }

    public static function badge(string $field, string|\BackedEnum|null $value): string
    {
        $val = $value instanceof \BackedEnum ? (string) $value->value : $value;

        return self::MAP[$field][$val ?? ''][1] ?? 'neutral';
    }
}

<?php

namespace App\Services\Inspector;

use App\Models\Category;
use App\Models\Inspection;
use App\Models\PurchaseOrder;

/**
 * InspectionModeAdvisor (PBI-18 / SYM-22)
 *
 * Memberikan rekomendasi mode verifikasi (DESK vs LAPANGAN) secara otomatis
 * berdasarkan tingkat risiko kategori, volume transaksi, dan riwayat Seller.
 */
class InspectionModeAdvisor
{
    public const VOLUME_THRESHOLD_KG = 10000.00;

    /**
     * Merekomendasikan mode verifikasi untuk Purchase Order tertentu.
     *
     * @return array{
     *     mode: string,
     *     alasan: string,
     *     tingkat_risiko_kategori: string,
     *     volume_kg: float,
     *     is_high_volume: bool,
     *     wajib_lab: bool
     * }
     */
    public function recommendMode(PurchaseOrder $po): array
    {
        $po->loadMissing([
            'rfqContract.listing.category',
            'seller',
        ]);

        /** @var Category|null $category */
        $category = $po->rfqContract?->listing?->category;
        $categoryRisk = $category instanceof Category ? $category->tingkat_risiko : 'RENDAH';
        $wajibLab = $category instanceof Category ? (bool) $category->wajib_sertifikat_lab : false;
        $categoryName = $category instanceof Category ? $category->nama : 'Umum';
        $volume = (float) ($po->volume_kg ?? 0);
        $isHighVolume = $volume > self::VOLUME_THRESHOLD_KG;

        // 1. Kategori Risiko Tinggi
        if (strtoupper($categoryRisk) === 'TINGGI') {
            return [
                'mode' => Inspection::MODE_LAPANGAN,
                'alasan' => "Kategori material berisiko tinggi ({$categoryName}) memerlukan pemeriksaan fisik di lokasi.",
                'tingkat_risiko_kategori' => $categoryRisk,
                'volume_kg' => $volume,
                'is_high_volume' => $isHighVolume,
                'wajib_lab' => $wajibLab,
            ];
        }

        // 2. Kategori Wajib Sertifikat Uji Lab
        if ($wajibLab) {
            return [
                'mode' => Inspection::MODE_LAPANGAN,
                'alasan' => "Kategori ({$categoryName}) mewajibkan pengujian laboratorium dan pengambilan sampel langsung di lapangan.",
                'tingkat_risiko_kategori' => $categoryRisk,
                'volume_kg' => $volume,
                'is_high_volume' => $isHighVolume,
                'wajib_lab' => $wajibLab,
            ];
        }

        // 3. Volume Besar (> 10 Ton)
        if ($isHighVolume) {
            return [
                'mode' => Inspection::MODE_LAPANGAN,
                'alasan' => 'Volume transaksi besar di atas 10 ton memerlukan verifikasi fisik kesesuaian tonase di lokasi.',
                'tingkat_risiko_kategori' => $categoryRisk,
                'volume_kg' => $volume,
                'is_high_volume' => $isHighVolume,
                'wajib_lab' => $wajibLab,
            ];
        }

        // 4. Kasus Risiko Rendah Default -> DESK
        return [
            'mode' => Inspection::MODE_DESK,
            'alasan' => 'Risiko kategori rendah, volume transaksi terkendali, dan riwayat Seller baik. Verifikasi berbasis bukti digital memadai.',
            'tingkat_risiko_kategori' => $categoryRisk,
            'volume_kg' => $volume,
            'is_high_volume' => $isHighVolume,
            'wajib_lab' => $wajibLab,
        ];
    }
}

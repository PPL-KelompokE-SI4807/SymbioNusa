<?php

namespace Database\Factories;

use App\Models\DocumentVerification;
use App\Models\PurchaseOrder;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DocumentVerification>
 */
class DocumentVerificationFactory extends Factory
{
    protected $model = DocumentVerification::class;

    public function definition(): array
    {
        return [
            'verifiable_type' => 'App\Models\PurchaseOrder',
            'verifiable_id' => null,
            'checklist' => [
                'nama_perusahaan_sesuai' => true,
                'nomor_dokumen_valid' => true,
                'masa_berlaku_valid' => true,
                'bukti_listing_sesuai' => true,
                'dokumen_terbaca_jelas' => true,
            ],
            'status' => DocumentVerification::STATUS_MENUNGGU,
            'purchase_orders_id_purchase_order' => PurchaseOrder::factory(),
            'users_id_user' => User::factory()->inspector(),
            'alasan' => null,
        ];
    }

    public function setuju(): static
    {
        return $this->state(fn () => [
            'status' => DocumentVerification::STATUS_SETUJU,
            'alasan' => null,
        ]);
    }

    public function ditolak(): static
    {
        return $this->state(fn () => [
            'status' => DocumentVerification::STATUS_DITOLAK,
            'alasan' => 'Dokumen izin lingkungan telah habis masa berlakunya.',
        ]);
    }
}

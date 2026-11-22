<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model untuk tabel document_verifications (PBI-17 / SYM-21).
 *
 * @property int $id_document_verification
 * @property string|null $verifiable_type
 * @property int|null $verifiable_id
 * @property array<string, bool>|null $checklist
 * @property string $status
 * @property int $purchase_orders_id_purchase_order
 * @property int $users_id_user
 * @property string|null $alasan
 * @property-read PurchaseOrder $purchaseOrder
 * @property-read User $inspector
 */
class DocumentVerification extends Model
{
    use HasFactory;

    public const STATUS_MENUNGGU = 'MENUNGGU';

    public const STATUS_SETUJU = 'SETUJU';

    public const STATUS_DITOLAK = 'DITOLAK';

    public const STATUS_DIBATALKAN = 'DIBATALKAN';

    /**
     * Butir checklist baku sesuai Screen 34 SCREENS.md
     *
     * @var array<string, string>
     */
    public const CHECKLIST_ITEMS = [
        'nama_perusahaan_sesuai' => 'Nama perusahaan sesuai dokumen',
        'nomor_dokumen_valid' => 'Nomor dokumen valid',
        'masa_berlaku_valid' => 'Masa berlaku belum habis',
        'bukti_listing_sesuai' => 'Bukti listing sesuai produk',
        'dokumen_terbaca_jelas' => 'Dokumen terbaca jelas',
    ];

    protected $table = 'document_verifications';

    protected $primaryKey = 'id_document_verification';

    public $timestamps = false;

    protected $fillable = [
        'id_document_verification',
        'verifiable_type',
        'verifiable_id',
        'checklist',
        'status',
        'purchase_orders_id_purchase_order',
        'users_id_user',
        'alasan',
    ];

    protected function casts(): array
    {
        return [
            'checklist' => 'array',
        ];
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_orders_id_purchase_order', 'id_purchase_order');
    }

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'users_id_user', 'id_user');
    }
}

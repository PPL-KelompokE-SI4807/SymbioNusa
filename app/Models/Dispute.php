<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Kolom sesuai DDL tabel `disputes`.
 *
 * @property int $id_dispute
 * @property string $jenis
 * @property string|null $deskripsi
 * @property string|null $bukti_url
 * @property string $status
 * @property string|null $putusan
 * @property int $purchase_orders_id_purchase_order
 * @property int $companies_id_company
 */
class Dispute extends Model
{
    use HasFactory;

    protected $table = 'disputes';

    protected $primaryKey = 'id_dispute';

    public $timestamps = false;

    protected $fillable = [
        'id_dispute',
        'jenis',
        'deskripsi',
        'bukti_url',
        'status',
        'putusan',
        'purchase_orders_id_purchase_order',
        'companies_id_company',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_orders_id_purchase_order', 'id_purchase_order');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'companies_id_company', 'id_company');
    }
}

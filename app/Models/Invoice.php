<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property int $id_invoice
 * @property string $nomor
 * @property string $total
 * @property Carbon|null $jatuh_tempo
 * @property string $status
 * @property int $purchase_orders_id_purchase_order
 * @property-read PurchaseOrder|null $purchaseOrder
 * @property-read Collection<int, Payment> $payments
 * @property-read Payment|null $latestPayment
 */
class Invoice extends Model
{
    use HasFactory;

    protected $table = 'invoices';

    protected $primaryKey = 'id_invoice';

    public $timestamps = false;

    protected $fillable = [
        'id_invoice',
        'nomor',
        'total',
        'jatuh_tempo',
        'status',
        'purchase_orders_id_purchase_order',
    ];

    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
            'jatuh_tempo' => 'date',
        ];
    }

    /**
     * @return BelongsTo<PurchaseOrder, $this>
     */
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_orders_id_purchase_order', 'id_purchase_order');
    }

    /**
     * @return HasMany<Payment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'invoices_id_invoice', 'id_invoice');
    }

    /**
     * @return HasOne<Payment, $this>
     */
    public function latestPayment(): HasOne
    {
        return $this->hasOne(Payment::class, 'invoices_id_invoice', 'id_invoice')->latestOfMany('id_payment');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_orders_id_purchase_order', 'id_purchase_order');
    }
}

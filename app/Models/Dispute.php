<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Dispute extends Model
{
    protected $table = 'disputes';

    protected $primaryKey = 'id_dispute';

    public $timestamps = false;

    protected $guarded = [];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_orders_id_purchase_order', 'id_purchase_order');
    }
}

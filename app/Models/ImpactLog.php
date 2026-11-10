<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImpactLog extends Model
{
    use HasFactory;

    protected $table = 'impact_logs';

    protected $primaryKey = 'id_impact_log';

    public $timestamps = false;

    protected $fillable = [
        'id_impact_log',
        'tonase_ton',
        'hemat_seller_rp',
        'hemat_buyer_rp',
        'co2e_dihindari_kg',
        'co2e_angkutan_kg',
        'co2e_net_kg',
        'versi_faktor',
        'purchase_orders_id_purchase_order',
    ];

    protected function casts(): array
    {
        return [
            'tonase_ton' => 'decimal:4',
            'hemat_seller_rp' => 'decimal:2',
            'hemat_buyer_rp' => 'decimal:2',
            'co2e_dihindari_kg' => 'decimal:4',
            'co2e_angkutan_kg' => 'decimal:4',
            'co2e_net_kg' => 'decimal:4',
        ];
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_orders_id_purchase_order', 'id_purchase_order');
    }
}

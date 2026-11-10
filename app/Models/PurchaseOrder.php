<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $table = 'purchase_orders';

    protected $primaryKey = 'id_purchase_order';

    public $timestamps = false;

    protected $fillable = [
        'id_purchase_order',
        'volume_kg',
        'harga_per_kg',
        'toleransi_persen',
        'kadar_air_maks_persen',
        'toleransi_kadar_air_poin',
        'jarak_km',
        'status',
        'rfq_contracts_id_rfq_contract',
        'companies_id_seller',
        'companies_id_buyer',
    ];

    protected function casts(): array
    {
        return [
            'volume_kg' => 'decimal:2',
            'harga_per_kg' => 'decimal:2',
            'toleransi_persen' => 'decimal:2',
            'kadar_air_maks_persen' => 'decimal:2',
            'toleransi_kadar_air_poin' => 'decimal:2',
            'jarak_km' => 'decimal:2',
        ];
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'companies_id_seller', 'id_company');
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'companies_id_buyer', 'id_company');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'purchase_orders_id_purchase_order', 'id_purchase_order');
    }

    public function inspections(): HasMany
    {
        return $this->hasMany(Inspection::class, 'purchase_orders_id_purchase_order', 'id_purchase_order');
    }

    public function disputes(): HasMany
    {
        return $this->hasMany(Dispute::class, 'purchase_orders_id_purchase_order', 'id_purchase_order');
    }

    public function impactLogs(): HasMany
    {
        return $this->hasMany(ImpactLog::class, 'purchase_orders_id_purchase_order', 'id_purchase_order');
    }
}

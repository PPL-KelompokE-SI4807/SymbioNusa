<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DemandRequest extends Model
{
    use HasFactory;

    protected $table = 'demand_requests';
    protected $primaryKey = 'id_demand_request';
    public $timestamps = false;

    protected $fillable = [
        'id_demand_request',
        'spesifikasi_min',
        'volume_kg',
        'harga_maks',
        'berlaku_sampai',
        'status',
        'companies_id_company',
        'categories_id_categories',
        'frekuensi',
    ];

    protected function casts(): array
    {
        return [
            'spesifikasi_min' => 'array',
            'volume_kg' => 'decimal:2',
            'harga_maks' => 'decimal:2',
            'berlaku_sampai' => 'date',
        ];
    }

    /**
     * Relationship: DemandRequest belongs to Company (buyer)
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'companies_id_company', 'id_company');
    }

    /**
     * Relationship: DemandRequest belongs to Category
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'categories_id_categories', 'id_categories');
    }

    /**
     * Relationship: DemandRequest has many RFQ Contracts
     */
    public function rfqContracts(): HasMany
    {
        return $this->hasMany(RfqContract::class, 'demand_requests_id_demand_request', 'id_demand_request');
    }

    /**
     * Check if demand request is open
     */
    public function isOpen(): bool
    {
        return $this->status === 'TERBUKA';
    }

    /**
     * Check if demand request is expired
     */
    public function isExpired(): bool
    {
        if (!$this->berlaku_sampai) {
            return false;
        }
        return now()->gt($this->berlaku_sampai);
    }
}
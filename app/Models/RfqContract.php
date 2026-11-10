<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RfqContract extends Model
{
    use HasFactory;

    protected $table = 'rfq_contracts';

    protected $primaryKey = 'id_rfq_contract';

    public $timestamps = false;

    protected $fillable = [
        'id_rfq_contract',
        'volume_kg_disepakati',
        'harga_disepakati',
        'tenggat',
        'status',
        'listings_id_listings',
        'companies_id_company',
        'demand_requests_id_demand_request',
    ];

    protected function casts(): array
    {
        return [
            'volume_kg_disepakati' => 'decimal:2',
            'harga_disepakati' => 'decimal:2',
            'tenggat' => 'datetime',
        ];
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class, 'listings_id_listings', 'id_listings');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'companies_id_company', 'id_company');
    }

    public function demandRequest(): BelongsTo
    {
        return $this->belongsTo(DemandRequest::class, 'demand_requests_id_demand_request', 'id_demand_request');
    }
}

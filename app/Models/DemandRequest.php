<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'companies_id_company', 'id_company');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'categories_id_categories', 'id_categories');
    }
}

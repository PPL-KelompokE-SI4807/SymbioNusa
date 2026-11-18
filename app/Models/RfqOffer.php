<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id_rfq_offer
 * @property float $volume_kg
 * @property float $harga_per_kg
 * @property string|null $catatan
 * @property int $urutan
 * @property int $rfq_contracts_id_rfq_contract
 * @property int $companies_id_company
 * @property-read RfqContract|null $rfqContract
 * @property-read Company|null $company
 */
class RfqOffer extends Model
{
    use HasFactory;

    protected $table = 'rfq_offers';

    protected $primaryKey = 'id_rfq_offer';

    public $timestamps = false;

    protected $fillable = [
        'id_rfq_offer',
        'volume_kg',
        'harga_per_kg',
        'catatan',
        'urutan',
        'rfq_contracts_id_rfq_contract',
        'companies_id_company',
    ];

    protected function casts(): array
    {
        return [
            'volume_kg' => 'decimal:2',
            'harga_per_kg' => 'decimal:2',
            'urutan' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<RfqContract, $this>
     */
    public function rfqContract(): BelongsTo
    {
        return $this->belongsTo(RfqContract::class, 'rfq_contracts_id_rfq_contract', 'id_rfq_contract');
    }

    /**
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'companies_id_company', 'id_company');
    }
}

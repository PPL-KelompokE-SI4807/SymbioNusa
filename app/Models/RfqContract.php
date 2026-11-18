<?php

namespace App\Models;

use App\Enums\RfqStatus;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id_rfq_contract
 * @property float|null $volume_kg_disepakati
 * @property float|null $harga_disepakati
 * @property CarbonInterface|null $tenggat
 * @property RfqStatus|string $status
 * @property int $listings_id_listings
 * @property int $companies_id_company
 * @property int|null $demand_requests_id_demand_request
 * @property-read Listing|null $listing
 * @property-read Company|null $company
 * @property-read DemandRequest|null $demandRequest
 * @property-read Collection<int, RfqOffer> $offers
 * @property-read RfqOffer|null $latestOffer
 */
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
            'status' => RfqStatus::class,
        ];
    }

    /**
     * @return BelongsTo<Listing, $this>
     */
    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class, 'listings_id_listings', 'id_listings');
    }

    /**
     * Perusahaan pembeli (Buyer) yang mengajukan RFQ.
     *
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'companies_id_company', 'id_company');
    }

    /**
     * @return BelongsTo<Company, $this>
     */
    public function buyer(): BelongsTo
    {
        return $this->company();
    }

    /**
     * @return BelongsTo<DemandRequest, $this>
     */
    public function demandRequest(): BelongsTo
    {
        return $this->belongsTo(DemandRequest::class, 'demand_requests_id_demand_request', 'id_demand_request');
    }

    /**
     * @return HasMany<RfqOffer, $this>
     */
    public function offers(): HasMany
    {
        return $this->hasMany(RfqOffer::class, 'rfq_contracts_id_rfq_contract', 'id_rfq_contract')
            ->orderBy('urutan', 'asc');
    }

    /**
     * @return HasOne<RfqOffer, $this>
     */
    public function latestOffer(): HasOne
    {
        return $this->hasOne(RfqOffer::class, 'rfq_contracts_id_rfq_contract', 'id_rfq_contract')
            ->latestOfMany('urutan');
    }

    public function isExpired(): bool
    {
        if ($this->status === RfqStatus::KEDALUWARSA) {
            return true;
        }

        return $this->tenggat !== null && $this->tenggat->isPast();
    }

    public function isOpenOrNegotiating(): bool
    {
        return in_array($this->status, [RfqStatus::TERBUKA, RfqStatus::NEGOSIASI], true);
    }

    public function canCounter(): bool
    {
        if (! $this->isOpenOrNegotiating() || $this->isExpired()) {
            return false;
        }

        $latestUrutan = $this->latestOffer ? $this->latestOffer->urutan : 0;

        return $latestUrutan < 5;
    }
}

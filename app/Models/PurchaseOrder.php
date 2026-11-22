<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Kolom sesuai DDL tabel `purchase_orders` (kolom DECIMAL di-cast "decimal:2", jadi berupa string).
 *
 * @property int $id_purchase_order
 * @property string $volume_kg
 * @property string $harga_per_kg
 * @property string $toleransi_persen
 * @property string|null $kadar_air_maks_persen
 * @property string|null $toleransi_kadar_air_poin
 * @property string|null $jarak_km
 * @property OrderStatus $status
 * @property int $rfq_contracts_id_rfq_contract
 * @property int $companies_id_seller
 * @property int $companies_id_buyer
 * @property-read RfqContract|null $rfqContract
 * @property-read Company|null $seller
 * @property-read Company|null $buyer
 * @property-read Collection<int, OrderStatusHistory> $statusHistory
 * @property-read Collection<int, Invoice> $invoices
 * @property-read Collection<int, Inspection> $inspections
 * @property-read Collection<int, DocumentVerification> $documentVerifications
 * @property-read Collection<int, Dispute> $disputes
 * @property-read Collection<int, ImpactLog> $impactLogs
 */
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
            'status' => OrderStatus::class,
            'companies_id_seller' => 'integer',
            'companies_id_buyer' => 'integer',
            'rfq_contracts_id_rfq_contract' => 'integer',
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
    public function seller(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'companies_id_seller', 'id_company');
    }

    /**
     * @return BelongsTo<Company, $this>
     */
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'companies_id_buyer', 'id_company');
    }

    /**
     * @return BelongsTo<RfqContract, $this>
     */
    public function rfqContract(): BelongsTo
    {
        return $this->belongsTo(RfqContract::class, 'rfq_contracts_id_rfq_contract', 'id_rfq_contract');
    }

    /**
     * @return HasMany<OrderStatusHistory, $this>
     */
    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class, 'purchase_orders_id_purchase_order', 'id_purchase_order')
            ->orderBy('id_order_status_history', 'asc');
    }
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'purchase_orders_id_purchase_order', 'id_purchase_order');
    }

    /**
     * @return HasMany<Inspection, $this>
     */
    public function inspections(): HasMany
    {
        return $this->hasMany(Inspection::class, 'purchase_orders_id_purchase_order', 'id_purchase_order');
    }

    /**
     * @return HasMany<DocumentVerification, $this>
     */
    public function documentVerifications(): HasMany
    {
        return $this->hasMany(DocumentVerification::class, 'purchase_orders_id_purchase_order', 'id_purchase_order');
    }

    /**
     * @return HasMany<Dispute, $this>
     */
    public function disputes(): HasMany
    {
        return $this->hasMany(Dispute::class, 'purchase_orders_id_purchase_order', 'id_purchase_order');
    }

    /**
     * @return HasMany<ImpactLog, $this>
     */
    public function impactLogs(): HasMany
    {
        return $this->hasMany(ImpactLog::class, 'purchase_orders_id_purchase_order', 'id_purchase_order');
    }

    public function minVolumeKg(): float
    {
        $vol = (float) $this->volume_kg;
        $tol = (float) $this->toleransi_persen;

        return round($vol * (1 - ($tol / 100)), 2);
    }

    public function maxVolumeKg(): float
    {
        $vol = (float) $this->volume_kg;
        $tol = (float) $this->toleransi_persen;

        return round($vol * (1 + ($tol / 100)), 2);
    }

    public function totalNominal(): float
    {
        return round((float) $this->volume_kg * (float) $this->harga_per_kg, 2);
    }

    public function canBeConfirmed(): bool
    {
        return $this->status === OrderStatus::MENUNGGU_KONFIRMASI;
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, [OrderStatus::DRAFT, OrderStatus::MENUNGGU_KONFIRMASI], true);
    }

    public function formattedNumber(): string
    {
        return 'PO-'.str_pad((string) $this->id_purchase_order, 4, '0', STR_PAD_LEFT);
    }
}

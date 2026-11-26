<?php

namespace App\Models;

use App\Enums\ShipmentStatus;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Manifes pengiriman (PBI-11 / SYM-15). Kolom sesuai DDL tabel `shipments_manifests`.
 *
 * @property int $id_shipment_manifest
 * @property string|null $armada
 * @property string|null $plat
 * @property string|null $sopir
 * @property string|null $manifes_url
 * @property ShipmentStatus $status
 * @property int $purchase_orders_id_purchase_order
 * @property string $berat_dikirim_kg
 * @property CarbonInterface|null $jadwal
 * @property string|null $rute
 * @property string|null $no_manifes
 * @property-read PurchaseOrder $purchaseOrder
 * @property-read Collection<int, ShipmentDocument> $documents
 */
class ShipmentManifest extends Model
{
    use HasFactory;

    protected $table = 'shipments_manifests';

    protected $primaryKey = 'id_shipment_manifest';

    public $timestamps = false;

    protected $fillable = [
        'armada',
        'plat',
        'sopir',
        'manifes_url',
        'status',
        'purchase_orders_id_purchase_order',
        'berat_dikirim_kg',
        'jadwal',
        'rute',
        'no_manifes',
    ];

    protected function casts(): array
    {
        return [
            'status' => ShipmentStatus::class,
            'berat_dikirim_kg' => 'decimal:2',
            'jadwal' => 'datetime',
            'purchase_orders_id_purchase_order' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<PurchaseOrder, $this>
     */
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_orders_id_purchase_order', 'id_purchase_order');
    }

    /**
     * @return HasMany<ShipmentDocument, $this>
     */
    public function documents(): HasMany
    {
        return $this->hasMany(ShipmentDocument::class, 'shipments_manifests_id_shipment_manifest', 'id_shipment_manifest')
            ->orderBy('id_shipment_document');
    }

    public function isDraft(): bool
    {
        return $this->status === ShipmentStatus::DRAF;
    }

    /** Nama tampilan: nomor manifes bila sudah terbit, selain itu nomor draf. */
    public function displayNumber(): string
    {
        return $this->no_manifes ?? 'Draf #'.$this->id_shipment_manifest;
    }

    /**
     * Isolasi data per peran: Seller melihat pengiriman perusahaannya, Buyer melihat yang sudah terbit
     * untuk perusahaannya, Admin dan Inspector melihat semua.
     *
     * @param  Builder<ShipmentManifest>  $query
     * @return Builder<ShipmentManifest>
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return match ($user->activeRole()) {
            'ADMIN', 'INSPECTOR' => $query,
            'SELLER' => $query->whereHas('purchaseOrder', fn (Builder $po) => $po->where('companies_id_seller', $user->id_company)),
            'BUYER' => $query->where('status', '!=', ShipmentStatus::DRAF->value)
                ->whereHas('purchaseOrder', fn (Builder $po) => $po->where('companies_id_buyer', $user->id_company)),
            default => $query->whereRaw('1 = 0'),
        };
    }
}

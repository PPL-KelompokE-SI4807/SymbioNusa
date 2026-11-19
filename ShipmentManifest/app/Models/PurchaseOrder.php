<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\ShipmentStatus;
use App\Support\ShipmentTolerance;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    protected $fillable = [
        'nomor_po', 'seller_company_id', 'buyer_company_id', 'material',
        'volume_kg', 'harga_per_kg', 'toleransi_tonase_persen', 'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'volume_kg' => 'decimal:2',
            'harga_per_kg' => 'decimal:2',
            'toleransi_tonase_persen' => 'decimal:2',
        ];
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'seller_company_id');
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'buyer_company_id');
    }

    public function documentVerifications(): HasMany
    {
        return $this->hasMany(DocumentVerification::class);
    }

    public function shipments(): HasMany
    {
        return $this->hasMany(ShipmentManifest::class);
    }

    public function getMaterialNameAttribute(): string
    {
        return (string) $this->material;
    }

    public function tolerance(): ShipmentTolerance
    {
        return new ShipmentTolerance((float) $this->volume_kg, (float) $this->toleransi_tonase_persen);
    }

    /** Syarat pengiriman (PBI-11): sudah dibayar + verifikasi dokumen non-B3 disetujui + belum ada pengiriman aktif. */
    public function scopeReadyToShip(Builder $query): Builder
    {
        return $query
            ->where('status', OrderStatus::Dibayar->value)
            ->whereHas('documentVerifications', fn ($v) => $v->where('status', DocumentVerification::DISETUJUI))
            ->whereDoesntHave('shipments', fn ($s) => $s->whereIn(
                'status',
                array_map(fn (ShipmentStatus $x) => $x->value, ShipmentStatus::active())
            ));
    }
}

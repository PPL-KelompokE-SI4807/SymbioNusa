<?php

namespace App\Models;

use App\Enums\ShipmentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShipmentManifest extends Model
{
    protected $table = 'shipments_manifests';

    protected $fillable = [
        'purchase_order_id', 'no_manifes', 'armada', 'plat', 'sopir', 'kontak_sopir',
        'jadwal_berangkat', 'estimasi_tiba', 'rute_asal', 'rute_tujuan', 'jarak_km',
        'berat_dikirim_kg', 'alasan_di_bawah_batas', 'catatan', 'status',
        'berangkat_at', 'tiba_at', 'dibatalkan_at', 'alasan_batal', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => ShipmentStatus::class,
            'jadwal_berangkat' => 'datetime',
            'estimasi_tiba' => 'datetime',
            'berangkat_at' => 'datetime',
            'tiba_at' => 'datetime',
            'dibatalkan_at' => 'datetime',
            'berat_dikirim_kg' => 'decimal:2',
            'jarak_km' => 'decimal:2',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ShipmentDocument::class, 'shipment_manifest_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isDraft(): bool
    {
        return $this->status === ShipmentStatus::Draf;
    }

    /** Pembatasan data per peran (isolasi company_id, NFR-04). */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return match ($user->role) {
            User::ROLE_ADMIN, User::ROLE_INSPECTOR => $query,
            User::ROLE_SELLER => $query->whereHas(
                'order', fn ($o) => $o->where('seller_company_id', $user->company_id)
            ),
            User::ROLE_BUYER => $query->where('status', '!=', ShipmentStatus::Draf->value)->whereHas(
                'order', fn ($o) => $o->where('buyer_company_id', $user->company_id)
            ),
            default => $query->whereRaw('1 = 0'),
        };
    }
}

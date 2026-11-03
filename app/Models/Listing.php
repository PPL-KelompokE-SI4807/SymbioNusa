<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Listing extends Model
{
    use HasFactory;

    protected $table = 'listings';
    protected $primaryKey = 'id_listings';
    public $timestamps = false;

    protected $fillable = [
        'id_listings',
        'judul',
        'spesifikasi',
        'volume_kg',
        'stok_kg',
        'harga_per_kg',
        'biaya_buang_per_kg',
        'metode_buang_sebelumnya',
        'lokasi',
        'status',
        'companies_id_company',
        'categories_id_categories',
    ];

    protected function casts(): array
    {
        return [
            'spesifikasi' => 'array',
            'volume_kg' => 'decimal:2',
            'stok_kg' => 'decimal:2',
            'harga_per_kg' => 'decimal:2',
            'biaya_buang_per_kg' => 'decimal:2',
        ];
    }

    /**
     * Relationship: Listing belongs to Company (seller)
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'companies_id_company', 'id_company');
    }

    /**
     * Relationship: Listing belongs to Category
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'categories_id_categories', 'id_categories');
    }

    /**
     * Relationship: Listing has many RFQ Contracts
     */
    public function rfqContracts(): HasMany
    {
        return $this->hasMany(RfqContract::class, 'listings_id_listings', 'id_listings');
    }

    /**
     * Relationship: Listing has many Supply Schedules
     */
    public function supplySchedules(): HasMany
    {
        return $this->hasMany(SupplySchedule::class, 'listings_id_listings', 'id_listings');
    }

    /**
     * Relationship: Listing has many Attachments
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(ListingAttachment::class, 'listings_id_listings', 'id_listings');
    }

    /**
     * Check if listing is published
     */
    public function isPublished(): bool
    {
        return $this->status === 'PUBLISHED';
    }

    /**
     * Check if listing has available stock
     */
    public function hasStock(float $requiredKg = 0): bool
    {
        return $this->stok_kg >= $requiredKg;
    }

    /**
     * Check if category prohibits B3
     */
    public function isB3Prohibited(): bool
    {
        return $this->category?->isB3Prohibited() ?? false;
    }

    /**
     * Reserve stock (lock for update)
     */
    public function reserveStock(float $kg): bool
    {
        if (!$this->hasStock($kg)) {
            return false;
        }

        $this->stok_kg -= $kg;
        $this->save();

        return true;
    }

    /**
     * Release reserved stock
     */
    public function releaseStock(float $kg): void
    {
        $this->stok_kg += $kg;
        $this->save();
    }
}
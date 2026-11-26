<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id_listings
 * @property string $judul
 * @property array<string, mixed>|null $spesifikasi
 * @property float $volume_kg
 * @property float $stok_kg
 * @property float $harga_per_kg
 * @property float|null $biaya_buang_per_kg
 * @property string|null $metode_buang_sebelumnya
 * @property string|null $lokasi
 * @property string $status
 * @property int $companies_id_company
 * @property int $categories_id_categories
 * @property-read Company|null $company
 * @property-read Category|null $category
 * @property-read Collection<int, ListingAttachment> $attachments
 */
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
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'companies_id_company', 'id_company');
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'categories_id_categories', 'id_categories');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(ListingAttachment::class, 'listings_id_listings', 'id_listings');
    }

    /**
     * Lampiran yang berlaku (lampiran lama yang sudah diganti berstatus NONAKTIF).
     */
    public function activeAttachments(): HasMany
    {
        return $this->attachments()->where('status', 'AKTIF');
    }

    public function rfqContracts(): HasMany
    {
        return $this->hasMany(RfqContract::class, 'listings_id_listings', 'id_listings');
    }

    /**
     * Listing sedang diinspeksi bila ada PO-nya yang berstatus INSPEKSI;
     * selama itu lampiran bukti tidak boleh diganti (PBI-09).
     */
    public function isUnderInspection(): bool
    {
        return PurchaseOrder::query()
            ->where('status', 'INSPEKSI')
            ->whereHas('rfqContract', fn ($q) => $q->where('listings_id_listings', $this->id_listings))
            ->exists();
    }

    public function isPublished(): bool
    {
        return $this->status === 'PUBLISHED';
    }

    public function hasStock(float $requiredKg = 0): bool
    {
        return $this->stok_kg >= $requiredKg;
    }

    /**
     * @param  Builder<Listing>  $query
     * @return Builder<Listing>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'PUBLISHED');
    }

    /**
     * @param  Builder<Listing>  $query
     * @return Builder<Listing>
     */
    public function scopeForCompany(Builder $query, int $companyId): Builder
    {
        return $query->where('companies_id_company', $companyId);
    }
}

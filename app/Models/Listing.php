<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id_listings
 * @property string $judul
 * @property array|null $spesifikasi
 * @property float $volume_kg
 * @property float $stok_kg
 * @property float $harga_per_kg
 * @property float|null $biaya_buang_per_kg
 * @property string|null $metode_buang_sebelumnya
 * @property string|null $lokasi
 * @property string $status
 * @property int $companies_id_company
 * @property int $categories_id_categories
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

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'companies_id_company', 'id_company');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'categories_id_categories', 'id_categories');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(ListingAttachment::class, 'listings_id_listings', 'id_listings');
    }

    public function isPublished(): bool
    {
        return $this->status === 'PUBLISHED';
    }

    public function hasStock(float $requiredKg = 0): bool
    {
        return $this->stok_kg >= $requiredKg;
    }
}

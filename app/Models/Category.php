<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $table = 'categories';
    protected $primaryKey = 'id_categories';
    public $timestamps = false;

    protected $fillable = [
        'id_categories',
        'nama',
        'spec_schema',
        'is_b3_prohibited',
        'toleransi_susut_persen',
        'kadar_air_maks_persen',
        'toleransi_kadar_air_poin',
        'tingkat_risiko',
        'wajib_sertifikat_lab',
        'categories_id_categories',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'spec_schema' => 'array',
            'is_b3_prohibited' => 'boolean',
            'toleransi_susut_persen' => 'decimal:2',
            'kadar_air_maks_persen' => 'decimal:2',
            'toleransi_kadar_air_poin' => 'decimal:2',
            'wajib_sertifikat_lab' => 'boolean',
        ];
    }

    /**
     * Relationship: Category belongs to parent Category
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'categories_id_categories', 'id_categories');
    }

    /**
     * Relationship: Category has many child Categories
     */
    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'categories_id_categories', 'id_categories');
    }

    /**
     * Relationship: Category has many Listings
     */
    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class, 'categories_id_categories', 'id_categories');
    }

    /**
     * Relationship: Category has many Impact Factors
     */
    public function impactFactors(): HasMany
    {
        return $this->hasMany(ImpactFactor::class, 'categories_id_categories', 'id_categories');
    }

    /**
     * Relationship: Category has many Demand Requests
     */
    public function demandRequests(): HasMany
    {
        return $this->hasMany(DemandRequest::class, 'categories_id_categories', 'id_categories');
    }

    /**
     * Check if category prohibits B3 materials
     */
    public function isB3Prohibited(): bool
    {
        return (bool) $this->is_b3_prohibited;
    }

    /**
     * Check if category is active
     */
    public function isActive(): bool
    {
        return $this->status === 'AKTIF';
    }
}
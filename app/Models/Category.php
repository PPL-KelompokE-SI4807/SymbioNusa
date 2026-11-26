<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id_categories
 * @property string $nama
 * @property array<array{nama: string, tipe: string, wajib?: bool, satuan?: string, pilihan?: array<string>}>|null $spec_schema
 * @property bool $is_b3_prohibited
 * @property string $toleransi_susut_persen
 * @property string|null $kadar_air_maks_persen
 * @property string|null $toleransi_kadar_air_poin
 * @property string $tingkat_risiko
 * @property bool $wajib_sertifikat_lab
 * @property int|null $categories_id_categories
 * @property string $status
 */
class Category extends Model
{
    use HasFactory;

    protected $table = 'categories';

    protected $primaryKey = 'id_categories';

    public $timestamps = false;

    /**
     * @var list<string>
     */
    protected $fillable = [
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

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'spec_schema' => 'array',
            'is_b3_prohibited' => 'boolean',
            'toleransi_susut_persen' => 'decimal:2',
            'kadar_air_maks_persen' => 'decimal:2',
            'toleransi_kadar_air_poin' => 'decimal:2',
            'wajib_sertifikat_lab' => 'boolean',
            'categories_id_categories' => 'integer',
        ];
    }

    /**
     * Relasi ke kategori induk (parent).
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'categories_id_categories', 'id_categories');
    }

    /**
     * Relasi ke subkategori turunan (children).
     */
    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'categories_id_categories', 'id_categories');
    }

    /**
     * Alias relasi ke subkategori turunan.
     */
    public function subcategories(): HasMany
    {
        return $this->children();
    }

    /**
     * Relasi ke listing material.
     */
    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class, 'categories_id_categories', 'id_categories');
    }

    /**
     * Relasi ke permintaan kebutuhan (demand requests).
     */
    public function demandRequests(): HasMany
    {
        return $this->hasMany(DemandRequest::class, 'categories_id_categories', 'id_categories');
    }

    /**
     * Relasi ke faktor dampak lingkungan.
     */
    public function impactFactors(): HasMany
    {
        return $this->hasMany(ImpactFactor::class, 'categories_id_categories', 'id_categories');
    }

    /**
     * Scope hanya kategori berstatus aktif.
     *
     * @param  Builder<Category>  $query
     * @return Builder<Category>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'AKTIF');
    }

    /**
     * Scope hanya kategori level atas / induk tanpa parent.
     *
     * @param  Builder<Category>  $query
     * @return Builder<Category>
     */
    public function scopeRoot(Builder $query): Builder
    {
        return $query->whereNull('categories_id_categories');
    }

    /**
     * Periksa apakah kategori diarsipkan.
     */
    public function isArchived(): bool
    {
        return $this->status === 'ARSIP';
    }

    /**
     * Periksa apakah kategori adalah B3 terlarang.
     */
    public function isB3(): bool
    {
        return (bool) $this->is_b3_prohibited;
    }
}

<?php

namespace App\Models;

use App\Enums\DemandRequestFrequency;
use App\Enums\DemandRequestStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id_demand_request
 * @property array<string, mixed>|null $spesifikasi_min
 * @property string $volume_kg
 * @property string|null $harga_maks
 * @property Carbon|null $berlaku_sampai
 * @property DemandRequestStatus|string $status
 * @property int $companies_id_company
 * @property int $categories_id_categories
 * @property DemandRequestFrequency|string $frekuensi
 * @property-read Company|null $company
 * @property-read Category|null $category
 *
 * @method static Builder<static> open()
 */
class DemandRequest extends Model
{
    use HasFactory;

    protected $table = 'demand_requests';

    protected $primaryKey = 'id_demand_request';

    public $timestamps = false;

    protected $fillable = [
        'id_demand_request',
        'spesifikasi_min',
        'volume_kg',
        'harga_maks',
        'berlaku_sampai',
        'status',
        'companies_id_company',
        'categories_id_categories',
        'frekuensi',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'spesifikasi_min' => 'array',
            'volume_kg' => 'decimal:2',
            'harga_maks' => 'decimal:2',
            'berlaku_sampai' => 'date',
            'status' => DemandRequestStatus::class,
            'frekuensi' => DemandRequestFrequency::class,
        ];
    }

    /**
     * Scope untuk mengambil kebutuhan yang berstatus TERBUKA dan belum kedaluwarsa.
     *
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', DemandRequestStatus::TERBUKA->value)
            ->where(function (Builder $sub) {
                $sub->whereNull('berlaku_sampai')
                    ->orWhere('berlaku_sampai', '>=', Carbon::today());
            });
    }

    /**
     * Cek apakah kebutuhan sudah lewat batas berlaku.
     */
    public function isExpired(): bool
    {
        if ($this->status === DemandRequestStatus::KEDALUWARSA || $this->status === DemandRequestStatus::KEDALUWARSA->value) {
            return true;
        }

        return $this->berlaku_sampai !== null && $this->berlaku_sampai->isPast() && ! $this->berlaku_sampai->isToday();
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
}

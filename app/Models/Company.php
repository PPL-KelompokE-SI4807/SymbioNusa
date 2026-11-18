<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Kolom sesuai DDL tabel `companies`.
 *
 * @property int $id_company
 * @property string $nama
 * @property string $tipe
 * @property string|null $nib
 * @property string|null $alamat
 * @property string|null $kontak
 * @property string $status_verifikasi
 */
class Company extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'PENDING';
    public const STATUS_VERIFIED = 'TERVERIFIKASI';
    public const STATUS_REJECTED = 'DITOLAK';

    public const TYPE_SELLER = 'SELLER';
    public const TYPE_BUYER = 'BUYER';
    public const TYPE_BOTH = 'KEDUANYA';

    protected $table = 'companies';

    protected $primaryKey = 'id_company';

    public $timestamps = false;

    protected $fillable = [
        'id_company',
        'nama',
        'tipe',
        'nib',
        'alamat',
        'kontak',
        'lat',
        'lng',
        'tos_versi',
        'tos_disetujui_pada',
        'status_verifikasi',
    ];

    protected function casts(): array
    {
        return [
            'lat' => 'double',
            'lng' => 'double',
            'tos_disetujui_pada' => 'datetime',
        ];
    }

    public function isVerified(): bool
    {
        return $this->status_verifikasi === self::STATUS_VERIFIED;
    }

    public function canActAsSeller(): bool
    {
        return in_array($this->tipe, [self::TYPE_SELLER, self::TYPE_BOTH], true);
    }

    public function canActAsBuyer(): bool
    {
        return in_array($this->tipe, [self::TYPE_BUYER, self::TYPE_BOTH], true);
    }

    public function scopePendingVerification(Builder $query): Builder
    {
        return $query->where('status_verifikasi', self::STATUS_PENDING);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(CompanyDocument::class, 'id_company', 'id_company');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'id_company', 'id_company');
    }

    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class, 'companies_id_company', 'id_company');
    }

    public function demandRequests(): HasMany
    {
        return $this->hasMany(DemandRequest::class, 'companies_id_company', 'id_company');
    }

    public function purchaseOrdersAsSeller(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class, 'companies_id_seller', 'id_company');
    }

    public function purchaseOrdersAsBuyer(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class, 'companies_id_buyer', 'id_company');
    }

    public function disputes(): HasMany
    {
        return $this->hasMany(Dispute::class, 'companies_id_company', 'id_company');
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasFactory;

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
            'tos_disetujui_pada' => 'datetime:Y-m-d H:i:s',
            'lat' => 'decimal:8',
            'lng' => 'decimal:8',
        ];
    }

    /**
     * Relationship: Company has many Users
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'id_company', 'id_company');
    }

    /**
     * Relationship: Company has many Listings (as seller)
     */
    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class, 'companies_id_company', 'id_company');
    }

    /**
     * Relationship: Company has many RFQ Contracts (as buyer)
     */
    public function rfqContracts(): HasMany
    {
        return $this->hasMany(RfqContract::class, 'companies_id_company', 'id_company');
    }

    /**
     * Relationship: Company has many RFQ Offers (as seller)
     */
    public function rfqOffers(): HasMany
    {
        return $this->hasMany(RfqOffer::class, 'companies_id_company', 'id_company');
    }

    /**
     * Relationship: Company has many Demand Requests (as buyer)
     */
    public function demandRequests(): HasMany
    {
        return $this->hasMany(DemandRequest::class, 'companies_id_company', 'id_company');
    }

    /**
     * Check if company can act as seller
     */
    public function canSell(): bool
    {
        return in_array($this->tipe, ['SELLER', 'KEDUANYA']);
    }

    /**
     * Check if company can act as buyer
     */
    public function canBuy(): bool
    {
        return in_array($this->tipe, ['BUYER', 'KEDUANYA']);
    }
}
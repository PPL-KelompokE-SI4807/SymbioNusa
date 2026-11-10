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
            'lat' => 'double',
            'lng' => 'double',
            'tos_disetujui_pada' => 'datetime',
        ];
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

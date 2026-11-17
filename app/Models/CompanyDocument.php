<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyDocument extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'PENDING';
    public const STATUS_VALID = 'VALID';
    public const STATUS_EXPIRED = 'KEDALUWARSA';

    protected $table = 'company_documents';

    protected $primaryKey = 'id_company_document';

    public $timestamps = false;

    protected $fillable = [
        'jenis',
        'file_url',
        'berlaku_sampai',
        'status',
        'id_company',
    ];

    protected function casts(): array
    {
        return [
            'berlaku_sampai' => 'date',
        ];
    }

    public function isExpired(): bool
    {
        return $this->berlaku_sampai !== null && $this->berlaku_sampai->isPast();
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'id_company', 'id_company');
    }
}

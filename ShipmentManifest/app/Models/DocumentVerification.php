<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** STUB milik PBI-17. PBI-11 hanya membaca status DISETUJUI dan nama Inspector untuk manifes. */
class DocumentVerification extends Model
{
    public const DISETUJUI = 'DISETUJUI';
    public const DITOLAK = 'DITOLAK';
    public const MENUNGGU = 'MENUNGGU';

    protected $fillable = ['purchase_order_id', 'user_id', 'status', 'verified_at'];

    protected function casts(): array
    {
        return ['verified_at' => 'datetime'];
    }

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

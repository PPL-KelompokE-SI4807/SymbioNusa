<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inspection extends Model
{
    use HasFactory;

    protected $table = 'inspections';

    protected $primaryKey = 'id_inspection';

    public $timestamps = false;

    protected $fillable = [
        'id_inspection',
        'mode_verifikasi',
        'alasan_mode',
        'jadwal',
        'status',
        'purchase_orders_id_purchase_order',
        'users_id_user',
    ];

    protected function casts(): array
    {
        return [
            'jadwal' => 'datetime',
        ];
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_orders_id_purchase_order', 'id_purchase_order');
    }

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'users_id_user', 'id_user');
    }
}

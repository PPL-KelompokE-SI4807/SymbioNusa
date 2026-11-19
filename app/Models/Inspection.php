<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Tabel inspections (DDL).
 *
 * @property int $id_inspection
 * @property string $mode_verifikasi DESK | LAPANGAN
 * @property string|null $alasan_mode
 * @property Carbon|null $jadwal
 * @property string $status DITUGASKAN | DIPROSES | SELESAI | DIBATALKAN
 * @property int $purchase_orders_id_purchase_order
 * @property int $users_id_user
 * @property-read PurchaseOrder|null $purchaseOrder
 * @property-read User|null $inspector
 */
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

<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id_order_status_history
 * @property string|null $dari
 * @property string|null $ke
 * @property string|null $alasan
 * @property CarbonInterface|null $waktu
 * @property int $purchase_orders_id_purchase_order
 * @property int|null $users_id_user
 * @property-read PurchaseOrder|null $purchaseOrder
 * @property-read User|null $user
 */
class OrderStatusHistory extends Model
{
    use HasFactory;

    protected $table = 'order_status_history';

    protected $primaryKey = 'id_order_status_history';

    public $timestamps = false;

    protected $fillable = [
        'id_order_status_history',
        'dari',
        'ke',
        'alasan',
        'waktu',
        'purchase_orders_id_purchase_order',
        'users_id_user',
    ];

    protected function casts(): array
    {
        return [
            'waktu' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<PurchaseOrder, $this>
     */
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_orders_id_purchase_order', 'id_purchase_order');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'users_id_user', 'id_user');
    }
}

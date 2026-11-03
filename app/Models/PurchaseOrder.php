<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    protected $table = 'purchase_orders';

    protected $primaryKey = 'id_purchase_order';

    public $timestamps = false;

    protected $guarded = [];

    public function seller()
    {
        return $this->belongsTo(Company::class, 'companies_id_seller', 'id_company');
    }

    public function buyer()
    {
        return $this->belongsTo(Company::class, 'companies_id_buyer', 'id_company');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'purchase_orders_id_purchase_order', 'id_purchase_order');
    }
}

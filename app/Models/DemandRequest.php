<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DemandRequest extends Model
{
    protected $table = 'demand_requests';

    protected $primaryKey = 'id_demand_request';

    public $timestamps = false;

    protected $guarded = [];
}

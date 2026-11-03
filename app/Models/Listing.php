<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Listing extends Model
{
    protected $table = 'listings';

    protected $primaryKey = 'id_listings';

    public $timestamps = false;

    protected $guarded = [];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** STUB milik PBI-08. Dipakai PBI-11 hanya untuk nama, alamat, dan NIB para pihak di manifes. */
class Company extends Model
{
    protected $fillable = ['nama', 'tipe', 'nib', 'alamat', 'latitude', 'longitude', 'kontak', 'status_verifikasi'];
}

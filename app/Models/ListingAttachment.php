<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ListingAttachment extends Model
{
    use HasFactory;

    protected $table = 'listing_attachments';
    protected $primaryKey = 'id_listing_attachment';
    public $timestamps = false;

    protected $fillable = [
        'id_listing_attachment',
        'jenis',
        'file_url',
        'tanggal_pengambilan',
        'listings_id_listings',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_pengambilan' => 'date',
        ];
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class, 'listings_id_listings', 'id_listings');
    }
}
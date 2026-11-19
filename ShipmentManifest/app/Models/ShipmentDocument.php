<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShipmentDocument extends Model
{
    public const JENIS_SURAT_JALAN = 'SURAT_JALAN';

    protected $fillable = [
        'shipment_manifest_id', 'jenis', 'file_url', 'nama_asli', 'mime', 'ukuran_kb', 'uploaded_by',
    ];

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(ShipmentManifest::class, 'shipment_manifest_id');
    }
}

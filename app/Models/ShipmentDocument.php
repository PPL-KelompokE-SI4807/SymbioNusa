<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Surat jalan internal sebuah pengiriman (PBI-11 / SYM-15). Kolom sesuai DDL tabel `shipment_documents`.
 * `file_url` menyimpan path berkas di disk privat; berkas diunduh lewat controller, bukan URL publik.
 *
 * @property int $id_shipment_document
 * @property string|null $jenis
 * @property string|null $file_url
 * @property int $shipments_manifests_id_shipment_manifest
 * @property-read ShipmentManifest $shipment
 */
class ShipmentDocument extends Model
{
    public const JENIS_SURAT_JALAN = 'SURAT_JALAN';

    protected $table = 'shipment_documents';

    protected $primaryKey = 'id_shipment_document';

    public $timestamps = false;

    protected $fillable = [
        'jenis',
        'file_url',
        'shipments_manifests_id_shipment_manifest',
    ];

    protected function casts(): array
    {
        return [
            'shipments_manifests_id_shipment_manifest' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<ShipmentManifest, $this>
     */
    public function shipment(): BelongsTo
    {
        return $this->belongsTo(ShipmentManifest::class, 'shipments_manifests_id_shipment_manifest', 'id_shipment_manifest');
    }

    /** Nama berkas untuk ditampilkan dan diunduh (bagian setelah awalan unik). */
    public function fileName(): string
    {
        $base = basename((string) $this->file_url);

        return preg_replace('/^[A-Za-z0-9]{8}_/', '', $base) ?? $base;
    }
}

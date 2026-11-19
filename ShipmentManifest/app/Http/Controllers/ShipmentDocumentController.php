<?php

namespace App\Http\Controllers;

use App\Models\ShipmentDocument;
use App\Models\ShipmentManifest;
use App\Services\ShipmentService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class ShipmentDocumentController extends Controller
{
    public function download(ShipmentManifest $shipment, ShipmentDocument $document)
    {
        Gate::authorize('view', $shipment);
        abort_unless($document->shipment_manifest_id === $shipment->id, 404);

        $disk = Storage::disk(config('symbionusa.storage_disk'));
        abort_unless($disk->exists($document->file_url), 404, 'Berkas tidak ditemukan.');

        return $disk->download($document->file_url, $document->nama_asli);
    }

    public function destroy(ShipmentManifest $shipment, ShipmentDocument $document, ShipmentService $service)
    {
        Gate::authorize('deleteDocument', $shipment);
        abort_unless($document->shipment_manifest_id === $shipment->id, 404);

        $service->deleteDocument($document);

        return back()->with('success', 'Surat jalan dihapus.');
    }
}

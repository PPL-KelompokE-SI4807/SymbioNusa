<?php

namespace App\Http\Controllers;

use App\Models\ShipmentDocument;
use App\Models\ShipmentManifest;
use App\Services\Shipment\ShipmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** Unduh dan hapus surat jalan pengiriman (PBI-11 / SYM-15). Berkas di disk privat. */
class ShipmentDocumentController extends Controller
{
    public function download(ShipmentManifest $shipment, ShipmentDocument $document): StreamedResponse
    {
        Gate::authorize('view', $shipment);
        abort_unless($document->shipments_manifests_id_shipment_manifest === $shipment->id_shipment_manifest, 404);

        $disk = Storage::disk(ShipmentService::DISK);
        abort_unless($document->file_url && $disk->exists($document->file_url), 404, 'Berkas tidak ditemukan.');

        return $disk->download($document->file_url, $document->fileName());
    }

    public function destroy(ShipmentManifest $shipment, ShipmentDocument $document, ShipmentService $service): RedirectResponse
    {
        Gate::authorize('deleteDocument', $shipment);
        abort_unless($document->shipments_manifests_id_shipment_manifest === $shipment->id_shipment_manifest, 404);

        $service->deleteDocument($document);

        return back()->with('status', 'Surat jalan dihapus.');
    }
}

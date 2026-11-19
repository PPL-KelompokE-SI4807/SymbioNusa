<?php

namespace App\Services;

use App\Contracts\OrderTransition;
use App\Enums\OrderStatus;
use App\Enums\ShipmentStatus;
use App\Models\DocumentVerification;
use App\Models\PurchaseOrder;
use App\Models\ShipmentDocument;
use App\Models\ShipmentManifest;
use App\Models\User;
use App\Support\ShipmentTolerance;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

/** Logika bisnis PBI-11: syarat pengiriman, toleransi tonase tahap kirim, nomor manifes, siklus status. */
class ShipmentService
{
    public function __construct(private OrderTransition $orders)
    {
    }

    /** Order milik perusahaan Seller yang boleh dikirim. */
    public function eligibleOrders(User $seller): Collection
    {
        return PurchaseOrder::query()
            ->readyToShip()
            ->where('seller_company_id', $seller->company_id)
            ->with('buyer')
            ->orderBy('nomor_po')
            ->get();
    }

    /** @param  UploadedFile[]  $files */
    public function createDraft(User $actor, array $data, array $files = []): ShipmentManifest
    {
        return DB::transaction(function () use ($actor, $data, $files) {
            $order = PurchaseOrder::query()->lockForUpdate()->findOrFail($data['purchase_order_id']);

            $this->assertEligible($order, $actor);
            $data = $this->checkWeight($order, $data);
            $this->assertDocumentQuota(0, count($files));

            $shipment = ShipmentManifest::create(array_merge(
                Arr::except($data, ['dokumen']),
                ['status' => ShipmentStatus::Draf, 'created_by' => $actor->id]
            ));

            $this->storeDocuments($shipment, $files, $actor);

            return $shipment;
        });
    }

    /** @param  UploadedFile[]  $files */
    public function updateDraft(ShipmentManifest $shipment, User $actor, array $data, array $files = []): ShipmentManifest
    {
        return DB::transaction(function () use ($shipment, $actor, $data, $files) {
            $this->assertDraft($shipment);

            $order = PurchaseOrder::query()->lockForUpdate()->findOrFail($shipment->purchase_order_id);
            $this->assertEligible($order, $actor, $shipment);
            $data = $this->checkWeight($order, $data);
            $this->assertDocumentQuota($shipment->documents()->count(), count($files));

            $shipment->update(Arr::except($data, ['dokumen', 'purchase_order_id']));
            $this->storeDocuments($shipment, $files, $actor);

            return $shipment;
        });
    }

    /** Draf -> Dalam Pengiriman, terbitkan nomor manifes, order -> DALAM_PENGIRIMAN. */
    public function start(ShipmentManifest $shipment, User $actor): ShipmentManifest
    {
        return DB::transaction(function () use ($shipment, $actor) {
            $shipment = ShipmentManifest::query()->lockForUpdate()->findOrFail($shipment->id);
            $this->assertDraft($shipment);

            $order = PurchaseOrder::query()->lockForUpdate()->findOrFail($shipment->purchase_order_id);
            $this->assertEligible($order, $actor, $shipment);
            $this->checkWeight($order, [
                'berat_dikirim_kg' => $shipment->berat_dikirim_kg,
                'alasan_di_bawah_batas' => $shipment->alasan_di_bawah_batas,
            ]);

            $shipment->update([
                'no_manifes' => $this->nextManifestNumber(),
                'status' => ShipmentStatus::DalamPengiriman,
                'berangkat_at' => now(),
            ]);

            $this->orders->markInTransit($order, $actor, "Manifes {$shipment->no_manifes} diterbitkan");

            return $shipment;
        });
    }

    public function markArrived(ShipmentManifest $shipment): ShipmentManifest
    {
        if ($shipment->status !== ShipmentStatus::DalamPengiriman) {
            throw ValidationException::withMessages(['status' => 'Hanya pengiriman berstatus Dalam Pengiriman yang dapat ditandai tiba.']);
        }

        $shipment->update(['status' => ShipmentStatus::Tiba, 'tiba_at' => now()]);

        return $shipment;
    }

    public function cancelDraft(ShipmentManifest $shipment, ?string $reason): ShipmentManifest
    {
        $this->assertDraft($shipment);

        $shipment->update([
            'status' => ShipmentStatus::Dibatalkan,
            'dibatalkan_at' => now(),
            'alasan_batal' => $reason,
        ]);

        return $shipment;
    }

    public function deleteDocument(ShipmentDocument $document): void
    {
        Storage::disk(config('symbionusa.storage_disk'))->delete($document->file_url);
        $document->delete();
    }

    // ---------------------------------------------------------------- aturan

    /** Syarat PBI-11: milik Seller, order dibayar, verifikasi dokumen non-B3 disetujui, belum ada pengiriman aktif. */
    public function assertEligible(PurchaseOrder $order, User $actor, ?ShipmentManifest $ignore = null): void
    {
        $fail = fn (string $msg) => throw ValidationException::withMessages(['purchase_order_id' => $msg]);

        if ((int) $order->seller_company_id !== (int) $actor->company_id) {
            $fail('Order ini bukan milik perusahaan Anda.');
        }

        if ($order->status !== OrderStatus::Dibayar) {
            $fail('Pengiriman hanya dapat dibuat untuk order yang sudah berstatus Dibayar.');
        }

        $verified = $order->documentVerifications()->where('status', DocumentVerification::DISETUJUI)->exists();
        if (! $verified) {
            $fail('Verifikasi dokumen legalitas non-B3 untuk order ini belum disetujui.');
        }

        $busy = $order->shipments()
            ->whereIn('status', array_map(fn (ShipmentStatus $s) => $s->value, ShipmentStatus::active()))
            ->when($ignore, fn ($q) => $q->where('id', '!=', $ignore->id))
            ->exists();
        if ($busy) {
            $fail('Order ini sudah memiliki pengiriman aktif.');
        }
    }

    /**
     * Berat > Bmax ditolak; berat < Bmin boleh dengan alasan wajib.
     * Mengembalikan data (alasan dikosongkan bila berat dalam toleransi).
     */
    public function checkWeight(PurchaseOrder $order, array $data): array
    {
        $tol = $order->tolerance();
        $weight = (float) $data['berat_dikirim_kg'];
        $fmt = fn (float $n) => number_format($n, 2, ',', '.');

        switch ($tol->evaluate($weight)) {
            case ShipmentTolerance::OVER:
                throw ValidationException::withMessages([
                    'berat_dikirim_kg' => "Berat dikirim {$fmt($weight)} kg melebihi batas atas toleransi PO ({$fmt($tol->max())} kg).",
                ]);

            case ShipmentTolerance::UNDER:
                if (blank($data['alasan_di_bawah_batas'] ?? null)) {
                    throw ValidationException::withMessages([
                        'alasan_di_bawah_batas' => "Berat di bawah batas bawah toleransi ({$fmt($tol->min())} kg). Isi alasan Seller; selisih akan dievaluasi saat penerimaan.",
                    ]);
                }
                break;

            default:
                $data['alasan_di_bawah_batas'] = null;
        }

        return $data;
    }

    private function assertDraft(ShipmentManifest $shipment): void
    {
        if (! $shipment->isDraft()) {
            throw ValidationException::withMessages(['status' => 'Aksi ini hanya berlaku untuk pengiriman berstatus Draf.']);
        }
    }

    private function assertDocumentQuota(int $existing, int $adding): void
    {
        $max = (int) config('symbionusa.shipment_max_documents');

        if ($existing + $adding > $max) {
            throw ValidationException::withMessages(['dokumen' => "Maksimal {$max} surat jalan per pengiriman."]);
        }
    }

    /** MNF-YYYYMM-0001, urut per bulan. Dipanggil di dalam transaksi. */
    private function nextManifestNumber(): string
    {
        $prefix = config('symbionusa.manifest_prefix').'-'.now()->format('Ym').'-';

        $last = ShipmentManifest::query()
            ->where('no_manifes', 'like', $prefix.'%')
            ->lockForUpdate()
            ->orderByDesc('no_manifes')
            ->value('no_manifes');

        $seq = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    /** @param  UploadedFile[]  $files */
    private function storeDocuments(ShipmentManifest $shipment, array $files, User $actor): void
    {
        $disk = config('symbionusa.storage_disk');

        foreach ($files as $file) {
            $path = $file->store("shipment-documents/{$shipment->id}", $disk);

            $shipment->documents()->create([
                'jenis' => ShipmentDocument::JENIS_SURAT_JALAN,
                'file_url' => $path,
                'nama_asli' => $file->getClientOriginalName(),
                'mime' => $file->getClientMimeType(),
                'ukuran_kb' => (int) ceil($file->getSize() / 1024),
                'uploaded_by' => $actor->id,
            ]);
        }
    }
}

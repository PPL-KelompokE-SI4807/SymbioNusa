<?php

namespace App\Services\Shipment;

use App\Enums\OrderStatus;
use App\Enums\ShipmentStatus;
use App\Models\DocumentVerification;
use App\Models\PurchaseOrder;
use App\Models\ShipmentDocument;
use App\Models\ShipmentManifest;
use App\Models\User;
use App\Services\OrderStateMachine;
use App\Support\Format;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Logika PBI-11 (SYM-15) Pengiriman & Manifes Angkut: syarat kirim, toleransi tonase tahap kirim,
 * nomor manifes, dan siklus status manifes. Perubahan status order lewat OrderStateMachine (PBI-05).
 */
class ShipmentService
{
    public const DISK = 'local';

    public const MAX_DOCUMENTS = 5;

    public const MAX_UPLOAD_KB = 5120;

    public const MANIFEST_PREFIX = 'MNF';

    public const WEIGHT_OK = 'OK';

    public const WEIGHT_UNDER = 'UNDER';

    public const WEIGHT_OVER = 'OVER';

    /**
     * Order milik perusahaan Seller yang siap dikirim: Dibayar, verifikasi dokumen non-B3 disetujui,
     * dan belum punya pengiriman aktif.
     *
     * @return Collection<int, PurchaseOrder>
     */
    public function eligibleOrders(User $seller): Collection
    {
        return $this->readyToShipQuery()
            ->where('companies_id_seller', $seller->id_company)
            ->with(['buyer', 'rfqContract.listing.category'])
            ->orderBy('id_purchase_order')
            ->get();
    }

    /**
     * @return Builder<PurchaseOrder>
     */
    public function readyToShipQuery(): Builder
    {
        return PurchaseOrder::query()
            ->where('status', OrderStatus::DIBAYAR->value)
            ->whereHas('documentVerifications', fn (Builder $v) => $v->where('status', DocumentVerification::STATUS_SETUJU))
            ->whereNotIn('id_purchase_order', ShipmentManifest::query()
                ->select('purchase_orders_id_purchase_order')
                ->whereIn('status', ShipmentStatus::activeValues()));
    }

    public function evaluateWeight(PurchaseOrder $order, float $weightKg): string
    {
        $weightKg = round($weightKg, 2);

        return match (true) {
            $weightKg > $order->maxVolumeKg() => self::WEIGHT_OVER,
            $weightKg < $order->minVolumeKg() => self::WEIGHT_UNDER,
            default => self::WEIGHT_OK,
        };
    }

    /**
     * Simpan draf baru; bila $issue true, langsung terbitkan manifesnya dalam transaksi yang sama.
     *
     * @param  array<string, mixed>  $data
     * @param  list<UploadedFile>  $files
     */
    public function create(User $actor, array $data, array $files = [], bool $issue = false): ShipmentManifest
    {
        return DB::transaction(function () use ($actor, $data, $files, $issue) {
            $order = PurchaseOrder::query()->lockForUpdate()->findOrFail($data['purchase_orders_id_purchase_order']);

            $this->assertEligible($order, $actor);
            $this->assertNotOverweight($order, (float) $data['berat_dikirim_kg']);
            $this->assertDocumentQuota(0, count($files));

            $shipment = ShipmentManifest::create([
                ...$this->fields($data),
                'purchase_orders_id_purchase_order' => $order->id_purchase_order,
                'status' => ShipmentStatus::DRAF,
            ]);

            $this->storeDocuments($shipment, $files);

            return $issue ? $this->issueLocked($shipment, $order, $actor, $data['alasan_berat'] ?? null) : $shipment;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  list<UploadedFile>  $files
     */
    public function update(ShipmentManifest $shipment, User $actor, array $data, array $files = [], bool $issue = false): ShipmentManifest
    {
        return DB::transaction(function () use ($shipment, $actor, $data, $files, $issue) {
            $shipment = ShipmentManifest::query()->lockForUpdate()->findOrFail($shipment->id_shipment_manifest);
            $this->assertDraft($shipment);

            $order = PurchaseOrder::query()->lockForUpdate()->findOrFail($shipment->purchase_orders_id_purchase_order);
            $this->assertEligible($order, $actor, $shipment);
            $this->assertNotOverweight($order, (float) $data['berat_dikirim_kg']);
            $this->assertDocumentQuota($shipment->documents()->count(), count($files));

            $shipment->update($this->fields($data));
            $this->storeDocuments($shipment, $files);

            return $issue ? $this->issueLocked($shipment, $order, $actor, $data['alasan_berat'] ?? null) : $shipment;
        });
    }

    /** Draf → Dalam Perjalanan: terbitkan nomor manifes, order DIBAYAR → DALAM_PENGIRIMAN. */
    public function issue(ShipmentManifest $shipment, User $actor, ?string $underweightReason = null): ShipmentManifest
    {
        return DB::transaction(function () use ($shipment, $actor, $underweightReason) {
            $shipment = ShipmentManifest::query()->lockForUpdate()->findOrFail($shipment->id_shipment_manifest);
            $order = PurchaseOrder::query()->lockForUpdate()->findOrFail($shipment->purchase_orders_id_purchase_order);

            return $this->issueLocked($shipment, $order, $actor, $underweightReason);
        });
    }

    public function markArrived(ShipmentManifest $shipment): ShipmentManifest
    {
        return DB::transaction(function () use ($shipment) {
            $shipment = ShipmentManifest::query()->lockForUpdate()->findOrFail($shipment->id_shipment_manifest);

            if ($shipment->status !== ShipmentStatus::DALAM_PERJALANAN) {
                throw ValidationException::withMessages(['status' => 'Hanya pengiriman yang sedang dalam perjalanan yang dapat ditandai tiba.']);
            }

            $shipment->update(['status' => ShipmentStatus::TIBA]);

            return $shipment;
        });
    }

    public function cancelDraft(ShipmentManifest $shipment): ShipmentManifest
    {
        return DB::transaction(function () use ($shipment) {
            $shipment = ShipmentManifest::query()->lockForUpdate()->findOrFail($shipment->id_shipment_manifest);
            $this->assertDraft($shipment);

            $shipment->update(['status' => ShipmentStatus::DIBATALKAN]);

            return $shipment;
        });
    }

    public function deleteDocument(ShipmentDocument $document): void
    {
        DB::transaction(function () use ($document) {
            $document->delete();
        });

        if ($document->file_url) {
            Storage::disk(self::DISK)->delete($document->file_url);
        }
    }

    /**
     * Syarat PBI-11: order milik Seller, berstatus Dibayar, verifikasi dokumen non-B3 disetujui,
     * dan belum ada pengiriman aktif lain.
     */
    public function assertEligible(PurchaseOrder $order, User $actor, ?ShipmentManifest $ignore = null): void
    {
        $fail = fn (string $message) => throw ValidationException::withMessages(['purchase_orders_id_purchase_order' => $message]);

        if ((int) $order->companies_id_seller !== (int) $actor->id_company) {
            $fail('Order ini bukan milik perusahaan Anda.');
        }

        if ($order->status !== OrderStatus::DIBAYAR) {
            $fail('Pengiriman hanya dapat dibuat untuk order yang sudah Dibayar.');
        }

        $verified = $order->documentVerifications()->where('status', DocumentVerification::STATUS_SETUJU)->exists();
        if (! $verified) {
            $fail('Verifikasi dokumen legalitas non-B3 untuk order ini belum disetujui Inspector.');
        }

        $busy = ShipmentManifest::query()
            ->where('purchase_orders_id_purchase_order', $order->id_purchase_order)
            ->whereIn('status', ShipmentStatus::activeValues())
            ->when($ignore, fn (Builder $q) => $q->where('id_shipment_manifest', '!=', $ignore->id_shipment_manifest))
            ->exists();
        if ($busy) {
            $fail('Order ini sudah memiliki pengiriman aktif.');
        }
    }

    private function issueLocked(ShipmentManifest $shipment, PurchaseOrder $order, User $actor, ?string $underweightReason): ShipmentManifest
    {
        $this->assertDraft($shipment);
        $this->assertEligible($order, $actor, $shipment);

        $weight = (float) $shipment->berat_dikirim_kg;
        $this->assertNotOverweight($order, $weight);

        $reason = trim((string) $underweightReason);
        $under = $this->evaluateWeight($order, $weight) === self::WEIGHT_UNDER;
        if ($under && $reason === '') {
            throw ValidationException::withMessages([
                'alasan_berat' => 'Berat dikirim di bawah batas bawah toleransi ('.Format::kg($order->minVolumeKg()).'). Isi alasan sebelum menerbitkan manifes.',
            ]);
        }

        $number = $this->nextManifestNumber();

        $shipment->update([
            'no_manifes' => $number,
            'status' => ShipmentStatus::DALAM_PERJALANAN,
            'manifes_url' => route('shipments.manifest', $shipment, false),
        ]);

        $note = "Manifes {$number} diterbitkan.";
        if ($under) {
            $note .= ' Berat dikirim '.Format::kg($weight).' di bawah batas bawah toleransi '.Format::kg($order->minVolumeKg()).'. Alasan Seller: '.$reason;
        }

        OrderStateMachine::transition($order, OrderStatus::DALAM_PENGIRIMAN, $actor, $note);

        return $shipment;
    }

    private function assertNotOverweight(PurchaseOrder $order, float $weightKg): void
    {
        if ($this->evaluateWeight($order, $weightKg) === self::WEIGHT_OVER) {
            throw ValidationException::withMessages([
                'berat_dikirim_kg' => 'Berat dikirim '.Format::kg($weightKg).' melebihi batas atas toleransi PO ('.Format::kg($order->maxVolumeKg()).').',
            ]);
        }
    }

    private function assertDraft(ShipmentManifest $shipment): void
    {
        if (! $shipment->isDraft()) {
            throw ValidationException::withMessages(['status' => 'Aksi ini hanya berlaku untuk pengiriman berstatus Draf.']);
        }
    }

    private function assertDocumentQuota(int $existing, int $adding): void
    {
        if ($existing + $adding > self::MAX_DOCUMENTS) {
            throw ValidationException::withMessages(['dokumen' => 'Maksimal '.self::MAX_DOCUMENTS.' surat jalan per pengiriman.']);
        }
    }

    /** MNF-YYYYMM-0001, urut per bulan. Dipanggil di dalam transaksi. */
    private function nextManifestNumber(): string
    {
        $prefix = self::MANIFEST_PREFIX.'-'.now()->format('Ym').'-';

        $last = ShipmentManifest::query()
            ->where('no_manifes', 'like', $prefix.'%')
            ->lockForUpdate()
            ->orderByDesc('no_manifes')
            ->value('no_manifes');

        $sequence = $last ? ((int) substr((string) $last, -4)) + 1 : 1;

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function fields(array $data): array
    {
        return [
            'armada' => $data['armada'],
            'plat' => $data['plat'],
            'sopir' => $data['sopir'],
            'jadwal' => $data['jadwal'],
            'rute' => $data['rute'],
            'berat_dikirim_kg' => $data['berat_dikirim_kg'],
        ];
    }

    /**
     * @param  list<UploadedFile>  $files
     */
    private function storeDocuments(ShipmentManifest $shipment, array $files): void
    {
        foreach ($files as $file) {
            $original = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $slug = Str::limit(Str::slug($original), 60, '') ?: 'surat-jalan';
            $name = Str::random(8).'_'.$slug.'.'.$file->extension();

            $path = $file->storeAs("shipment-documents/{$shipment->id_shipment_manifest}", $name, self::DISK);

            $shipment->documents()->create([
                'jenis' => ShipmentDocument::JENIS_SURAT_JALAN,
                'file_url' => $path,
            ]);
        }
    }
}

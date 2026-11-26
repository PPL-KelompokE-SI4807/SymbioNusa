<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\ShipmentStatus;
use App\Http\Requests\ShipmentRequest;
use App\Models\DocumentVerification;
use App\Models\PurchaseOrder;
use App\Models\ShipmentManifest;
use App\Services\Shipment\ShipmentService;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/** PBI-11 (SYM-15): Pengiriman & Manifes Angkut. */
class ShipmentController extends Controller
{
    public function __construct(private readonly ShipmentService $service) {}

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', ShipmentManifest::class);

        $user = $request->user();
        $search = trim((string) $request->query('q'));
        $status = ShipmentStatus::tryFrom((string) $request->query('status'));

        $base = ShipmentManifest::query()->visibleTo($user);

        /** @var array<string, int> $counts */
        $counts = (clone $base)->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status')->all();

        $shipments = (clone $base)
            ->with(['purchaseOrder.buyer', 'purchaseOrder.seller'])
            ->when($status, fn (Builder $q) => $q->where('status', $status->value))
            ->when($search !== '', function (Builder $q) use ($search) {
                $poId = (int) preg_replace('/\D/', '', $search);
                $q->where(function (Builder $w) use ($search, $poId) {
                    $w->where('no_manifes', 'like', "%{$search}%")
                        ->orWhere('plat', 'like', "%{$search}%")
                        ->orWhere('sopir', 'like', "%{$search}%")
                        ->orWhere('rute', 'like', "%{$search}%");
                    if ($poId > 0) {
                        $w->orWhere('purchase_orders_id_purchase_order', $poId);
                    }
                });
            })
            ->orderByDesc('id_shipment_manifest')
            ->paginate(15)
            ->withQueryString();

        $canCreate = $user->can('create', ShipmentManifest::class);

        return view('shipments.index', [
            'shipments' => $shipments,
            'counts' => $counts,
            'search' => $search,
            'status' => $status,
            'canCreate' => $canCreate,
            'readyOrders' => $canCreate ? $this->service->eligibleOrders($user) : collect(),
            'issuedThisMonth' => (clone $base)->where('no_manifes', 'like', ShipmentService::MANIFEST_PREFIX.'-'.now()->format('Ym').'-%')->count(),
        ]);
    }

    public function create(Request $request): View
    {
        Gate::authorize('create', ShipmentManifest::class);

        $orders = $this->service->eligibleOrders($request->user());

        // Pilihan awal: input lama setelah galat validasi, lalu ?order= dari daftar "Siap dikirim", lalu order pertama.
        $requested = (int) ($request->old('purchase_orders_id_purchase_order') ?? $request->query('order'));
        $selected = $orders->firstWhere('id_purchase_order', $requested) ?? $orders->first();

        return view('shipments.form', [
            'shipment' => null,
            'orders' => $orders,
            'selectedOrderId' => $selected?->id_purchase_order,
        ]);
    }

    public function store(ShipmentRequest $request): RedirectResponse
    {
        Gate::authorize('create', ShipmentManifest::class);

        /** @var list<UploadedFile> $files */
        $files = $request->file('dokumen', []);
        $shipment = $this->service->create($request->user(), $request->validated(), $files, $request->wantsIssue());

        return redirect()->route('shipments.show', $shipment)->with('status', $shipment->isDraft()
            ? 'Draf pengiriman tersimpan. Periksa datanya, lalu terbitkan manifes saat truk siap berangkat.'
            : "Manifes {$shipment->no_manifes} terbit. Order kini Dalam Pengiriman.");
    }

    public function show(ShipmentManifest $shipment): View
    {
        Gate::authorize('view', $shipment);

        $shipment->load(['purchaseOrder.buyer', 'purchaseOrder.seller', 'purchaseOrder.rfqContract.listing.category', 'documents']);
        $order = $shipment->purchaseOrder;

        return view('shipments.show', [
            'shipment' => $shipment,
            'order' => $order,
            'verification' => $this->approvedVerification($order),
            'weightState' => $this->service->evaluateWeight($order, (float) $shipment->berat_dikirim_kg),
            'issuedAt' => $this->issuedAt($shipment),
        ]);
    }

    public function edit(ShipmentManifest $shipment): View
    {
        Gate::authorize('update', $shipment);

        $shipment->load(['purchaseOrder.buyer', 'purchaseOrder.rfqContract.listing.category', 'documents']);

        return view('shipments.form', [
            'shipment' => $shipment,
            'orders' => collect([$shipment->purchaseOrder]),
            'selectedOrderId' => $shipment->purchase_orders_id_purchase_order,
        ]);
    }

    public function update(ShipmentRequest $request, ShipmentManifest $shipment): RedirectResponse
    {
        Gate::authorize('update', $shipment);

        /** @var list<UploadedFile> $files */
        $files = $request->file('dokumen', []);
        $shipment = $this->service->update($shipment, $request->user(), $request->validated(), $files, $request->wantsIssue());

        return redirect()->route('shipments.show', $shipment)->with('status', $shipment->isDraft()
            ? 'Draf pengiriman diperbarui.'
            : "Manifes {$shipment->no_manifes} terbit. Order kini Dalam Pengiriman.");
    }

    public function issue(Request $request, ShipmentManifest $shipment): RedirectResponse
    {
        Gate::authorize('issue', $shipment);

        $data = $request->validate(['alasan_berat' => ['nullable', 'string', 'max:500']]);
        $shipment = $this->service->issue($shipment, $request->user(), $data['alasan_berat'] ?? null);

        return redirect()->route('shipments.show', $shipment)
            ->with('status', "Manifes {$shipment->no_manifes} terbit. Order kini Dalam Pengiriman.");
    }

    public function arrive(ShipmentManifest $shipment): RedirectResponse
    {
        Gate::authorize('arrive', $shipment);

        $this->service->markArrived($shipment);

        return redirect()->route('shipments.show', $shipment)
            ->with('status', 'Pengiriman ditandai tiba. Buyer kini dapat mencatat penerimaan barang.');
    }

    public function cancel(ShipmentManifest $shipment): RedirectResponse
    {
        Gate::authorize('cancel', $shipment);

        $this->service->cancelDraft($shipment);

        return redirect()->route('shipments.index')->with('status', 'Draf pengiriman dibatalkan.');
    }

    /** Manifes Angkut sebagai halaman cetak (Cetak / Simpan sebagai PDF lewat dialog cetak peramban). */
    public function manifest(ShipmentManifest $shipment): View
    {
        Gate::authorize('downloadManifest', $shipment);

        $shipment->load(['purchaseOrder.buyer', 'purchaseOrder.seller', 'purchaseOrder.rfqContract.listing.category']);
        $order = $shipment->purchaseOrder;

        return view('shipments.manifest', [
            'shipment' => $shipment,
            'order' => $order,
            'verification' => $this->approvedVerification($order),
            'issuedAt' => $this->issuedAt($shipment),
        ]);
    }

    private function approvedVerification(PurchaseOrder $order): ?DocumentVerification
    {
        return $order->documentVerifications()
            ->with('inspector')
            ->where('status', DocumentVerification::STATUS_SETUJU)
            ->orderByDesc('id_document_verification')
            ->first();
    }

    /** Waktu terbit manifes = waktu order berpindah ke Dalam Pengiriman (DDL manifes tidak punya kolom waktu). */
    private function issuedAt(ShipmentManifest $shipment): ?CarbonInterface
    {
        if ($shipment->no_manifes === null) {
            return null;
        }

        return $shipment->purchaseOrder->statusHistory()
            ->where('ke', OrderStatus::DALAM_PENGIRIMAN->value)
            ->reorder('id_order_status_history', 'desc')
            ->first()?->waktu;
    }
}

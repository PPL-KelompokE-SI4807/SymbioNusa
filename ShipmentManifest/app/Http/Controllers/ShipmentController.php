<?php

namespace App\Http\Controllers;

use App\Enums\ShipmentStatus;
use App\Http\Requests\ShipmentRequest;
use App\Models\DocumentVerification;
use App\Models\ShipmentManifest;
use App\Services\ShipmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ShipmentController extends Controller
{
    public function __construct(private ShipmentService $service)
    {
    }

    public function index(Request $request)
    {
        Gate::authorize('viewAny', ShipmentManifest::class);

        $user = $request->user();
        $search = trim((string) $request->query('q'));
        $status = ShipmentStatus::tryFrom((string) $request->query('status'));

        $base = ShipmentManifest::query()->visibleTo($user);

        $counts = (clone $base)->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        $shipments = (clone $base)
            ->with(['order.buyer', 'order.seller'])
            ->when($status, fn ($q) => $q->where('status', $status->value))
            ->when($search !== '', fn ($q) => $q->where(function ($w) use ($search) {
                $w->where('no_manifes', 'like', "%{$search}%")
                    ->orWhere('plat', 'like', "%{$search}%")
                    ->orWhere('sopir', 'like', "%{$search}%")
                    ->orWhereHas('order', fn ($o) => $o->where('nomor_po', 'like', "%{$search}%"));
            }))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('shipments.index', [
            'shipments' => $shipments,
            'counts' => $counts,
            'search' => $search,
            'status' => $status,
            'canCreate' => $user->can('create', ShipmentManifest::class),
        ]);
    }

    public function create(Request $request)
    {
        Gate::authorize('create', ShipmentManifest::class);

        return view('shipments.form', [
            'shipment' => null,
            'orders' => $this->service->eligibleOrders($request->user()),
            'selectedOrderId' => $request->integer('order'),
        ]);
    }

    public function store(ShipmentRequest $request)
    {
        Gate::authorize('create', ShipmentManifest::class);

        $shipment = $this->service->createDraft(
            $request->user(),
            $request->validated(),
            $request->file('dokumen', [])
        );

        return redirect()->route('shipments.show', $shipment)
            ->with('success', 'Draf pengiriman tersimpan. Periksa data lalu tekan "Mulai Pengiriman" untuk menerbitkan manifes.');
    }

    public function show(ShipmentManifest $shipment)
    {
        Gate::authorize('view', $shipment);

        $shipment->load(['order.buyer', 'order.seller', 'order.documentVerifications.inspector', 'documents', 'creator']);

        $verification = $shipment->order->documentVerifications
            ->firstWhere('status', DocumentVerification::DISETUJUI);

        return view('shipments.show', [
            'shipment' => $shipment,
            'order' => $shipment->order,
            'tolerance' => $shipment->order->tolerance(),
            'verification' => $verification,
        ]);
    }

    public function edit(ShipmentManifest $shipment)
    {
        Gate::authorize('update', $shipment);

        $shipment->load(['order.buyer', 'documents']);

        return view('shipments.form', [
            'shipment' => $shipment,
            'orders' => collect(),
            'selectedOrderId' => $shipment->purchase_order_id,
        ]);
    }

    public function update(ShipmentRequest $request, ShipmentManifest $shipment)
    {
        Gate::authorize('update', $shipment);

        $this->service->updateDraft($shipment, $request->user(), $request->validated(), $request->file('dokumen', []));

        return redirect()->route('shipments.show', $shipment)->with('success', 'Draf pengiriman diperbarui.');
    }

    public function start(Request $request, ShipmentManifest $shipment)
    {
        Gate::authorize('start', $shipment);

        $shipment = $this->service->start($shipment, $request->user());

        return redirect()->route('shipments.show', $shipment)
            ->with('success', "Pengiriman dimulai. Manifes {$shipment->no_manifes} telah diterbitkan.");
    }

    public function arrive(ShipmentManifest $shipment)
    {
        Gate::authorize('arrive', $shipment);

        $this->service->markArrived($shipment);

        return redirect()->route('shipments.show', $shipment)
            ->with('success', 'Pengiriman ditandai tiba. Buyer dapat mengonfirmasi penerimaan (PBI-12).');
    }

    public function cancel(Request $request, ShipmentManifest $shipment)
    {
        Gate::authorize('cancel', $shipment);

        $data = $request->validate(['alasan_batal' => ['nullable', 'string', 'max:500']]);

        $this->service->cancelDraft($shipment, $data['alasan_batal'] ?? null);

        return redirect()->route('shipments.index')->with('success', 'Draf pengiriman dibatalkan.');
    }

    /** PDF bila barryvdh/laravel-dompdf terpasang; jika belum, halaman cetak HTML. */
    public function manifest(ShipmentManifest $shipment)
    {
        Gate::authorize('downloadManifest', $shipment);

        $shipment->load(['order.buyer', 'order.seller', 'order.documentVerifications.inspector']);

        $data = [
            'shipment' => $shipment,
            'order' => $shipment->order,
            'tolerance' => $shipment->order->tolerance(),
            'verification' => $shipment->order->documentVerifications->firstWhere('status', DocumentVerification::DISETUJUI),
            'asPdf' => false,
        ];

        $pdfFacade = '\\Barryvdh\\DomPDF\\Facade\\Pdf';

        if (class_exists($pdfFacade)) {
            $data['asPdf'] = true;

            return $pdfFacade::loadView('shipments.manifest', $data)
                ->setPaper('a4')
                ->stream("manifes-{$shipment->no_manifes}.pdf");
        }

        return view('shipments.manifest', $data);
    }
}

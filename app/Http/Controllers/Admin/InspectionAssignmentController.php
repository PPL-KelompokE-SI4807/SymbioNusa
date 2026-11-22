<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssignInspectionRequest;
use App\Models\Inspection;
use App\Models\PurchaseOrder;
use App\Services\Inspector\InspectionAssignmentService;
use App\Services\Inspector\InspectionModeAdvisor;
use App\Services\Inspector\InspectorMatchingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class InspectionAssignmentController extends Controller
{
    public function __construct(
        protected InspectionAssignmentService $assignmentService,
        protected InspectionModeAdvisor $modeAdvisor,
        protected InspectorMatchingService $matchingService
    ) {}

    /**
     * Menampilkan daftar PO yang menunggu penugasan dan riwayat inspeksi platform.
     */
    public function index(Request $request): View
    {
        Gate::authorize('assign', Inspection::class);

        $search = $request->query('search');
        $status = $request->query('status');

        $pendingOrders = $this->assignmentService->getOrdersAwaitingAssignment(
            search: is_string($search) ? $search : null,
            perPage: 10
        );

        $recentInspections = $this->assignmentService->getRecentInspections(
            status: is_string($status) ? $status : null,
            perPage: 10
        );

        return view('admin.inspections.index', [
            'pendingOrders' => $pendingOrders,
            'recentInspections' => $recentInspections,
            'currentStatus' => $status,
            'search' => $search,
        ]);
    }

    /**
     * Menampilkan formulir penugasan Inspector untuk Purchase Order tertentu (Layar 35).
     */
    public function create(PurchaseOrder $order): View
    {
        Gate::authorize('assign', Inspection::class);

        $order->loadMissing([
            'seller',
            'buyer',
            'rfqContract.listing.category',
        ]);

        $recommendation = $this->modeAdvisor->recommendMode($order);
        $inspectors = $this->matchingService->findEligibleInspectors($order, $recommendation['mode']);

        return view('admin.inspections.create', [
            'order' => $order,
            'recommendation' => $recommendation,
            'inspectors' => $inspectors,
        ]);
    }

    /**
     * Memproses penugasan Inspector untuk Purchase Order.
     */
    public function store(AssignInspectionRequest $request, PurchaseOrder $order): RedirectResponse
    {
        $this->assignmentService->assignInspection(
            po: $order,
            data: $request->validated(),
            actor: $request->user()
        );

        return redirect()
            ->route('admin.inspections.index')
            ->with('success', "Inspector berhasil ditugaskan untuk PO #{$order->id_purchase_order}.");
    }

    /**
     * Menampilkan detail penugasan inspeksi.
     */
    public function show(Inspection $inspection): View
    {
        Gate::authorize('view', $inspection);

        $inspection->load([
            'purchaseOrder.seller',
            'purchaseOrder.buyer',
            'purchaseOrder.rfqContract.listing.category',
            'inspector.inspectorProfile',
        ]);

        return view('admin.inspections.show', [
            'inspection' => $inspection,
        ]);
    }

    /**
     * Membatalkan penugasan inspeksi.
     */
    public function cancel(Request $request, Inspection $inspection): RedirectResponse
    {
        Gate::authorize('cancel', $inspection);

        $validated = $request->validate([
            'alasan_batal' => ['nullable', 'string', 'max:255'],
        ]);

        $this->assignmentService->cancelInspection(
            inspection: $inspection,
            reason: $validated['alasan_batal'] ?? null,
            actor: $request->user()
        );

        return redirect()
            ->route('admin.inspections.index')
            ->with('success', "Penugasan inspeksi #{$inspection->id_inspection} berhasil dibatalkan.");
    }

    /**
     * Tampilan daftar tugas untuk peran Inspector.
     */
    public function inspectorTasks(Request $request): View
    {
        $user = $request->user();
        if ($user->role !== 'INSPECTOR') {
            abort(403, 'Akses terbatas untuk Inspector.');
        }

        $status = $request->query('status');
        $inspections = $this->assignmentService->getInspectionsForInspector(
            inspector: $user,
            status: is_string($status) ? $status : null,
            perPage: 10
        );

        return view('inspector.tasks.index', [
            'inspections' => $inspections,
            'currentStatus' => $status,
        ]);
    }
}

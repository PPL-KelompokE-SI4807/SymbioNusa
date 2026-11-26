<?php

namespace App\Http\Controllers;

use App\Enums\DemandRequestStatus;
use App\Http\Requests\StoreDemandRequestRequest;
use App\Http\Requests\UpdateDemandRequestRequest;
use App\Models\Category;
use App\Models\DemandRequest;
use App\Services\Demand\DemandService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class DemandRequestController extends Controller
{
    public function __construct(
        protected DemandService $demandService
    ) {}

    /**
     * Daftar kebutuhan milik Buyer (Layar 27: Kebutuhan Saya).
     */
    public function buyerIndex(Request $request): View
    {
        $user = $request->user();
        if ($user === null || $user->role !== 'BUYER' || ! $user->id_company) {
            abort(403, 'Aksi ini hanya dapat diakses oleh Buyer yang terdaftar pada perusahaan.');
        }

        $statusFilter = $request->string('status')->toString();
        $categoryFilter = $request->input('category');

        $query = DemandRequest::with('category')
            ->where('companies_id_company', $user->id_company)
            ->orderByDesc('id_demand_request');

        if ($statusFilter && in_array($statusFilter, ['DRAF', 'TERBUKA', 'TERPENUHI', 'KEDALUWARSA'], true)) {
            $query->where('status', $statusFilter);
        }

        if ($categoryFilter) {
            $query->where('categories_id_categories', (int) $categoryFilter);
        }

        $demands = $query->paginate(10)->withQueryString();

        // KPI Counts
        $kpiBase = fn () => DemandRequest::where('companies_id_company', $user->id_company);
        $kpi = [
            'total' => $kpiBase()->count(),
            'terbuka' => $kpiBase()->where('status', DemandRequestStatus::TERBUKA->value)->count(),
            'terpenuhi' => $kpiBase()->where('status', DemandRequestStatus::TERPENUHI->value)->count(),
            'draf' => $kpiBase()->where('status', DemandRequestStatus::DRAF->value)->count(),
        ];

        $categories = Category::where('status', 'AKTIF')
            ->where('is_b3_prohibited', false)
            ->orderBy('nama')
            ->get();

        return view('demand.buyer.index', compact('demands', 'kpi', 'categories'));
    }

    /**
     * Formulir pembuatan kebutuhan bahan baku oleh Buyer (Layar 29).
     */
    public function create(): View
    {
        Gate::authorize('create', DemandRequest::class);

        $categories = Category::where('status', 'AKTIF')
            ->where('is_b3_prohibited', false)
            ->orderBy('nama')
            ->get();

        return view('demand.buyer.create', compact('categories'));
    }

    /**
     * Menyimpan publikasi atau draf kebutuhan baru oleh Buyer.
     */
    public function store(StoreDemandRequestRequest $request): RedirectResponse
    {
        $user = $request->user();
        if ($user === null) {
            abort(401);
        }

        $demand = $this->demandService->createDemand($request->validated(), $user);

        $pesan = $demand->status === DemandRequestStatus::DRAF || $demand->status === DemandRequestStatus::DRAF->value
            ? 'Draf kebutuhan berhasil disimpan.'
            : 'Kebutuhan bahan baku berhasil dipublikasikan ke pasar.';

        return redirect()->route('buyer.demands.index')->with('success', $pesan);
    }

    /**
     * Formulir penyuntingan kebutuhan oleh Buyer pemilik.
     */
    public function edit(DemandRequest $demand): View
    {
        Gate::authorize('update', $demand);

        $categories = Category::where('status', 'AKTIF')
            ->where('is_b3_prohibited', false)
            ->orderBy('nama')
            ->get();

        $demand->load('category');

        return view('demand.buyer.edit', compact('demand', 'categories'));
    }

    /**
     * Memperbarui kebutuhan oleh Buyer pemilik.
     */
    public function update(UpdateDemandRequestRequest $request, DemandRequest $demand): RedirectResponse
    {
        $updated = $this->demandService->updateDemand($demand, $request->validated());

        $pesan = $updated->status === DemandRequestStatus::DRAF || $updated->status === DemandRequestStatus::DRAF->value
            ? 'Draf kebutuhan berhasil diperbarui.'
            : 'Kebutuhan bahan baku berhasil diperbarui dan dipublikasikan.';

        return redirect()->route('buyer.demands.index')->with('success', $pesan);
    }

    /**
     * Menutup kebutuhan oleh Buyer pemilik.
     */
    public function close(DemandRequest $demand): RedirectResponse
    {
        Gate::authorize('close', $demand);

        $this->demandService->closeDemand($demand);

        return redirect()->route('buyer.demands.index')
            ->with('success', 'Kebutuhan bahan baku berhasil ditutup.');
    }

    /**
     * Menghapus draf kebutuhan oleh Buyer pemilik.
     */
    public function destroy(DemandRequest $demand): RedirectResponse
    {
        Gate::authorize('delete', $demand);

        $this->demandService->deleteDemand($demand);

        return redirect()->route('buyer.demands.index')
            ->with('success', 'Draf kebutuhan berhasil dihapus.');
    }

    /**
     * Papan Kebutuhan Buyer bagi Seller (Layar 28).
     */
    public function sellerIndex(Request $request): View
    {
        $user = $request->user();
        if ($user === null || $user->role !== 'SELLER' || ! $user->id_company) {
            abort(403, 'Papan kebutuhan hanya dapat diakses oleh Seller yang memiliki profil perusahaan.');
        }

        $categoryFilter = $request->input('category');
        $wilayahFilter = $request->string('wilayah')->toString();
        $volMin = $request->input('vol_min');
        $volMax = $request->input('vol_max');

        $query = DemandRequest::open()
            ->with(['company', 'category'])
            ->orderByDesc('id_demand_request');

        if ($categoryFilter) {
            $query->where('categories_id_categories', (int) $categoryFilter);
        }

        if ($wilayahFilter) {
            $query->whereHas('company', function (Builder $q) use ($wilayahFilter) {
                $q->where('alamat', 'like', "%{$wilayahFilter}%")
                    ->orWhere('nama', 'like', "%{$wilayahFilter}%");
            });
        }

        if ($volMin !== null && is_numeric($volMin)) {
            $query->where('volume_kg', '>=', (float) $volMin);
        }

        if ($volMax !== null && is_numeric($volMax)) {
            $query->where('volume_kg', '<=', (float) $volMax);
        }

        $demands = $query->paginate(12)->withQueryString();

        $categories = Category::where('status', 'AKTIF')
            ->where('is_b3_prohibited', false)
            ->orderBy('nama')
            ->get();

        return view('demand.seller.index', compact('demands', 'categories'));
    }

    /**
     * Detail kebutuhan spesifik bagi Seller (Layar 28 detail).
     */
    public function sellerShow(DemandRequest $demand): View
    {
        Gate::authorize('view', $demand);

        $demand->load(['company', 'category']);

        return view('demand.seller.show', compact('demand'));
    }
}

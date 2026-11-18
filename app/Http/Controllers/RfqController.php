<?php

namespace App\Http\Controllers;

use App\Enums\RfqStatus;
use App\Http\Requests\CounterOfferRfqRequest;
use App\Http\Requests\RejectRfqRequest;
use App\Http\Requests\StoreRfqRequest;
use App\Models\Category;
use App\Models\Listing;
use App\Models\RfqContract;
use App\Services\Rfq\RfqService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RfqController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected RfqService $rfqService
    ) {}

    /**
     * Menampilkan daftar RFQ milik perusahaan (Buyer atau Seller).
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $this->authorize('viewAny', RfqContract::class);

        $query = RfqContract::with(['listing.category', 'listing.company', 'company', 'latestOffer']);

        if ($user->role === 'SELLER') {
            $query->whereHas('listing', function ($q) use ($user) {
                $q->where('companies_id_company', $user->id_company);
            });
        } elseif ($user->role === 'BUYER') {
            $query->where('companies_id_company', $user->id_company);
        }

        // Hitung metrik KPI strip (Screen 07)
        $kpiBaseQuery = clone $query;
        $allContracts = $kpiBaseQuery->get(['id_rfq_contract', 'status']);
        $totalRfq = $allContracts->count();
        $terbukaNegosiasi = $allContracts->whereIn('status', [RfqStatus::TERBUKA, RfqStatus::NEGOSIASI])->count();
        $disepakati = $allContracts->where('status', RfqStatus::DISEPAKATI)->count();
        $ditolakKedaluwarsa = $allContracts->whereIn('status', [RfqStatus::DITOLAK, RfqStatus::KEDALUWARSA, RfqStatus::DITARIK])->count();

        // Filter pencarian
        if ($search = trim((string) $request->input('q'))) {
            // Ekstrak kandidat ID numerik bila pengguna mengetik "RFQ-0001", "RFQ-1", "0001", dsb.
            $cleanNumeric = preg_replace('/^rfq[-_\s]*/i', '', $search);
            $extractedId = null;
            if ($cleanNumeric !== '' && is_numeric($cleanNumeric)) {
                $extractedId = (int) $cleanNumeric;
            } elseif (preg_match('/(\d+)$/', $search, $matches)) {
                $extractedId = (int) $matches[1];
            }

            $query->where(function ($q) use ($search, $extractedId) {
                if ($extractedId !== null) {
                    $q->where('id_rfq_contract', $extractedId);
                } else {
                    $q->where('id_rfq_contract', 'like', "%{$search}%");
                }

                $q->orWhereHas('listing', fn ($lq) => $lq->where('judul', 'like', "%{$search}%"));
            });
        }

        // Filter status
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Filter kategori
        if ($kategoriId = $request->input('kategori')) {
            $query->whereHas('listing', fn ($lq) => $lq->where('categories_id_categories', $kategoriId));
        }

        $rfqs = $query->orderByDesc('id_rfq_contract')->paginate(10)->withQueryString();
        $categories = Category::where('status', 'AKTIF')->orderBy('nama')->get();

        return view('rfq.index', compact(
            'rfqs',
            'totalRfq',
            'terbukaNegosiasi',
            'disepakati',
            'ditolakKedaluwarsa',
            'categories'
        ));
    }

    /**
     * Menampilkan formulir pembuatan RFQ baru untuk Buyer (Screen 08).
     */
    public function create(Request $request): View
    {
        $this->authorize('create', RfqContract::class);
        $user = $request->user();

        // Listing yang dapat ditawar: aktif, ada stok, dan bukan milik perusahaan sendiri
        $listings = Listing::with(['category', 'company'])
            ->where('status', 'PUBLISHED')
            ->where('companies_id_company', '!=', $user->id_company)
            ->where('stok_kg', '>', 0)
            ->orderBy('judul')
            ->get();

        $selectedListingId = (int) $request->input('listing_id', 0);
        $selectedListing = $selectedListingId > 0 ? $listings->firstWhere('id_listings', $selectedListingId) : null;

        return view('rfq.create', compact('listings', 'selectedListing'));
    }

    /**
     * Menyimpan pengajuan RFQ baru.
     */
    public function store(StoreRfqRequest $request): RedirectResponse
    {
        $rfq = $this->rfqService->createRfq($request->user(), $request->validated());

        return redirect()->route('rfq.show', $rfq)
            ->with('success', 'Pengajuan RFQ berhasil dikirim ke Seller.');
    }

    /**
     * Menampilkan detail RFQ dan utas kronologis negosiasi (Screen 09).
     */
    public function show(RfqContract $rfq): View
    {
        $this->authorize('view', $rfq);

        $rfq->load([
            'listing.category',
            'listing.company',
            'company',
            'offers.company',
        ]);

        return view('rfq.show', compact('rfq'));
    }

    /**
     * Menangani penawaran balik (counter-offer).
     */
    public function counter(CounterOfferRfqRequest $request, RfqContract $rfq): RedirectResponse
    {
        $this->rfqService->counterOffer($rfq, $request->user(), $request->validated());

        return redirect()->route('rfq.show', $rfq)
            ->with('success', 'Penawaran balik berhasil dikirim.');
    }

    /**
     * Menyetujui dan mengunci penawaran RFQ.
     */
    public function accept(Request $request, RfqContract $rfq): RedirectResponse
    {
        $this->authorize('respond', $rfq);

        $this->rfqService->acceptOffer($rfq, $request->user());

        return redirect()->route('rfq.show', $rfq)
            ->with('success', 'Penawaran berhasil disepakati! Kontrak harga dan volume telah terkunci.');
    }

    /**
     * Menolak penawaran RFQ.
     */
    public function reject(RejectRfqRequest $request, RfqContract $rfq): RedirectResponse
    {
        $this->rfqService->rejectOffer($rfq, $request->user(), $request->validated('alasan_penolakan'));

        return redirect()->route('rfq.show', $rfq)
            ->with('success', 'Penawaran RFQ telah ditolak.');
    }

    /**
     * Menarik pengajuan RFQ oleh Buyer.
     */
    public function withdraw(Request $request, RfqContract $rfq): RedirectResponse
    {
        $this->authorize('withdraw', $rfq);

        $this->rfqService->withdrawRfq($rfq, $request->user());

        return redirect()->route('rfq.show', $rfq)
            ->with('success', 'Pengajuan RFQ berhasil ditarik.');
    }
}

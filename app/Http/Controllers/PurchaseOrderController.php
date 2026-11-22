<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\RfqStatus;
use App\Http\Requests\CancelPurchaseOrderRequest;
use App\Http\Requests\RejectPurchaseOrderRequest;
use App\Http\Requests\StorePurchaseOrderRequest;
use App\Models\PurchaseOrder;
use App\Models\RfqContract;
use App\Services\Po\PoService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PurchaseOrderController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected PoService $poService
    ) {}

    /**
     * Menampilkan daftar Purchase Order (Screen 10).
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $this->authorize('viewAny', PurchaseOrder::class);

        $query = PurchaseOrder::with([
            'rfqContract.listing.category',
            'seller',
            'buyer',
        ]);

        if ($user && $user->role === 'SELLER') {
            $query->where('companies_id_seller', $user->id_company);
        } elseif ($user && $user->role === 'BUYER') {
            $query->where('companies_id_buyer', $user->id_company);
        }

        // Metrik KPI kartu (Screen 10)
        $kpiQuery = clone $query;
        $allOrders = $kpiQuery->get(['id_purchase_order', 'status']);
        $totalDraf = $allOrders->where('status', OrderStatus::DRAFT)->count();
        $totalMenunggu = $allOrders->where('status', OrderStatus::MENUNGGU_KONFIRMASI)->count();
        $totalDikonfirmasi = $allOrders->where('status', OrderStatus::DIKONFIRMASI)->count();
        $totalDitolakBatal = $allOrders->whereIn('status', [OrderStatus::DITOLAK, OrderStatus::DIBATALKAN])->count();

        // Pencarian (No. PO atau Judul Material)
        if ($search = trim((string) $request->input('q'))) {
            $cleanNumeric = preg_replace('/^po[-_\s]*/i', '', $search);
            $extractedId = null;
            if ($cleanNumeric !== '' && is_numeric($cleanNumeric)) {
                $extractedId = (int) $cleanNumeric;
            } elseif (preg_match('/(\d+)$/', $search, $matches)) {
                $extractedId = (int) $matches[1];
            }

            $query->where(function ($q) use ($search, $extractedId) {
                if ($extractedId !== null) {
                    $q->where('id_purchase_order', $extractedId);
                } else {
                    $q->where('id_purchase_order', 'like', "%{$search}%");
                }

                $q->orWhereHas('rfqContract.listing', function ($lq) use ($search) {
                    $lq->where('judul', 'like', "%{$search}%");
                });
            });
        }

        // Filter status
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $sortOrder = $request->input('sort') === 'asc' ? 'asc' : 'desc';
        $orders = $query->orderBy('id_purchase_order', $sortOrder)->paginate(10)->withQueryString();

        return view('po.index', compact(
            'orders',
            'totalDraf',
            'totalMenunggu',
            'totalDikonfirmasi',
            'totalDitolakBatal'
        ));
    }

    /**
     * Menampilkan formulir buat Purchase Order dari RFQ yang disepakati (Screen 11).
     */
    public function create(Request $request): View
    {
        $user = $request->user();
        $this->authorize('create', PurchaseOrder::class);

        // Ambil semua RFQ milik buyer yang berstatus DISEPAKATI dan belum pernah dibuatkan PO
        $availableRfqs = RfqContract::with(['listing.category', 'listing.company', 'latestOffer'])
            ->where('companies_id_company', $user?->id_company)
            ->where('status', RfqStatus::DISEPAKATI)
            ->whereDoesntHave('purchaseOrder')
            ->orderByDesc('id_rfq_contract')
            ->get();

        $selectedRfqId = $request->input('rfq_id') ?? $request->input('rfq');
        $selectedRfq = null;
        if ($selectedRfqId) {
            $selectedRfq = $availableRfqs->firstWhere('id_rfq_contract', (int) $selectedRfqId);
        }

        return view('po.create', compact('availableRfqs', 'selectedRfq'));
    }

    /**
     * Menyimpan Purchase Order baru.
     */
    public function store(StorePurchaseOrderRequest $request): RedirectResponse
    {
        $user = $request->user();
        if (! $user) {
            abort(401);
        }

        $po = $this->poService->createPo($user, $request->validated());

        $message = $request->validated('action') === 'send'
            ? 'Purchase Order berhasil dibuat dan dikirim ke Seller.'
            : 'Purchase Order berhasil disimpan sebagai draf.';

        return redirect()->route('po.show', $po)->with('status', $message);
    }

    /**
     * Menampilkan detail Purchase Order & alur konfirmasi (Screen 12).
     */
    public function show(PurchaseOrder $po): View
    {
        $this->authorize('view', $po);

        $po->load([
            'rfqContract.listing.category',
            'rfqContract.listing.company',
            'seller',
            'buyer',
            'statusHistory.user',
        ]);

        return view('po.show', compact('po'));
    }

    /**
     * Mengirim PO berstatus DRAFT ke Seller.
     */
    public function send(PurchaseOrder $po, Request $request): RedirectResponse
    {
        $user = $request->user();
        if (! $user) {
            abort(401);
        }

        $this->authorize('send', $po);

        if ($po->status !== OrderStatus::DRAFT) {
            return redirect()->route('po.show', $po)->with('status', 'Purchase Order sudah dikirim atau tidak dalam status Draf.');
        }

        $this->poService->sendToSeller($po, $user);

        return redirect()->route('po.show', $po)->with('status', 'Purchase Order berhasil dikirim ke Seller.');
    }

    /**
     * Mengonfirmasi PO oleh Seller dan mereservasi stok listing.
     */
    public function confirm(PurchaseOrder $po, Request $request): RedirectResponse
    {
        $user = $request->user();
        if (! $user) {
            abort(401);
        }

        $this->authorize('confirm', $po);

        if ($po->status === OrderStatus::DIKONFIRMASI) {
            return redirect()->route('po.show', $po)->with('status', 'Purchase Order sudah berhasil dikonfirmasi sebelumnya.');
        }

        if ($po->status !== OrderStatus::MENUNGGU_KONFIRMASI) {
            return redirect()->route('po.show', $po)->with('error', 'Purchase Order tidak dalam status Menunggu Konfirmasi.');
        }

        $this->poService->confirmPo($po, $user);

        return redirect()->route('po.show', $po)->with('status', 'Purchase Order berhasil dikonfirmasi. Stok listing telah direservasi.');
    }

    /**
     * Menolak PO oleh Seller dengan alasan wajib.
     */
    public function reject(RejectPurchaseOrderRequest $request, PurchaseOrder $po): RedirectResponse
    {
        $user = $request->user();
        if (! $user) {
            abort(401);
        }

        $this->authorize('reject', $po);

        if ($po->status === OrderStatus::DITOLAK) {
            return redirect()->route('po.show', $po)->with('status', 'Purchase Order sudah ditolak sebelumnya.');
        }

        $this->poService->rejectPo($po, $user, (string) $request->validated('alasan_penolakan'));

        return redirect()->route('po.show', $po)->with('status', 'Purchase Order telah ditolak.');
    }

    /**
     * Membatalkan PO oleh Buyer.
     */
    public function cancel(CancelPurchaseOrderRequest $request, PurchaseOrder $po): RedirectResponse
    {
        $user = $request->user();
        if (! $user) {
            abort(401);
        }

        $this->authorize('cancel', $po);

        if ($po->status === OrderStatus::DIBATALKAN) {
            return redirect()->route('po.show', $po)->with('status', 'Purchase Order sudah dibatalkan sebelumnya.');
        }

        $this->poService->cancelPo($po, $user, $request->validated('alasan'));

        return redirect()->route('po.show', $po)->with('status', 'Purchase Order telah dibatalkan.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePaymentRequest;
use App\Http\Requests\VerifyPaymentRequest;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\Payment\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService
    ) {}

    /**
     * Daftar invoice dan riwayat pembayaran bagi Buyer.
     */
    public function buyerInvoices(Request $request): View
    {
        $user = $request->user();
        if ($user?->role !== 'BUYER' || ! $user->id_company) {
            abort(403, 'Aksi ini hanya dapat diakses oleh Buyer.');
        }

        $invoices = Invoice::with(['purchaseOrder.seller', 'latestPayment'])
            ->whereHas('purchaseOrder', function ($query) use ($user) {
                $query->where('companies_id_buyer', $user->id_company);
            })
            ->orderByDesc('id_invoice')
            ->paginate(10);

        return view('payments.buyer.index', compact('invoices'));
    }

    /**
     * Tampilan form unggah bukti pembayaran bagi Buyer (Layar 16).
     */
    public function create(Invoice $invoice): View
    {
        Gate::authorize('create', [Payment::class, $invoice]);

        $invoice->load(['purchaseOrder.seller', 'latestPayment']);

        return view('payments.buyer.create', compact('invoice'));
    }

    /**
     * Proses penyimpanan berkas bukti transfer oleh Buyer.
     */
    public function store(StorePaymentRequest $request, Invoice $invoice): RedirectResponse
    {
        /** @var \Illuminate\Http\UploadedFile $buktiFile */
        $buktiFile = $request->file('bukti');

        $this->paymentService->createPayment(
            $invoice,
            $request->validated(),
            $buktiFile,
            $request->user()
        );

        return redirect()->route('buyer.invoices.index')
            ->with('success', 'Bukti transfer berhasil dikirim. Pembayaran kini menunggu verifikasi dari Seller.');
    }

    /**
     * Daftar invoice dan antrean verifikasi pembayaran bagi Seller.
     */
    public function sellerInvoices(Request $request): View
    {
        $user = $request->user();
        if ($user?->role !== 'SELLER' || ! $user->id_company) {
            abort(403, 'Aksi ini hanya dapat diakses oleh Seller.');
        }

        $invoices = Invoice::with(['purchaseOrder.buyer', 'latestPayment'])
            ->whereHas('purchaseOrder', function ($query) use ($user) {
                $query->where('companies_id_seller', $user->id_company);
            })
            ->orderByDesc('id_invoice')
            ->paginate(10);

        return view('payments.seller.index', compact('invoices'));
    }

    /**
     * Halaman review dan verifikasi bukti transfer oleh Seller (Layar 17).
     */
    public function showVerify(Payment $payment): View
    {
        Gate::authorize('verify', $payment);

        $payment->load(['invoice.purchaseOrder.buyer']);

        return view('payments.seller.verify', compact('payment'));
    }

    /**
     * Memproses verifikasi (VALID atau DITOLAK) oleh Seller.
     */
    public function processVerify(VerifyPaymentRequest $request, Payment $payment): RedirectResponse
    {
        $status = (string) $request->input('status');
        $alasanTolak = $request->input('alasan_tolak');

        $this->paymentService->verifyPayment($payment, $status, $alasanTolak, $request->user());

        $pesan = $status === 'VALID'
            ? 'Pembayaran berhasil diverifikasi. Status order kini DIBAYAR.'
            : 'Pembayaran telah ditolak. Alasan penolakan telah dikirimkan ke Buyer.';

        return redirect()->route('seller.invoices.index')->with('success', $pesan);
    }

    /**
     * Menampilkan berkas bukti transfer secara aman (terproteksi hak akses).
     */
    public function streamProof(Payment $payment): BinaryFileResponse
    {
        Gate::authorize('view', $payment);

        $path = $this->paymentService->getProofAbsolutePath($payment);
        if (! $path || ! file_exists($path)) {
            abort(404, 'Berkas bukti pembayaran tidak ditemukan.');
        }

        return response()->file($path);
    }
}

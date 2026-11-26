<?php

namespace App\Http\Controllers;

use App\Http\Requests\VerifyDocumentRequest;
use App\Models\DocumentVerification;
use App\Services\Inspector\DocumentVerificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class DocumentVerificationController extends Controller
{
    public function __construct(
        protected DocumentVerificationService $verificationService
    ) {}

    /**
     * Menampilkan daftar tugas verifikasi dokumen untuk Inspector.
     */
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', DocumentVerification::class);

        $status = strtoupper((string) $request->query('status', ''));
        $statusFilter = in_array($status, [
            DocumentVerification::STATUS_MENUNGGU,
            DocumentVerification::STATUS_SETUJU,
            DocumentVerification::STATUS_DITOLAK,
        ], true) ? $status : null;

        $verifications = $this->verificationService->getVerificationsForInspector(
            inspector: $request->user(),
            status: $statusFilter,
            perPage: 10
        );

        return view('inspector.verifications.index', [
            'verifications' => $verifications,
            'currentStatus' => $statusFilter,
        ]);
    }

    /**
     * Menampilkan halaman detail verifikasi dokumen, pratinjau dokumen, dan form checklist (Layar 34).
     */
    public function show(DocumentVerification $verification): View
    {
        Gate::authorize('view', $verification);

        $verification->load([
            'purchaseOrder.seller',
            'purchaseOrder.buyer',
            'purchaseOrder.rfqContract.listing',
            'inspector',
        ]);

        $documents = $this->verificationService->getDocumentsForPreview($verification);

        return view('inspector.verifications.show', [
            'verification' => $verification,
            'documents' => $documents,
            'checklistItems' => DocumentVerification::CHECKLIST_ITEMS,
        ]);
    }

    /**
     * Memproses keputusan persetujuan atau penolakan verifikasi dokumen.
     */
    public function verify(VerifyDocumentRequest $request, DocumentVerification $verification): RedirectResponse
    {
        $validated = $request->validated();

        $this->verificationService->verify(
            verification: $verification,
            data: $validated,
            inspector: $request->user()
        );

        $pesan = $validated['status'] === DocumentVerification::STATUS_SETUJU
            ? 'Dokumen legalitas dan bukti listing berhasil disetujui.'
            : 'Verifikasi dokumen ditolak dengan catatan yang telah disimpan.';

        return redirect()
            ->route('inspector.verifications.index')
            ->with('success', $pesan);
    }
}

<?php

namespace App\Services\Inspector;

use App\Models\CompanyDocument;
use App\Models\DocumentVerification;
use App\Models\Inspection;
use App\Models\ListingAttachment;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class DocumentVerificationService
{
    /**
     * Mengambil daftar penugasan verifikasi dokumen untuk Inspector tertentu.
     */
    public function getVerificationsForInspector(User $inspector, ?string $status = null, int $perPage = 10): LengthAwarePaginator
    {
        $query = DocumentVerification::query()
            ->with(['purchaseOrder.seller', 'purchaseOrder.buyer', 'inspector'])
            ->where('users_id_user', $inspector->id_user);

        if ($status) {
            $query->where('status', $status);
        }

        return $query->orderBy('id_document_verification', 'desc')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Mengumpulkan dokumen terkait (legalitas seller, izin usaha, bukti listing) untuk pratinjau.
     *
     * @return array{
     *     legalitas: Collection<int, CompanyDocument>,
     *     izin_usaha: Collection<int, CompanyDocument>,
     *     bukti_listing: Collection<int, ListingAttachment>
     * }
     */
    public function getDocumentsForPreview(DocumentVerification $verification): array
    {
        $po = $verification->purchaseOrder;
        $sellerId = $po->companies_id_seller;

        $legalitas = new Collection;
        $izinUsaha = new Collection;
        $buktiListing = new Collection;

        if ($sellerId) {
            $legalitas = CompanyDocument::query()
                ->where('id_company', $sellerId)
                // SURAT_NON_B3 diunggah lewat profil perusahaan (PBI-08) dan inti pemeriksaan PBI-17.
                ->whereIn('jenis', ['NIB', 'AKTA_PENDIRIAN', 'NPWP', 'SURAT_NON_B3', 'LAINNYA'])
                ->get();

            $izinUsaha = CompanyDocument::query()
                ->where('id_company', $sellerId)
                ->whereIn('jenis', ['AMDAL_UKL_UPL', 'IZIN_LINGKUNGAN', 'STANDAR_MUTU', 'SERTIFIKAT_LAIN'])
                ->get();
        }

        // Ambil bukti listing dari RFQ contract -> listing -> attachments
        $listingId = $po->rfqContract?->listings_id_listings;
        if ($listingId) {
            $buktiListing = ListingAttachment::query()
                ->where('listings_id_listings', $listingId)
                ->get();
        }

        return [
            'legalitas' => $legalitas,
            'izin_usaha' => $izinUsaha,
            'bukti_listing' => $buktiListing,
        ];
    }

    /**
     * Memproses verifikasi dokumen (Setuju atau Tolak).
     *
     * @param  array{status: string, checklist?: array<string, bool>|null, alasan?: string|null}  $data
     */
    public function verify(DocumentVerification $verification, array $data, User $inspector): DocumentVerification
    {
        return DB::transaction(function () use ($verification, $data, $inspector) {
            $verification->update([
                'status' => $data['status'],
                'checklist' => $data['checklist'] ?? $verification->checklist,
                'alasan' => $data['status'] === DocumentVerification::STATUS_DITOLAK ? ($data['alasan'] ?? null) : null,
                'users_id_user' => $inspector->id_user,
            ]);

            // Bila ada penugasan inspeksi terkait, catat progres
            Inspection::query()
                ->where('purchase_orders_id_purchase_order', $verification->purchase_orders_id_purchase_order)
                ->where('users_id_user', $inspector->id_user)
                ->where('status', 'DITUGASKAN')
                ->update(['status' => 'DIPROSES']);

            // Status order tidak berubah di sini: hasil SETUJU menjadi syarat pengiriman (PBI-11),
            // sedangkan transisi Lolos/Gagal Inspeksi dilakukan laporan mutu (PBI-19).

            return $verification->fresh();
        });
    }
}

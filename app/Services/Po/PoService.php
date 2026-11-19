<?php

namespace App\Services\Po;

use App\Enums\OrderStatus;
use App\Enums\RfqStatus;
use App\Models\Category;
use App\Models\Listing;
use App\Models\OrderStatusHistory;
use App\Models\PurchaseOrder;
use App\Models\RfqContract;
use App\Models\User;
use App\Services\OrderStateMachine;
use App\Support\Format;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PoService
{
    /**
     * Buat Purchase Order dari kontrak RFQ yang telah berstatus DISEPAKATI.
     *
     * @param  array{rfq_contracts_id_rfq_contract: int, jarak_km?: float|numeric-string|null, action?: string}  $data
     *
     * @throws ValidationException
     */
    public function createPo(User $buyer, array $data): PurchaseOrder
    {
        if (! $buyer->id_company) {
            throw ValidationException::withMessages([
                'user' => 'Pengguna tidak terhubung dengan perusahaan pembeli.',
            ]);
        }

        $rfqId = (int) $data['rfq_contracts_id_rfq_contract'];

        /** @var RfqContract|null $rfq */
        $rfq = RfqContract::with(['listing.category', 'listing.company', 'latestOffer'])
            ->find($rfqId);

        if (! $rfq) {
            throw ValidationException::withMessages([
                'rfq_contracts_id_rfq_contract' => 'Kontrak RFQ tidak ditemukan.',
            ]);
        }

        if ($rfq->companies_id_company !== $buyer->id_company) {
            throw ValidationException::withMessages([
                'rfq_contracts_id_rfq_contract' => 'Anda tidak memiliki hak untuk membuat PO dari kontrak RFQ ini.',
            ]);
        }

        $rfqStatusValue = $rfq->status instanceof RfqStatus ? $rfq->status->value : (string) $rfq->status;
        if ($rfqStatusValue !== RfqStatus::DISEPAKATI->value) {
            throw ValidationException::withMessages([
                'rfq_contracts_id_rfq_contract' => 'Purchase Order hanya dapat dibuat dari kontrak RFQ yang berstatus Disepakati.',
            ]);
        }

        if (PurchaseOrder::where('rfq_contracts_id_rfq_contract', $rfq->id_rfq_contract)->exists()) {
            throw ValidationException::withMessages([
                'rfq_contracts_id_rfq_contract' => 'Purchase Order untuk kontrak RFQ ini sudah pernah dibuat sebelumnya.',
            ]);
        }

        $listing = $rfq->listing;
        if (! $listing) {
            throw ValidationException::withMessages([
                'listing' => 'Listing material terkait kontrak RFQ tidak ditemukan.',
            ]);
        }

        $category = $listing->category;
        if ($category instanceof Category && $category->is_b3_prohibited) {
            throw ValidationException::withMessages([
                'category' => 'Material termasuk kategori B3 terlarang dan tidak dapat ditransaksikan.',
            ]);
        }

        $latestOffer = $rfq->latestOffer;
        $volumeKg = $rfq->volume_kg_disepakati ?? ($latestOffer ? $latestOffer->volume_kg : 0);
        $hargaPerKg = $rfq->harga_disepakati ?? ($latestOffer ? $latestOffer->harga_per_kg : 0);

        $toleransiPersen = $category instanceof Category ? ($category->toleransi_susut_persen ?? 0) : 0;
        $kadarAirMaks = $category instanceof Category ? $category->kadar_air_maks_persen : null;
        $toleransiKadarAirPoin = $category instanceof Category ? $category->toleransi_kadar_air_poin : null;

        $jarakKm = isset($data['jarak_km']) ? (float) $data['jarak_km'] : null;
        $action = $data['action'] ?? 'draft';

        return DB::transaction(function () use (
            $rfq,
            $listing,
            $buyer,
            $volumeKg,
            $hargaPerKg,
            $toleransiPersen,
            $kadarAirMaks,
            $toleransiKadarAirPoin,
            $jarakKm,
            $action
        ) {
            $po = PurchaseOrder::create([
                'volume_kg' => (string) $volumeKg,
                'harga_per_kg' => (string) $hargaPerKg,
                'toleransi_persen' => (string) $toleransiPersen,
                'kadar_air_maks_persen' => $kadarAirMaks !== null ? (string) $kadarAirMaks : null,
                'toleransi_kadar_air_poin' => $toleransiKadarAirPoin !== null ? (string) $toleransiKadarAirPoin : null,
                'jarak_km' => $jarakKm !== null ? (string) $jarakKm : null,
                'status' => OrderStatus::DRAFT,
                'rfq_contracts_id_rfq_contract' => $rfq->id_rfq_contract,
                'companies_id_seller' => $listing->companies_id_company,
                'companies_id_buyer' => (int) $buyer->id_company,
            ]);

            OrderStatusHistory::create([
                'dari' => null,
                'ke' => OrderStatus::DRAFT->value,
                'alasan' => 'PO dibuat oleh Buyer dari RFQ-'.str_pad((string) $rfq->id_rfq_contract, 4, '0', STR_PAD_LEFT),
                'waktu' => now(),
                'purchase_orders_id_purchase_order' => $po->id_purchase_order,
                'users_id_user' => $buyer->id_user,
            ]);

            if ($action === 'send') {
                OrderStateMachine::transition(
                    $po,
                    OrderStatus::MENUNGGU_KONFIRMASI,
                    $buyer,
                    'PO dikirim ke Seller untuk konfirmasi'
                );
            }

            return $po;
        });
    }

    /**
     * Kirim PO berstatus DRAFT ke Seller.
     *
     * @throws ValidationException
     */
    public function sendToSeller(PurchaseOrder $po, User $buyer): PurchaseOrder
    {
        if ($po->companies_id_buyer !== $buyer->id_company) {
            throw ValidationException::withMessages([
                'authorization' => 'Anda tidak memiliki hak akses untuk mengirim PO ini.',
            ]);
        }

        if ($po->status !== OrderStatus::DRAFT) {
            throw ValidationException::withMessages([
                'status' => 'Hanya PO dengan status Draf yang dapat dikirim ke Seller.',
            ]);
        }

        return OrderStateMachine::transition(
            $po,
            OrderStatus::MENUNGGU_KONFIRMASI,
            $buyer,
            'PO dikirim ke Seller untuk konfirmasi'
        );
    }

    /**
     * Konfirmasi PO oleh Seller dengan reservasi stok listing secara atomik (lockForUpdate).
     *
     * @throws ValidationException
     */
    public function confirmPo(PurchaseOrder $po, User $seller): PurchaseOrder
    {
        if ($po->companies_id_seller !== $seller->id_company) {
            throw ValidationException::withMessages([
                'authorization' => 'Anda tidak memiliki hak akses untuk mengonfirmasi PO ini.',
            ]);
        }

        if ($po->status !== OrderStatus::MENUNGGU_KONFIRMASI) {
            throw ValidationException::withMessages([
                'status' => 'Hanya PO dengan status Menunggu Konfirmasi yang dapat dikonfirmasi.',
            ]);
        }

        return DB::transaction(function () use ($po, $seller) {
            $po->loadMissing('rfqContract.listing');

            $listingId = $po->rfqContract?->listings_id_listings;
            if (! $listingId) {
                throw ValidationException::withMessages([
                    'listing' => 'Listing material terkait PO tidak ditemukan.',
                ]);
            }

            /** @var Listing|null $listing */
            $listing = Listing::where('id_listings', $listingId)->lockForUpdate()->first();

            if (! $listing) {
                throw ValidationException::withMessages([
                    'listing' => 'Listing material tidak ditemukan dalam sistem.',
                ]);
            }

            $stokTersedia = (float) $listing->stok_kg;
            $volumeDibutuhkan = (float) $po->volume_kg;

            if ($stokTersedia < $volumeDibutuhkan) {
                throw ValidationException::withMessages([
                    'stok' => 'Stok listing saat ini ('.Format::kg($stokTersedia).') tidak mencukupi untuk mengonfirmasi volume PO ('.Format::kg($volumeDibutuhkan).').',
                ]);
            }

            // Kurangi stok listing untuk reservasi pesanan
            $listing->stok_kg = (float) round($stokTersedia - $volumeDibutuhkan, 2);
            $listing->save();

            // Ubah status PO lewat State Machine
            OrderStateMachine::transition(
                $po,
                OrderStatus::DIKONFIRMASI,
                $seller,
                'PO dikonfirmasi oleh Seller. Stok sebesar '.Format::kg($volumeDibutuhkan).' berhasil direservasi.'
            );

            return $po;
        });
    }

    /**
     * Tolak PO oleh Seller dengan alasan penolakan wajib.
     *
     * @throws ValidationException
     */
    public function rejectPo(PurchaseOrder $po, User $seller, string $reason): PurchaseOrder
    {
        if ($po->companies_id_seller !== $seller->id_company) {
            throw ValidationException::withMessages([
                'authorization' => 'Anda tidak memiliki hak akses untuk menolak PO ini.',
            ]);
        }

        if ($po->status !== OrderStatus::MENUNGGU_KONFIRMASI) {
            throw ValidationException::withMessages([
                'status' => 'Hanya PO dengan status Menunggu Konfirmasi yang dapat ditolak.',
            ]);
        }

        $cleanReason = trim($reason);
        if ($cleanReason === '') {
            throw ValidationException::withMessages([
                'alasan_penolakan' => 'Alasan penolakan PO wajib diisi.',
            ]);
        }

        return OrderStateMachine::transition(
            $po,
            OrderStatus::DITOLAK,
            $seller,
            $cleanReason
        );
    }

    /**
     * Batalkan PO oleh Buyer (hanya diizinkan jika status DRAFT atau MENUNGGU_KONFIRMASI).
     *
     * @throws ValidationException
     */
    public function cancelPo(PurchaseOrder $po, User $buyer, ?string $reason = null): PurchaseOrder
    {
        if ($po->companies_id_buyer !== $buyer->id_company) {
            throw ValidationException::withMessages([
                'authorization' => 'Anda tidak memiliki hak akses untuk membatalkan PO ini.',
            ]);
        }

        if (! $po->canBeCancelled()) {
            throw ValidationException::withMessages([
                'status' => 'PO tidak dapat dibatalkan pada status saat ini.',
            ]);
        }

        $alasan = $reason ? trim($reason) : 'PO dibatalkan oleh Buyer sebelum konfirmasi.';

        return OrderStateMachine::transition(
            $po,
            OrderStatus::DIBATALKAN,
            $buyer,
            $alasan
        );
    }
}

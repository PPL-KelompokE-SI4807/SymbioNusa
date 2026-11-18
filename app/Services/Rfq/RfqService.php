<?php

namespace App\Services\Rfq;

use App\Enums\RfqStatus;
use App\Models\Listing;
use App\Models\RfqContract;
use App\Models\RfqOffer;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RfqService
{
    /**
     * Membuat kontrak RFQ baru dan mencatat penawaran pertama dari Buyer.
     *
     * @param  array{listings_id_listings: int, volume_kg: float|int, harga_per_kg: float|int, tenggat_hari?: int, catatan?: string|null, demand_requests_id_demand_request?: int|null}  $data
     */
    public function createRfq(User $buyer, array $data): RfqContract
    {
        return DB::transaction(function () use ($buyer, $data) {
            /** @var Listing $listing */
            $listing = Listing::findOrFail($data['listings_id_listings']);

            if (! $listing->isPublished()) {
                throw ValidationException::withMessages([
                    'listings_id_listings' => 'Listing material belum dipublikasikan.',
                ]);
            }

            if ((float) $data['volume_kg'] > (float) $listing->stok_kg) {
                throw ValidationException::withMessages([
                    'volume_kg' => 'Volume yang diajukan melebihi stok tersedia.',
                ]);
            }

            $tenggatHari = isset($data['tenggat_hari']) ? (int) $data['tenggat_hari'] : 3;
            $tenggat = Carbon::now()->addDays($tenggatHari);

            $rfq = RfqContract::create([
                'volume_kg_disepakati' => null,
                'harga_disepakati' => null,
                'tenggat' => $tenggat,
                'status' => RfqStatus::TERBUKA,
                'listings_id_listings' => $listing->id_listings,
                'companies_id_company' => $buyer->id_company,
                'demand_requests_id_demand_request' => $data['demand_requests_id_demand_request'] ?? null,
            ]);

            RfqOffer::create([
                'volume_kg' => $data['volume_kg'],
                'harga_per_kg' => $data['harga_per_kg'],
                'catatan' => $data['catatan'] ?? null,
                'urutan' => 1,
                'rfq_contracts_id_rfq_contract' => $rfq->id_rfq_contract,
                'companies_id_company' => $buyer->id_company,
            ]);

            return $rfq;
        });
    }

    /**
     * Mengajukan penawaran balik (counter-offer) pada utas negosiasi.
     * Maksimal 5 putaran.
     *
     * @param  array{volume_kg: float|int, harga_per_kg: float|int, catatan?: string|null}  $data
     */
    public function counterOffer(RfqContract $rfq, User $responder, array $data): RfqOffer
    {
        return DB::transaction(function () use ($rfq, $responder, $data) {
            if (! $rfq->isOpenOrNegotiating() || $rfq->isExpired()) {
                throw ValidationException::withMessages([
                    'rfq' => 'Kontrak RFQ ini sudah tidak dalam status aktif atau telah kedaluwarsa.',
                ]);
            }

            $latestOffer = $rfq->latestOffer;
            if ($latestOffer instanceof RfqOffer && $latestOffer->companies_id_company === $responder->id_company) {
                throw ValidationException::withMessages([
                    'rfq' => 'Anda harus menunggu tanggapan dari pihak lawan sebelum mengajukan penawaran baru.',
                ]);
            }

            $currentUrutan = $latestOffer instanceof RfqOffer ? $latestOffer->urutan : 0;
            if ($currentUrutan >= 5) {
                throw ValidationException::withMessages([
                    'rfq' => 'Batas maksimal negosiasi adalah 5 putaran penawaran. Anda hanya dapat menerima atau menolak tawaran.',
                ]);
            }

            $listing = $rfq->listing;
            if ($listing instanceof Listing && (float) $data['volume_kg'] > (float) $listing->stok_kg) {
                throw ValidationException::withMessages([
                    'volume_kg' => 'Volume penawaran balik melebihi stok material saat ini.',
                ]);
            }

            $nextUrutan = $currentUrutan + 1;

            $rfq->status = RfqStatus::NEGOSIASI;
            $rfq->save();

            return RfqOffer::create([
                'volume_kg' => $data['volume_kg'],
                'harga_per_kg' => $data['harga_per_kg'],
                'catatan' => $data['catatan'] ?? null,
                'urutan' => $nextUrutan,
                'rfq_contracts_id_rfq_contract' => $rfq->id_rfq_contract,
                'companies_id_company' => $responder->id_company,
            ]);
        });
    }

    /**
     * Menerima penawaran terakhir dan mengunci kesepakatan harga & volume.
     */
    public function acceptOffer(RfqContract $rfq, User $responder): RfqContract
    {
        return DB::transaction(function () use ($rfq, $responder) {
            if (! $rfq->isOpenOrNegotiating() || $rfq->isExpired()) {
                throw ValidationException::withMessages([
                    'rfq' => 'Kontrak RFQ ini tidak dapat disepakati karena sudah ditutup atau kedaluwarsa.',
                ]);
            }

            $latestOffer = $rfq->latestOffer;
            if (! $latestOffer instanceof RfqOffer) {
                throw ValidationException::withMessages([
                    'rfq' => 'Tidak ada tawaran aktif untuk diterima.',
                ]);
            }

            if ($latestOffer->companies_id_company === $responder->id_company) {
                throw ValidationException::withMessages([
                    'rfq' => 'Anda tidak dapat menerima penawaran yang diajukan oleh perusahaan Anda sendiri.',
                ]);
            }

            $rfq->volume_kg_disepakati = $latestOffer->volume_kg;
            $rfq->harga_disepakati = $latestOffer->harga_per_kg;
            $rfq->status = RfqStatus::DISEPAKATI;
            $rfq->save();

            return $rfq;
        });
    }

    /**
     * Menolak penawaran RFQ dengan mencatat alasan penolakan.
     */
    public function rejectOffer(RfqContract $rfq, User $responder, string $alasan): RfqContract
    {
        return DB::transaction(function () use ($rfq, $responder, $alasan) {
            if (! $rfq->isOpenOrNegotiating() || $rfq->isExpired()) {
                throw ValidationException::withMessages([
                    'rfq' => 'Kontrak RFQ ini sudah tidak aktif.',
                ]);
            }

            $latestOffer = $rfq->latestOffer;
            if ($latestOffer instanceof RfqOffer && $latestOffer->companies_id_company === $responder->id_company) {
                throw ValidationException::withMessages([
                    'rfq' => 'Anda tidak dapat menolak tawaran dari pihak Anda sendiri.',
                ]);
            }

            $rfq->status = RfqStatus::DITOLAK;
            $rfq->save();

            // Catat riwayat penolakan sebagai offer penutup dengan urutan berikutnya jika memungkinkan
            $nextUrutan = ($latestOffer instanceof RfqOffer ? $latestOffer->urutan : 0) + 1;
            RfqOffer::create([
                'volume_kg' => $latestOffer instanceof RfqOffer ? $latestOffer->volume_kg : 0.0,
                'harga_per_kg' => $latestOffer instanceof RfqOffer ? $latestOffer->harga_per_kg : 0.0,
                'catatan' => '[DITOLAK] '.$alasan,
                'urutan' => $nextUrutan,
                'rfq_contracts_id_rfq_contract' => $rfq->id_rfq_contract,
                'companies_id_company' => $responder->id_company,
            ]);

            return $rfq;
        });
    }

    /**
     * Menarik pengajuan RFQ oleh Buyer.
     */
    public function withdrawRfq(RfqContract $rfq, User $buyer): RfqContract
    {
        return DB::transaction(function () use ($rfq, $buyer) {
            if ($rfq->companies_id_company !== $buyer->id_company) {
                throw ValidationException::withMessages([
                    'rfq' => 'Hanya Buyer pembuat RFQ yang dapat menarik pengajuan.',
                ]);
            }

            if (! $rfq->isOpenOrNegotiating() || $rfq->isExpired()) {
                throw ValidationException::withMessages([
                    'rfq' => 'Hanya RFQ berstatus terbuka atau negosiasi yang dapat ditarik.',
                ]);
            }

            $rfq->status = RfqStatus::DITARIK;
            $rfq->save();

            return $rfq;
        });
    }

    /**
     * Memperbarui status RFQ yang telah melewati tenggat menjadi KEDALUWARSA.
     */
    public function expireOverdue(): int
    {
        return DB::table('rfq_contracts')
            ->whereIn('status', [RfqStatus::TERBUKA->value, RfqStatus::NEGOSIASI->value])
            ->whereNotNull('tenggat')
            ->where('tenggat', '<', Carbon::now())
            ->update(['status' => RfqStatus::KEDALUWARSA->value]);
    }
}

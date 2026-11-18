<?php

namespace App\Policies;

use App\Models\Listing;
use App\Models\RfqContract;
use App\Models\RfqOffer;
use App\Models\User;

class RfqPolicy
{
    /**
     * Memeriksa apakah pengguna berhak melihat daftar RFQ miliknya.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['BUYER', 'SELLER', 'ADMIN'], true) && $user->id_company !== null;
    }

    /**
     * Memeriksa apakah pengguna berhak melihat detail kontrak RFQ tertentu.
     * Hanya Buyer pembuat RFQ, Seller pemilik listing, atau Admin.
     */
    public function view(User $user, RfqContract $rfq): bool
    {
        if ($user->role === 'ADMIN') {
            return true;
        }

        if (! $user->id_company) {
            return false;
        }

        // Pihak Buyer
        if ($rfq->companies_id_company === $user->id_company) {
            return true;
        }

        // Pihak Seller (pemilik listing)
        $listing = $rfq->listing;
        $sellerCompanyId = $listing instanceof Listing ? $listing->companies_id_company : null;

        return $sellerCompanyId !== null && $sellerCompanyId === $user->id_company;
    }

    /**
     * Memeriksa apakah pengguna berhak membuat/mengajukan RFQ baru.
     * Hanya Buyer yang terverifikasi dan aktif.
     */
    public function create(User $user): bool
    {
        return $user->role === 'BUYER'
            && $user->id_company !== null
            && $user->status === 'AKTIF';
    }

    /**
     * Memeriksa apakah pengguna adalah pihak yang berhak merespons (giliran menanggapi) tawaran terakhir.
     */
    public function respond(User $user, RfqContract $rfq): bool
    {
        if (! $this->view($user, $rfq)) {
            return false;
        }

        if (! $rfq->isOpenOrNegotiating() || $rfq->isExpired()) {
            return false;
        }

        $latestOffer = $rfq->latestOffer;
        if (! $latestOffer instanceof RfqOffer) {
            return false;
        }

        // Tidak boleh menanggapi penawaran dari perusahaannya sendiri (harus bergantian)
        return $latestOffer->companies_id_company !== $user->id_company;
    }

    /**
     * Memeriksa apakah pengguna berhak mengajukan penawaran balik (counter-offer).
     */
    public function counter(User $user, RfqContract $rfq): bool
    {
        return $this->respond($user, $rfq) && $rfq->canCounter();
    }

    /**
     * Memeriksa apakah pengguna berhak menarik (withdraw) RFQ.
     * Hanya Buyer pembuat RFQ saat status masih terbuka/negosiasi dan belum kedaluwarsa.
     */
    public function withdraw(User $user, RfqContract $rfq): bool
    {
        if ($user->id_company !== $rfq->companies_id_company) {
            return false;
        }

        return $rfq->isOpenOrNegotiating() && ! $rfq->isExpired();
    }
}

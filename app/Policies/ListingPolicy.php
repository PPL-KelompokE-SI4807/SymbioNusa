<?php

namespace App\Policies;

use App\Models\Listing;
use App\Models\User;

class ListingPolicy
{
    /**
     * Menentukan apakah user dapat melihat daftar listing miliknya di portal Seller.
     */
    public function viewAny(User $user): bool
    {
        return $user->role === 'SELLER'
            && $user->company !== null
            && $user->company->isVerified();
    }

    /**
     * Menentukan apakah user dapat melihat detail listing tertentu.
     */
    public function view(User $user, Listing $listing): bool
    {
        if ($listing->status === 'PUBLISHED') {
            return true;
        }

        return $user->role === 'SELLER'
            && (int) $user->id_company === (int) $listing->companies_id_company;
    }

    /**
     * Menentukan apakah user dapat membuat listing baru.
     */
    public function create(User $user): bool
    {
        return $user->role === 'SELLER'
            && $user->company !== null
            && $user->company->isVerified();
    }

    /**
     * Menentukan apakah user dapat mengedit/mengubah listing.
     */
    public function update(User $user, Listing $listing): bool
    {
        return $user->role === 'SELLER'
            && (int) $user->id_company === (int) $listing->companies_id_company
            && $user->company !== null
            && $user->company->isVerified();
    }

    /**
     * Menentukan apakah user dapat mengarsipkan listing.
     */
    public function delete(User $user, Listing $listing): bool
    {
        return $this->update($user, $listing);
    }
}

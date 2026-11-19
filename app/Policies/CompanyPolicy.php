<?php

namespace App\Policies;

use App\Models\Company;
use App\Models\User;

class CompanyPolicy
{
    public function view(User $user, Company $company): bool
    {
        return strtoupper($user->role ?? '') === 'ADMIN'
            || (int) $user->id_company === (int) $company->id_company;
    }

    public function update(User $user, Company $company): bool
    {
        return (int) $user->id_company === (int) $company->id_company;
    }

    public function verify(User $user): bool
    {
        return strtoupper($user->role ?? '') === 'ADMIN';
    }
}

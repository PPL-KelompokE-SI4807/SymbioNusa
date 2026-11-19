<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCompanyVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (in_array(strtoupper($user->role ?? ''), ['ADMIN', 'INSPECTOR'], true)) {
            return $next($request);
        }

        $company = $user->company;

        if (! $company || ! $company->isVerified()) {
            return redirect()
                ->route('companies.edit')
                ->with('error', 'Akses dibatasi: lengkapi profil dan pastikan legalitas perusahaan Anda berstatus TERVERIFIKASI terlebih dahulu.');
        }

        return $next($request);
    }
}

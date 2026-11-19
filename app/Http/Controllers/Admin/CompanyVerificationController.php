<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\CompanyDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CompanyVerificationController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('verify', Company::class);

        $status = strtoupper((string) $request->query('status', ''));

        $companies = Company::withCount('documents')
            ->when(in_array($status, [Company::STATUS_PENDING, Company::STATUS_VERIFIED, Company::STATUS_REJECTED], true), function ($q) use ($status) {
                $q->where('status_verifikasi', $status);
            })
            ->orderByRaw("FIELD(status_verifikasi, 'PENDING', 'DITOLAK', 'TERVERIFIKASI')")
            ->orderByDesc('id_company')
            ->paginate(10)
            ->withQueryString();

        return view('admin.companies.index', compact('companies', 'status'));
    }

    public function show(Company $company): View
    {
        Gate::authorize('verify', Company::class);

        $company->documents()
            ->whereNotNull('berlaku_sampai')
            ->whereDate('berlaku_sampai', '<', now()->toDateString())
            ->update(['status' => CompanyDocument::STATUS_EXPIRED]);

        $company->load(['documents', 'users']);

        return view('admin.companies.show', compact('company'));
    }

    public function approve(Company $company): RedirectResponse
    {
        Gate::authorize('verify', Company::class);

        if ($company->documents()->count() === 0) {
            return back()->with('error', 'Perusahaan belum mengunggah dokumen legalitas apa pun.');
        }

        $hasExpired = $company->documents()
            ->whereNotNull('berlaku_sampai')
            ->whereDate('berlaku_sampai', '<', now()->toDateString())
            ->exists();

        if ($hasExpired) {
            $company->documents()
                ->whereNotNull('berlaku_sampai')
                ->whereDate('berlaku_sampai', '<', now()->toDateString())
                ->update(['status' => CompanyDocument::STATUS_EXPIRED]);

            return back()->with('error', 'Terdapat dokumen yang sudah kedaluwarsa. Perusahaan harus memperbarui dokumen sebelum disetujui.');
        }

        $company->update(['status_verifikasi' => Company::STATUS_VERIFIED]);
        $company->documents()->update(['status' => CompanyDocument::STATUS_VALID]);

        return redirect()
            ->route('admin.companies.index')
            ->with('success', "Perusahaan {$company->nama} berhasil diverifikasi.");
    }

    public function reject(Request $request, Company $company): RedirectResponse
    {
        Gate::authorize('verify', Company::class);

        $validated = $request->validate([
            'alasan_penolakan' => ['required', 'string', 'max:500'],
        ]);

        $company->update(['status_verifikasi' => Company::STATUS_REJECTED]);

        return redirect()
            ->route('admin.companies.index')
            ->with('success', "Verifikasi {$company->nama} ditolak. Alasan: {$validated['alasan_penolakan']}");
    }
}

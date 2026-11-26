<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateCompanyProfileRequest;
use App\Models\Company;
use App\Services\Company\CompanyVerificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CompanyController extends Controller
{
    public function edit(Request $request, CompanyVerificationService $verification): View
    {
        $user = $request->user();
        $company = $user->company?->load('documents');
        $documentTypes = CompanyVerificationService::DOCUMENT_TYPES;
        $missingDocuments = $company ? $verification->missingRequiredDocuments($company) : [];

        return view('companies.edit', compact('user', 'company', 'documentTypes', 'missingDocuments'));
    }

    public function update(UpdateCompanyProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        DB::transaction(function () use ($user, $validated) {
            $company = $user->company;

            $payload = [
                'nama' => $validated['nama'],
                'tipe' => $validated['tipe'],
                'nib' => $validated['nib'],
                'alamat' => $validated['alamat'],
                'kontak' => $validated['kontak'],
                'lat' => $validated['lat'] ?? null,
                'lng' => $validated['lng'] ?? null,
                'tos_versi' => 'v1.0-2026',
                'tos_disetujui_pada' => now(),
            ];

            if (! $company) {
                $payload['status_verifikasi'] = Company::STATUS_PENDING;
                $company = Company::create($payload);
                $user->forceFill(['id_company' => $company->id_company])->save();
            } else {
                Gate::authorize('update', $company);

                if ($company->nib !== $validated['nib'] && $company->isVerified()) {
                    $payload['status_verifikasi'] = Company::STATUS_PENDING;
                }

                $company->update($payload);
            }
        });

        return redirect()
            ->route('companies.edit')
            ->with('success', 'Profil perusahaan dan persetujuan Syarat & Ketentuan berhasil disimpan.');
    }

    public function switchRole(Request $request): RedirectResponse
    {
        $user = $request->user();
        $company = $user->company;

        if (! $company || $company->tipe !== Company::TYPE_BOTH) {
            return back()->with('error', 'Pengalih peran hanya tersedia untuk perusahaan dengan tipe mitra KEDUANYA.');
        }

        $targetRole = strtoupper((string) $request->input('role'));
        if (! in_array($targetRole, [Company::TYPE_SELLER, Company::TYPE_BUYER], true)) {
            return back()->with('error', 'Peran tujuan tidak valid.');
        }

        session(['active_role' => $targetRole]);

        return redirect()
            ->route('dashboard')
            ->with('success', "Mode kerja berhasil dialihkan ke {$targetRole}.");
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCompanyDocumentRequest;
use App\Models\CompanyDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class CompanyDocumentController extends Controller
{
    public function store(StoreCompanyDocumentRequest $request): RedirectResponse
    {
        $company = $request->user()->company;
        Gate::authorize('update', $company);

        $path = $request->file('dokumen')->store(
            "company-documents/{$company->id_company}",
            'public'
        );

        $company->documents()->create([
            'jenis' => $request->validated('jenis'),
            'file_url' => $path,
            'berlaku_sampai' => $request->validated('berlaku_sampai'),
            'status' => CompanyDocument::STATUS_PENDING,
        ]);

        return redirect()
            ->route('companies.edit')
            ->with('success', 'Dokumen legalitas berhasil diunggah dan menunggu peninjauan Admin.');
    }

    public function destroy(Request $request, CompanyDocument $document): RedirectResponse
    {
        $company = $request->user()->company;

        if (! $company || (int) $document->id_company !== (int) $company->id_company) {
            abort(403);
        }

        if ($document->file_url && Storage::disk('public')->exists($document->file_url)) {
            Storage::disk('public')->delete($document->file_url);
        }

        $document->delete();

        return redirect()
            ->route('companies.edit')
            ->with('success', 'Dokumen legalitas berhasil dihapus.');
    }
}

<?php

namespace App\Services\Company;

use App\Models\Company;
use App\Models\CompanyDocument;
use Illuminate\Support\Facades\DB;

/**
 * PBI-08 (SYM-12): aturan dokumen wajib per tipe mitra dan tinjauan ulang
 * otomatis saat dokumen wajib kedaluwarsa.
 */
class CompanyVerificationService
{
    public const TIMEZONE = 'Asia/Jakarta';

    /**
     * Label tampilan untuk setiap jenis dokumen perusahaan.
     *
     * @var array<string, string>
     */
    public const DOCUMENT_TYPES = [
        'NIB' => 'NIB / OSS',
        'NPWP' => 'NPWP Perusahaan',
        'AKTA_PENDIRIAN' => 'Akta Pendirian',
        'IZIN_LINGKUNGAN' => 'Izin Lingkungan / UKL-UPL',
        'AMDAL_UKL_UPL' => 'Dokumen AMDAL / UKL-UPL',
        'SURAT_NON_B3' => 'Surat Pernyataan Non-B3',
        'DOKUMEN_PEMANFAATAN' => 'Dokumen Kegiatan Pemanfaatan',
    ];

    /**
     * Kelompok dokumen wajib sesuai tipe mitra. Setiap kelompok terpenuhi bila
     * salah satu jenis di dalamnya ada dan masih berlaku.
     *
     * @return array<string, list<string>> label kelompok => jenis yang diterima
     */
    public function requiredDocumentGroups(Company $company): array
    {
        $groups = [
            'NIB / OSS' => ['NIB'],
            'Dokumen lingkungan (Izin Lingkungan atau AMDAL/UKL-UPL)' => ['IZIN_LINGKUNGAN', 'AMDAL_UKL_UPL'],
        ];

        if ($company->canActAsSeller()) {
            $groups['Surat Pernyataan Non-B3'] = ['SURAT_NON_B3'];
        }

        if ($company->canActAsBuyer()) {
            $groups['Dokumen Kegiatan Pemanfaatan'] = ['DOKUMEN_PEMANFAATAN'];
        }

        return $groups;
    }

    /**
     * Kelompok dokumen wajib yang belum ada atau sudah kedaluwarsa.
     *
     * @return list<string>
     */
    public function missingRequiredDocuments(Company $company): array
    {
        $today = $this->today();

        $activeTypes = $company->documents()
            ->where('status', '!=', CompanyDocument::STATUS_EXPIRED)
            ->where(function ($q) use ($today) {
                $q->whereNull('berlaku_sampai')->orWhereDate('berlaku_sampai', '>=', $today);
            })
            ->pluck('jenis')
            ->all();

        $missing = [];
        foreach ($this->requiredDocumentGroups($company) as $label => $types) {
            if (array_intersect($types, $activeTypes) === []) {
                $missing[] = $label;
            }
        }

        return $missing;
    }

    /**
     * Tandai dokumen yang masa berlakunya sudah lewat sebagai KEDALUWARSA.
     */
    public function markExpiredDocuments(?Company $company = null): int
    {
        $query = $company ? $company->documents() : CompanyDocument::query();

        return $query
            ->where('status', '!=', CompanyDocument::STATUS_EXPIRED)
            ->whereNotNull('berlaku_sampai')
            ->whereDate('berlaku_sampai', '<', $this->today())
            ->update(['status' => CompanyDocument::STATUS_EXPIRED]);
    }

    /**
     * Kembalikan perusahaan terverifikasi ke antrean tinjauan (PENDING) bila
     * ada dokumen wajibnya yang kedaluwarsa. Mengembalikan jumlah perusahaan.
     */
    public function reviewCompaniesWithExpiredDocuments(): int
    {
        return DB::transaction(function () {
            $this->markExpiredDocuments();

            $count = 0;
            Company::query()
                ->where('status_verifikasi', Company::STATUS_VERIFIED)
                ->whereHas('documents', fn ($q) => $q->where('status', CompanyDocument::STATUS_EXPIRED))
                ->lockForUpdate()
                ->get()
                ->each(function (Company $company) use (&$count) {
                    if ($this->missingRequiredDocuments($company) !== []) {
                        $company->update(['status_verifikasi' => Company::STATUS_PENDING]);
                        $count++;
                    }
                });

            return $count;
        });
    }

    public function needsReReview(Company $company): bool
    {
        return $company->status_verifikasi === Company::STATUS_PENDING
            && $company->documents()->where('status', CompanyDocument::STATUS_EXPIRED)->exists();
    }

    protected function today(): string
    {
        return now(self::TIMEZONE)->toDateString();
    }
}

<?php

namespace App\Services\Listing;

use App\Models\Company;
use App\Models\Listing;
use App\Models\ListingAttachment;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ListingService
{
    /**
     * Jenis lampiran => [nama field unggahan, folder penyimpanan].
     *
     * @var array<string, array{0: string, 1: string}>
     */
    protected const ATTACHMENT_FIELDS = [
        'SURAT_NON_B3' => ['surat_non_b3', 'listings/documents'],
        'FOTO_SAMPEL' => ['foto_sampel', 'listings/photos'],
        'SERTIFIKAT_LAB' => ['sertifikat_lab', 'listings/certificates'],
    ];

    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $files
     */
    public function createListing(array $data, array $files, User $seller): Listing
    {
        return DB::transaction(function () use ($data, $files, $seller) {
            $listing = Listing::create([
                'judul' => $data['judul'],
                'spesifikasi' => $data['spesifikasi'] ?? null,
                'volume_kg' => $data['volume_kg'],
                'stok_kg' => $data['stok_kg'] ?? $data['volume_kg'],
                'harga_per_kg' => $data['harga_per_kg'],
                'biaya_buang_per_kg' => $data['biaya_buang_per_kg'] ?? null,
                'metode_buang_sebelumnya' => $data['metode_buang_sebelumnya'] ?? null,
                'lokasi' => $data['lokasi'],
                'status' => $data['status'],
                'companies_id_company' => $seller->id_company,
                'categories_id_categories' => $data['categories_id_categories'],
            ]);

            $this->storeAttachments($listing, $files, $data);

            return $listing->fresh(['attachments', 'category', 'company']) ?? $listing;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $files
     */
    public function updateListing(Listing $listing, array $data, array $files): Listing
    {
        return DB::transaction(function () use ($listing, $data, $files) {
            // Baris stok dikunci agar tidak balapan dengan reservasi stok saat konfirmasi PO.
            /** @var Listing $locked */
            $locked = Listing::whereKey($listing->id_listings)->lockForUpdate()->firstOrFail();

            $locked->update([
                'judul' => $data['judul'],
                'spesifikasi' => array_key_exists('spesifikasi', $data) ? $data['spesifikasi'] : $locked->spesifikasi,
                'volume_kg' => $data['volume_kg'],
                'stok_kg' => $data['stok_kg'],
                'harga_per_kg' => $data['harga_per_kg'],
                'biaya_buang_per_kg' => $data['biaya_buang_per_kg'] ?? null,
                'metode_buang_sebelumnya' => $data['metode_buang_sebelumnya'] ?? null,
                'lokasi' => $data['lokasi'],
                'status' => $data['status'],
                'categories_id_categories' => $data['categories_id_categories'],
            ]);

            $this->storeAttachments($locked, $files, $data);

            return $locked->fresh(['attachments', 'category', 'company']) ?? $locked;
        });
    }

    public function archiveListing(Listing $listing): Listing
    {
        $listing->update(['status' => 'DIARSIPKAN']);

        return $listing;
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, Listing>
     */
    public function getSellerListings(int $companyId, array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = Listing::query()
            ->where('companies_id_company', $companyId)
            ->with(['category', 'activeAttachments']);

        $this->applyKeywordAndCategory($query, $filters);

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderByDesc('id_listings')->paginate($perPage)->withQueryString();
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, Listing>
     */
    public function getCatalogListings(array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        $query = Listing::query()
            ->published()
            ->where('stok_kg', '>', 0)
            // Listing perusahaan yang kembali ke Tinjauan Ulang tidak tampil di katalog (PBI-08).
            ->whereHas('company', fn ($q) => $q->where('status_verifikasi', Company::STATUS_VERIFIED))
            ->with(['category', 'company', 'activeAttachments']);

        $this->applyKeywordAndCategory($query, $filters);

        return $query->orderByDesc('id_listings')->paginate($perPage)->withQueryString();
    }

    /**
     * Daftar semua listing untuk moderasi Admin.
     *
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, Listing>
     */
    public function getModerationListings(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Listing::query()->with(['category', 'company']);

        $this->applyKeywordAndCategory($query, $filters);

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query
            ->orderByRaw("FIELD(status, 'PUBLISHED', 'DRAF', 'DIARSIPKAN')")
            ->orderByDesc('id_listings')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @param  Builder<Listing>  $query
     * @param  array<string, mixed>  $filters
     */
    protected function applyKeywordAndCategory($query, array $filters): void
    {
        if (! empty($filters['q'])) {
            $keyword = '%'.$filters['q'].'%';
            $query->where(function ($q) use ($keyword) {
                $q->where('judul', 'like', $keyword)
                    ->orWhere('lokasi', 'like', $keyword);
            });
        }

        if (! empty($filters['kategori'])) {
            $query->where('categories_id_categories', $filters['kategori']);
        }
    }

    /**
     * Simpan lampiran baru. Lampiran aktif sebelumnya dengan jenis yang sama
     * dinonaktifkan agar hanya ada satu lampiran aktif per jenis.
     *
     * @param  array<string, mixed>  $files
     * @param  array<string, mixed>  $data
     */
    protected function storeAttachments(Listing $listing, array $files, array $data): void
    {
        foreach (self::ATTACHMENT_FIELDS as $jenis => [$field, $folder]) {
            $file = $files[$field] ?? null;
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $listing->attachments()
                ->where('jenis', $jenis)
                ->where('status', 'AKTIF')
                ->update(['status' => 'NONAKTIF']);

            ListingAttachment::create([
                'jenis' => $jenis,
                'file_url' => Storage::disk('public')->putFile($folder, $file),
                'tanggal_pengambilan' => $jenis === 'FOTO_SAMPEL'
                    ? ($data['tanggal_pengambilan_sampel'] ?? now('Asia/Jakarta')->toDateString())
                    : null,
                'listings_id_listings' => $listing->id_listings,
                'status' => 'AKTIF',
            ]);
        }
    }
}

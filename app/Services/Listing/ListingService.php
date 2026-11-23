<?php

namespace App\Services\Listing;

use App\Models\Listing;
use App\Models\ListingAttachment;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ListingService
{
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

            $this->storeAttachments($listing, $files);

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
            $listing->update([
                'judul' => $data['judul'],
                'spesifikasi' => $data['spesifikasi'] ?? $listing->spesifikasi,
                'volume_kg' => $data['volume_kg'],
                'stok_kg' => $data['stok_kg'],
                'harga_per_kg' => $data['harga_per_kg'],
                'biaya_buang_per_kg' => $data['biaya_buang_per_kg'] ?? null,
                'metode_buang_sebelumnya' => $data['metode_buang_sebelumnya'] ?? null,
                'lokasi' => $data['lokasi'],
                'status' => $data['status'],
                'categories_id_categories' => $data['categories_id_categories'],
            ]);

            $this->storeAttachments($listing, $files);

            return $listing->fresh(['attachments', 'category', 'company']) ?? $listing;
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
            ->with(['category', 'attachments']);

        if (! empty($filters['q'])) {
            $keyword = '%'.$filters['q'].'%';
            $query->where(function ($q) use ($keyword) {
                $q->where('judul', 'like', $keyword)
                    ->orWhere('lokasi', 'like', $keyword);
            });
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['kategori'])) {
            $query->where('categories_id_categories', $filters['kategori']);
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
            ->with(['category', 'company', 'attachments']);

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

        return $query->orderByDesc('id_listings')->paginate($perPage)->withQueryString();
    }

    /**
     * @param  array<string, mixed>  $files
     */
    protected function storeAttachments(Listing $listing, array $files): void
    {
        if (isset($files['surat_non_b3']) && $files['surat_non_b3'] instanceof UploadedFile) {
            $path = Storage::disk('public')->putFile('listings/documents', $files['surat_non_b3']);
            ListingAttachment::create([
                'jenis' => 'SURAT_NON_B3',
                'file_url' => $path,
                'listings_id_listings' => $listing->id_listings,
                'status' => 'AKTIF',
            ]);
        }

        if (isset($files['foto_sampel']) && $files['foto_sampel'] instanceof UploadedFile) {
            $path = Storage::disk('public')->putFile('listings/photos', $files['foto_sampel']);
            ListingAttachment::create([
                'jenis' => 'FOTO_SAMPEL',
                'file_url' => $path,
                'tanggal_pengambilan' => $files['tanggal_pengambilan_sampel'] ?? now()->toDateString(),
                'listings_id_listings' => $listing->id_listings,
                'status' => 'AKTIF',
            ]);
        }

        if (isset($files['sertifikat_lab']) && $files['sertifikat_lab'] instanceof UploadedFile) {
            $path = Storage::disk('public')->putFile('listings/certificates', $files['sertifikat_lab']);
            ListingAttachment::create([
                'jenis' => 'SERTIFIKAT_LAB',
                'file_url' => $path,
                'listings_id_listings' => $listing->id_listings,
                'status' => 'AKTIF',
            ]);
        }
    }
}

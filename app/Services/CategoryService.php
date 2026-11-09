<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CategoryService
{
    /**
     * Evaluasi apakah kategori dapat diarsipkan.
     * Mencegah arsip jika masih dipakai (subkategori aktif, listing aktif, atau permintaan aktif).
     */
    public function canArchive(Category $category): bool
    {
        return empty($this->getArchiveBlockReasons($category));
    }

    /**
     * Dapatkan daftar alasan mengapa kategori tidak dapat diarsipkan.
     *
     * @return array<int, string>
     */
    public function getArchiveBlockReasons(Category $category): array
    {
        $reasons = [];

        // 1. Cek subkategori aktif
        $activeChildrenCount = $category->children()
            ->where('status', 'AKTIF')
            ->count();

        if ($activeChildrenCount > 0) {
            $reasons[] = "Kategori masih memiliki {$activeChildrenCount} subkategori aktif.";
        }

        // 2. Cek listing aktif (DRAF atau PUBLISHED)
        $activeListingsCount = $category->listings()
            ->whereIn('status', ['DRAF', 'PUBLISHED'])
            ->count();

        if ($activeListingsCount > 0) {
            $reasons[] = "Kategori masih digunakan oleh {$activeListingsCount} listing material aktif.";
        }

        // 3. Cek permintaan material (demand request) yang masih terbuka
        $activeDemandsCount = $category->demandRequests()
            ->where('status', 'TERBUKA')
            ->count();

        if ($activeDemandsCount > 0) {
            $reasons[] = "Kategori masih digunakan oleh {$activeDemandsCount} permintaan kebutuhan bahan baku terbuka.";
        }

        return $reasons;
    }

    /**
     * Arsipkan kategori jika memenuhi syarat canArchive.
     *
     * @throws InvalidArgumentException
     */
    public function archive(Category $category): bool
    {
        $reasons = $this->getArchiveBlockReasons($category);

        if (! empty($reasons)) {
            throw new InvalidArgumentException(implode(' ', $reasons));
        }

        return $category->update(['status' => 'ARSIP']);
    }

    /**
     * Pulihkan kategori dari status arsip kembali ke status aktif.
     */
    public function unarchive(Category $category): bool
    {
        return $category->update(['status' => 'AKTIF']);
    }

    /**
     * Buat kategori baru dalam transaksi database.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Category
    {
        return DB::transaction(function () use ($data) {
            $normalized = $this->normalizeCategoryData($data);

            return Category::create($normalized);
        });
    }

    /**
     * Perbarui kategori yang sudah ada dalam transaksi database.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Category $category, array $data): Category
    {
        return DB::transaction(function () use ($category, $data) {
            $normalized = $this->normalizeCategoryData($data, $category->id_categories);
            $category->update($normalized);

            return $category->fresh() ?? $category;
        });
    }

    /**
     * Normalisasi dan sanitasi data kategori.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function normalizeCategoryData(array $data, ?int $currentCategoryId = null): array
    {
        // Cegah self-referencing hierarchy
        if (isset($data['categories_id_categories']) && $currentCategoryId !== null) {
            if ((int) $data['categories_id_categories'] === $currentCategoryId) {
                $data['categories_id_categories'] = null;
            }
        }

        // Pastikan format spec_schema tersimpan sebagai array/JSON valid
        if (isset($data['spec_schema']) && is_string($data['spec_schema'])) {
            $decoded = json_decode($data['spec_schema'], true);
            $data['spec_schema'] = is_array($decoded) ? $decoded : null;
        }

        // Penanganan boolean flags
        if (isset($data['is_b3_prohibited'])) {
            $data['is_b3_prohibited'] = (int) (bool) $data['is_b3_prohibited'];
        }

        if (isset($data['wajib_sertifikat_lab'])) {
            $data['wajib_sertifikat_lab'] = (int) (bool) $data['wajib_sertifikat_lab'];
        }

        return $data;
    }
}

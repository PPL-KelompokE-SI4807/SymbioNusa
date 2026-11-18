<?php

namespace App\Services\Demand;

use App\Enums\DemandRequestStatus;
use App\Models\DemandRequest;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class DemandService
{
    /**
     * Membuat publikasi kebutuhan baru oleh Buyer.
     *
     * @param  array<string, mixed>  $data
     */
    public function createDemand(array $data, User $user): DemandRequest
    {
        if (! $user->id_company) {
            throw new InvalidArgumentException('Pengguna harus terdaftar pada sebuah perusahaan.');
        }

        $isDraft = ($data['action'] ?? '') === 'draft';
        $status = $isDraft ? DemandRequestStatus::DRAF : DemandRequestStatus::TERBUKA;

        return DB::transaction(function () use ($data, $user, $status) {
            $demand = new DemandRequest;
            $demand->categories_id_categories = (int) $data['categories_id_categories'];
            $demand->companies_id_company = (int) $user->id_company;
            $demand->volume_kg = (string) $data['volume_kg'];
            $demand->harga_maks = isset($data['harga_maks']) && $data['harga_maks'] !== '' ? (string) $data['harga_maks'] : null;
            $demand->berlaku_sampai = isset($data['berlaku_sampai']) && $data['berlaku_sampai'] !== '' ? Carbon::parse($data['berlaku_sampai']) : null;
            $demand->frekuensi = $data['frekuensi'];
            $demand->spesifikasi_min = isset($data['spesifikasi_min']) && is_array($data['spesifikasi_min']) ? $data['spesifikasi_min'] : null;
            $demand->status = $status;
            $demand->save();

            return $demand;
        });
    }

    /**
     * Memperbarui publikasi kebutuhan oleh Buyer pemilik.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateDemand(DemandRequest $demand, array $data): DemandRequest
    {
        $action = $data['action'] ?? null;
        $status = $demand->status;

        if ($action === 'publish') {
            $status = DemandRequestStatus::TERBUKA;
        } elseif ($action === 'draft') {
            $status = DemandRequestStatus::DRAF;
        }

        return DB::transaction(function () use ($demand, $data, $status) {
            $demand->categories_id_categories = (int) $data['categories_id_categories'];
            $demand->volume_kg = (string) $data['volume_kg'];
            $demand->harga_maks = isset($data['harga_maks']) && $data['harga_maks'] !== '' ? (string) $data['harga_maks'] : null;
            $demand->berlaku_sampai = isset($data['berlaku_sampai']) && $data['berlaku_sampai'] !== '' ? Carbon::parse($data['berlaku_sampai']) : null;
            $demand->frekuensi = $data['frekuensi'];
            $demand->spesifikasi_min = isset($data['spesifikasi_min']) && is_array($data['spesifikasi_min']) ? $data['spesifikasi_min'] : null;
            $demand->status = $status;
            $demand->save();

            return $demand;
        });
    }

    /**
     * Menutup kebutuhan secara manual oleh Buyer pemilik.
     */
    public function closeDemand(DemandRequest $demand): bool
    {
        return DB::transaction(function () use ($demand) {
            $demand->status = DemandRequestStatus::KEDALUWARSA;

            return $demand->save();
        });
    }

    /**
     * Menghapus draf kebutuhan oleh Buyer pemilik.
     */
    public function deleteDemand(DemandRequest $demand): bool
    {
        return DB::transaction(function () use ($demand) {
            return (bool) $demand->delete();
        });
    }

    /**
     * Menutup otomatis seluruh kebutuhan yang telah melewati batas berlaku.
     */
    public function closeExpiredDemands(): int
    {
        return DB::transaction(function () {
            return DemandRequest::where('status', DemandRequestStatus::TERBUKA->value)
                ->whereNotNull('berlaku_sampai')
                ->where('berlaku_sampai', '<', Carbon::today())
                ->update(['status' => DemandRequestStatus::KEDALUWARSA->value]);
        });
    }
}

<?php

namespace App\Services\Inspector;

use App\Models\InspectorProfile;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Logika Registri Inspector (PBI-01 / SYM-5).
 *
 * - Berkas sertifikat disimpan di disk "local" (privat) dan hanya diunduh lewat controller berotorisasi.
 * - "Keluarkan dari registri" = status_aktif 0; profil tetap ada (soft delete tanpa kolom deleted_at).
 * - Setiap perubahan dicatat di audit_logs.
 */
class InspectorService
{
    public const CERTIFICATE_DIR = 'inspector-certificates';

    public const DISK = 'local';

    /**
     * @param  array{id_user: int|string, bidang_keahlian: list<int|string>, wilayah_tugas: string, no_sertifikat: string, berlaku_sampai: string}  $data
     */
    public function register(array $data, UploadedFile $certificate, User $actor, ?string $ip = null): InspectorProfile
    {
        return $this->withStoredFile($certificate, function (string $path) use ($data, $actor, $ip) {
            $profile = InspectorProfile::create([
                'id_user' => (int) $data['id_user'],
                'bidang_keahlian' => $this->normalizeIds($data['bidang_keahlian']),
                'wilayah_tugas' => $data['wilayah_tugas'],
                'no_sertifikat' => trim($data['no_sertifikat']),
                'sertifikat_url' => $path,
                'berlaku_sampai' => $data['berlaku_sampai'],
                'status_aktif' => 1,
            ]);

            $this->audit('INSPECTOR_DIDAFTARKAN', $profile, null, $this->snapshot($profile), $actor, $ip);

            return $profile;
        });
    }

    /**
     * Ubah bidang keahlian, wilayah, nomor sertifikat, dan (opsional) berkasnya.
     *
     * @param  array{bidang_keahlian: list<int|string>, wilayah_tugas: string, no_sertifikat: string}  $data
     */
    public function update(InspectorProfile $profile, array $data, ?UploadedFile $certificate, User $actor, ?string $ip = null): InspectorProfile
    {
        return $this->withStoredFile($certificate, function (?string $path) use ($profile, $data, $actor, $ip) {
            $before = $this->snapshot($profile);

            $profile->fill([
                'bidang_keahlian' => $this->normalizeIds($data['bidang_keahlian']),
                'wilayah_tugas' => $data['wilayah_tugas'],
                'no_sertifikat' => trim($data['no_sertifikat']),
            ]);
            if ($path !== null) {
                $profile->sertifikat_url = $path;
            }
            $profile->save();

            $this->audit('INSPECTOR_DIUBAH', $profile, $before, $this->snapshot($profile), $actor, $ip);

            return $profile;
        });
    }

    /**
     * Perpanjang sertifikat: masa berlaku baru, sekaligus mengaktifkan kembali Inspector.
     *
     * @param  array{berlaku_sampai: string, no_sertifikat: string}  $data
     */
    public function renew(InspectorProfile $profile, array $data, ?UploadedFile $certificate, User $actor, ?string $ip = null): InspectorProfile
    {
        return $this->withStoredFile($certificate, function (?string $path) use ($profile, $data, $actor, $ip) {
            $before = $this->snapshot($profile);

            $profile->fill([
                'berlaku_sampai' => $data['berlaku_sampai'],
                'no_sertifikat' => trim($data['no_sertifikat']),
                'status_aktif' => 1,
            ]);
            if ($path !== null) {
                $profile->sertifikat_url = $path;
            }
            $profile->save();

            $this->audit('INSPECTOR_DIPERPANJANG', $profile, $before, $this->snapshot($profile), $actor, $ip);

            return $profile;
        });
    }

    /**
     * Keluarkan dari registri (status_aktif 0) dengan alasan tercatat.
     */
    public function deactivate(InspectorProfile $profile, string $reason, User $actor, ?string $ip = null): InspectorProfile
    {
        return DB::transaction(function () use ($profile, $reason, $actor, $ip) {
            $before = $this->snapshot($profile);

            $profile->status_aktif = false;
            $profile->save();

            $this->audit('INSPECTOR_DIKELUARKAN', $profile, $before, $this->snapshot($profile) + ['alasan' => trim($reason)], $actor, $ip);

            return $profile;
        });
    }

    /**
     * Inspector mengganti berkas sertifikatnya sendiri. Masa berlaku dan status tidak berubah;
     * Admin meninjau berkas baru lalu memperpanjang.
     */
    public function replaceOwnCertificate(InspectorProfile $profile, UploadedFile $certificate, User $actor, ?string $ip = null): InspectorProfile
    {
        return $this->withStoredFile($certificate, function (string $path) use ($profile, $actor, $ip) {
            $before = $this->snapshot($profile);

            $profile->sertifikat_url = $path;
            $profile->save();

            $this->audit('INSPECTOR_UNGGAH_SERTIFIKAT', $profile, $before, $this->snapshot($profile), $actor, $ip);

            return $profile;
        });
    }

    /**
     * Nonaktifkan semua Inspector yang sertifikatnya sudah lewat (dijalankan scheduler harian).
     *
     * @return int jumlah Inspector yang dinonaktifkan
     */
    public function deactivateExpired(?CarbonInterface $today = null): int
    {
        $today = ($today ?? InspectorProfile::today())->toDateString();
        $count = 0;

        InspectorProfile::query()
            ->where('status_aktif', 1)
            ->whereDate('berlaku_sampai', '<', $today)
            ->orderBy('id_inspector')
            ->each(function (InspectorProfile $profile) use (&$count) {
                DB::transaction(function () use ($profile) {
                    $before = $this->snapshot($profile);
                    $profile->status_aktif = false;
                    $profile->save();

                    $this->audit('INSPECTOR_KEDALUWARSA', $profile, $before, $this->snapshot($profile), null, null);
                });
                $count++;
            });

        return $count;
    }

    /**
     * Simpan berkas dulu, jalankan perubahan dalam transaksi, dan hapus berkas bila transaksi gagal.
     *
     * @template T
     *
     * @param  callable(?string): T  $callback
     * @return T
     */
    private function withStoredFile(?UploadedFile $file, callable $callback): mixed
    {
        $path = $file?->store(self::CERTIFICATE_DIR, self::DISK);

        if ($file !== null && $path === false) {
            throw new \RuntimeException('Berkas sertifikat gagal disimpan.');
        }

        try {
            return DB::transaction(fn () => $callback($path ?: null));
        } catch (Throwable $e) {
            if (is_string($path)) {
                Storage::disk(self::DISK)->delete($path);
            }
            throw $e;
        }
    }

    /**
     * @param  list<int|string>  $ids
     * @return list<int>
     */
    private function normalizeIds(array $ids): array
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));
        sort($ids);

        return $ids;
    }

    /**
     * @return array<string, mixed>
     */
    private function snapshot(InspectorProfile $profile): array
    {
        return [
            'bidang_keahlian' => $profile->expertiseIds(),
            'wilayah_tugas' => $profile->wilayah_tugas,
            'no_sertifikat' => $profile->no_sertifikat,
            'sertifikat_url' => $profile->sertifikat_url,
            'berlaku_sampai' => $profile->berlaku_sampai?->toDateString(),
            'status_aktif' => (bool) $profile->status_aktif,
        ];
    }

    /**
     * @param  array<string, mixed>|null  $before
     * @param  array<string, mixed>|null  $after
     */
    private function audit(string $action, InspectorProfile $profile, ?array $before, ?array $after, ?User $actor, ?string $ip): void
    {
        DB::table('audit_logs')->insert([
            'waktu' => Carbon::now(),
            'users_id_user' => $actor?->id_user,
            'aksi' => $action,
            'auditable_type' => 'inspector_profiles',
            'auditable_id' => $profile->id_inspector,
            'nilai_lama' => $before === null ? null : json_encode($before),
            'nilai_baru' => $after === null ? null : json_encode($after),
            'ip_address' => $ip,
        ]);
    }
}

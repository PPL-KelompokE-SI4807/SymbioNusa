<?php

namespace App\Http\Requests\Inspector;

use App\Support\Wilayah;
use Illuminate\Validation\Rule;

/**
 * Aturan dan pesan validasi bersama Registri Inspector (PBI-01 / SYM-5).
 * Locale aplikasi masih "en", jadi semua pesan ditulis eksplisit dalam Bahasa Indonesia.
 */
final class InspectorRules
{
    /**
     * @return array<string, mixed>
     */
    public static function competency(): array
    {
        return [
            'bidang_keahlian' => ['required', 'array', 'min:1'],
            'bidang_keahlian.*' => [
                'integer',
                'distinct',
                // Hanya kategori aktif non-B3: material B3 tidak pernah diperdagangkan, jadi tidak diinspeksi.
                Rule::exists('categories', 'id_categories')
                    ->where('status', 'AKTIF')
                    ->where('is_b3_prohibited', 0),
            ],
            'wilayah_tugas' => ['required', 'string', Rule::in(Wilayah::PROVINSI)],
        ];
    }

    /**
     * @return list<string>
     */
    public static function certificateFile(bool $required): array
    {
        return [
            $required ? 'required' : 'nullable',
            'file',
            'mimes:pdf,jpg,jpeg,png',
            'max:'.self::maxUploadKb(),
        ];
    }

    public static function maxUploadKb(): int
    {
        return (int) config('symbionusa.max_upload_size_mb', 5) * 1024;
    }

    /**
     * @return array<string, string>
     */
    public static function messages(): array
    {
        $maxMb = (int) config('symbionusa.max_upload_size_mb', 5);

        return [
            'id_user.required' => 'Pilih akun pengguna Inspector.',
            'id_user.integer' => 'Akun pengguna tidak valid.',
            'id_user.exists' => 'Akun harus berperan Inspector dan berstatus aktif.',
            'id_user.unique' => 'Akun ini sudah terdaftar di registri Inspector.',
            'bidang_keahlian.required' => 'Pilih minimal satu bidang keahlian.',
            'bidang_keahlian.array' => 'Bidang keahlian tidak valid.',
            'bidang_keahlian.min' => 'Pilih minimal satu bidang keahlian.',
            'bidang_keahlian.*.integer' => 'Bidang keahlian tidak valid.',
            'bidang_keahlian.*.distinct' => 'Bidang keahlian tidak boleh dipilih dua kali.',
            'bidang_keahlian.*.exists' => 'Bidang keahlian harus kategori material aktif dan bukan B3.',
            'wilayah_tugas.required' => 'Pilih wilayah tugas.',
            'wilayah_tugas.in' => 'Pilih wilayah tugas dari daftar provinsi.',
            'no_sertifikat.required' => 'Isi nomor sertifikat.',
            'no_sertifikat.max' => 'Nomor sertifikat maksimal 100 karakter.',
            'no_sertifikat.unique' => 'Nomor sertifikat ini sudah dipakai Inspector lain.',
            'berlaku_sampai.required' => 'Isi tanggal berlaku sampai.',
            'berlaku_sampai.date' => 'Tanggal berlaku sampai tidak valid.',
            'berlaku_sampai.after' => 'Tanggal berlaku sampai harus setelah hari ini.',
            'sertifikat.required' => 'Unggah berkas sertifikat.',
            'sertifikat.file' => 'Berkas sertifikat gagal diunggah. Coba lagi.',
            'sertifikat.uploaded' => 'Berkas sertifikat gagal diunggah. Ukuran maksimal '.$maxMb.' MB.',
            'sertifikat.mimes' => 'Berkas sertifikat harus PDF, JPG, atau PNG.',
            'sertifikat.max' => 'Ukuran berkas sertifikat maksimal '.$maxMb.' MB.',
            'alasan.required' => 'Tuliskan alasan mengeluarkan Inspector.',
            'alasan.min' => 'Alasan minimal 10 karakter.',
            'alasan.max' => 'Alasan maksimal 500 karakter.',
        ];
    }
}

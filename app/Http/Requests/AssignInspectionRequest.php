<?php

namespace App\Http\Requests;

use App\Models\Inspection;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignInspectionRequest extends FormRequest
{
    /**
     * Tentukan apakah user berwenang membuat penugasan ini.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('assign', Inspection::class) ?? false;
    }

    /**
     * Aturan validasi penugasan inspeksi.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'users_id_user' => ['required', 'integer', 'exists:users,id_user'],
            'mode_verifikasi' => [
                'required',
                'string',
                Rule::in([Inspection::MODE_DESK, Inspection::MODE_LAPANGAN]),
            ],
            'is_override' => ['nullable', 'boolean'],
            'alasan_mode' => [
                'nullable',
                'string',
                'max:255',
                'required_if:is_override,1',
                'required_if:is_override,true',
            ],
            'jadwal' => ['required', 'date', 'after_or_equal:now'],
        ];
    }

    /**
     * Pesan validasi dalam Bahasa Indonesia.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'users_id_user.required' => 'Pilih salah satu Inspector yang memenuhi syarat.',
            'users_id_user.exists' => 'Inspector yang dipilih tidak terdaftar.',
            'mode_verifikasi.required' => 'Mode verifikasi wajib ditentukan.',
            'mode_verifikasi.in' => 'Mode verifikasi harus berupa Desk atau Lapangan.',
            'alasan_mode.required_if' => 'Alasan wajib diisi jika mode verifikasi diubah dari rekomendasi sistem.',
            'alasan_mode.max' => 'Alasan penyesuaian mode tidak boleh lebih dari 255 karakter.',
            'jadwal.required' => 'Jadwal pelaksanaan inspeksi wajib ditentukan.',
            'jadwal.date' => 'Format tanggal dan jam jadwal inspeksi tidak valid.',
            'jadwal.after_or_equal' => 'Jadwal inspeksi tidak boleh ditentukan di masa lampau.',
        ];
    }
}

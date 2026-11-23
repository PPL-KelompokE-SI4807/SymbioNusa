<?php

namespace App\Http\Requests;

use App\Models\DocumentVerification;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VerifyDocumentRequest extends FormRequest
{
    /**
     * Tentukan apakah user berwenang membuat request ini.
     */
    public function authorize(): bool
    {
        $verification = $this->route('verification');

        if (! $verification instanceof DocumentVerification) {
            return false;
        }

        return $this->user()?->can('verify', $verification) ?? false;
    }

    /**
     * Aturan validasi untuk verifikasi dokumen.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => [
                'required',
                'string',
                Rule::in([DocumentVerification::STATUS_SETUJU, DocumentVerification::STATUS_DITOLAK]),
            ],
            'checklist' => ['nullable', 'array'],
            'checklist.*' => ['nullable', 'boolean'],
            'alasan' => [
                'nullable',
                'string',
                'max:512',
                'required_if:status,'.DocumentVerification::STATUS_DITOLAK,
            ],
        ];
    }

    /**
     * Pesan error kustom dalam Bahasa Indonesia.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'status.required' => 'Keputusan verifikasi wajib ditentukan.',
            'status.in' => 'Keputusan verifikasi harus berupa Setuju atau Tolak.',
            'alasan.required_if' => 'Catatan penolakan wajib diisi jika verifikasi ditolak.',
            'alasan.max' => 'Catatan penolakan tidak boleh melebihi 512 karakter.',
        ];
    }
}

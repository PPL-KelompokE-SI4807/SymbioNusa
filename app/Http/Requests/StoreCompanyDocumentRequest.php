<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCompanyDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->company !== null;
    }

    public function rules(): array
    {
        return [
            'jenis' => ['required', 'string', 'max:45', 'in:NIB,NPWP,AKTA_PENDIRIAN,IZIN_LINGKUNGAN,SURAT_NON_B3'],
            'berlaku_sampai' => ['nullable', 'date', 'after:today'],
            'dokumen' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'jenis.required' => 'Jenis dokumen legalitas wajib dipilih.',
            'dokumen.mimes' => 'Format berkas wajib PDF, JPG, JPEG, atau PNG.',
            'dokumen.max' => 'Ukuran berkas maksimal adalah 5 MB.',
            'berlaku_sampai.after' => 'Tanggal masa berlaku dokumen harus lebih dari hari ini.',
        ];
    }
}

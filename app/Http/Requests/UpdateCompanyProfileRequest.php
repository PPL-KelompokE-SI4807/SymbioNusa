<?php

namespace App\Http\Requests;

use App\Models\Company;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCompanyProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $companyId = $this->user()?->id_company;

        return [
            'nama' => ['required', 'string', 'max:255'],
            'tipe' => ['required', Rule::in([Company::TYPE_SELLER, Company::TYPE_BUYER, Company::TYPE_BOTH])],
            'nib' => [
                'required',
                'string',
                'max:45',
                Rule::unique('companies', 'nib')->ignore($companyId, 'id_company'),
            ],
            'alamat' => ['required', 'string', 'max:255'],
            'kontak' => ['required', 'string', 'max:45'],
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'numeric', 'between:-180,180'],
            'tos_accepted' => ['accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'nib.unique' => 'Nomor Induk Berusaha (NIB) tersebut sudah terdaftar pada perusahaan lain.',
            'tos_accepted.accepted' => 'Anda wajib menyetujui Syarat & Ketentuan SymbioNusa.',
        ];
    }
}

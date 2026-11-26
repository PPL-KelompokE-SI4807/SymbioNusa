<?php

namespace App\Http\Requests;

use App\Models\Category;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', Category::class) ?? false;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Parse spec_schema_json jika dikirim sebagai string dari textarea/JSON builder
        if ($this->has('spec_schema_json') && is_string($this->input('spec_schema_json'))) {
            $raw = trim($this->input('spec_schema_json'));
            if ($raw !== '') {
                $decoded = json_decode($raw, true);
                if (is_array($decoded)) {
                    $this->merge(['spec_schema' => $decoded]);
                }
            }
        } elseif ($this->has('spec_schema') && is_string($this->input('spec_schema'))) {
            $raw = trim($this->input('spec_schema'));
            if ($raw !== '') {
                $decoded = json_decode($raw, true);
                if (is_array($decoded)) {
                    $this->merge(['spec_schema' => $decoded]);
                }
            }
        }

        // Normalisasi boolean checkboxes
        $this->merge([
            'is_b3_prohibited' => $this->boolean('is_b3_prohibited'),
            'wajib_sertifikat_lab' => $this->boolean('wajib_sertifikat_lab'),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:100'],
            'categories_id_categories' => ['nullable', 'integer', 'exists:categories,id_categories'],
            'is_b3_prohibited' => ['boolean'],
            'toleransi_susut_persen' => ['required', 'numeric', 'min:0', 'max:100'],
            'kadar_air_maks_persen' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'toleransi_kadar_air_poin' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'tingkat_risiko' => ['required', 'in:RENDAH,TINGGI'],
            'wajib_sertifikat_lab' => ['boolean'],
            'status' => ['nullable', 'in:AKTIF,ARSIP'],
            'spec_schema' => ['nullable', 'array'],
            'spec_schema.*.nama' => ['required_with:spec_schema', 'string', 'max:100'],
            'spec_schema.*.tipe' => ['required_with:spec_schema', 'string', 'max:50'],
            'spec_schema.*.satuan' => ['nullable', 'string', 'max:50'],
            'spec_schema.*.wajib' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nama.required' => 'Nama kategori wajib diisi.',
            'nama.max' => 'Nama kategori maksimal 100 karakter.',
            'categories_id_categories.exists' => 'Kategori induk yang dipilih tidak valid.',
            'toleransi_susut_persen.required' => 'Toleransi susut tonase wajib diisi.',
            'toleransi_susut_persen.numeric' => 'Toleransi susut harus berupa angka.',
            'toleransi_susut_persen.min' => 'Toleransi susut minimal 0%.',
            'toleransi_susut_persen.max' => 'Toleransi susut maksimal 100%.',
            'kadar_air_maks_persen.numeric' => 'Kadar air maksimum harus berupa angka.',
            'kadar_air_maks_persen.min' => 'Kadar air maksimum minimal 0%.',
            'kadar_air_maks_persen.max' => 'Kadar air maksimum maksimal 100%.',
            'toleransi_kadar_air_poin.numeric' => 'Toleransi poin kadar air harus berupa angka.',
            'toleransi_kadar_air_poin.min' => 'Toleransi poin kadar air minimal 0.',
            'toleransi_kadar_air_poin.max' => 'Toleransi poin kadar air maksimal 100.',
            'tingkat_risiko.required' => 'Tingkat risiko wajib dipilih.',
            'tingkat_risiko.in' => 'Tingkat risiko harus bernilai RENDAH atau TINGGI.',
            'spec_schema.array' => 'Format templat spesifikasi harus berupa array atau JSON valid.',
            'spec_schema.*.nama.required_with' => 'Nama parameter spesifikasi wajib diisi.',
            'spec_schema.*.tipe.required_with' => 'Tipe parameter spesifikasi wajib diisi.',
        ];
    }
}

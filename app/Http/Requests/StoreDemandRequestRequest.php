<?php

namespace App\Http\Requests;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreDemandRequestRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && $user->role === 'BUYER' && (bool) $user->id_company;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'categories_id_categories' => ['required', 'integer', 'exists:categories,id_categories'],
            'volume_kg' => ['required', 'numeric', 'min:0.01'],
            'harga_maks' => ['nullable', 'numeric', 'min:0'],
            'berlaku_sampai' => ['nullable', 'date', 'after_or_equal:today'],
            'frekuensi' => ['required', Rule::in(['SEKALI', 'MINGGUAN', 'BULANAN'])],
            'action' => ['nullable', 'string', Rule::in(['draft', 'publish'])],
            'spesifikasi_min' => ['nullable', 'array'],
        ];
    }

    /**
     * Konfigurasi validasi tambahan untuk kepatuhan B3 dan templat spesifikasi kategori.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $categoryId = $this->input('categories_id_categories');
            if (! $categoryId) {
                return;
            }

            $category = Category::find($categoryId);
            if (! $category) {
                return;
            }

            // Kategori B3 dilarang
            if ($category->is_b3_prohibited) {
                $v->errors()->add(
                    'categories_id_categories',
                    'Kategori material ini termasuk kategori B3 yang dilarang pada platform SymbioNusa.'
                );
            }

            // Validasi spesifikasi minimal bila bukan draf dan kategori memiliki spec_schema
            $isPublish = $this->input('action', 'publish') === 'publish';
            if ($isPublish && is_array($category->spec_schema)) {
                $spesifikasi = (array) $this->input('spesifikasi_min', []);
                foreach ($category->spec_schema as $field) {
                    $fieldName = $field['nama'];
                    $isWajib = ! empty($field['wajib']);
                    if ($isWajib && (! isset($spesifikasi[$fieldName]) || trim((string) $spesifikasi[$fieldName]) === '')) {
                        $v->errors()->add(
                            "spesifikasi_min.{$fieldName}",
                            "Spesifikasi '{$fieldName}' wajib diisi untuk kategori material {$category->nama}."
                        );
                    }
                }
            }
        });
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'categories_id_categories.required' => 'Kategori material wajib dipilih.',
            'categories_id_categories.exists' => 'Kategori material yang dipilih tidak valid.',
            'volume_kg.required' => 'Volume kebutuhan wajib diisi.',
            'volume_kg.min' => 'Volume kebutuhan minimal 0.01 kg.',
            'harga_maks.min' => 'Harga maksimal tidak boleh bernilai negatif.',
            'berlaku_sampai.after_or_equal' => 'Tanggal batas berlaku tidak boleh di masa lampau.',
            'frekuensi.required' => 'Frekuensi pasokan wajib dipilih.',
            'frekuensi.in' => 'Pilihan frekuensi tidak valid.',
        ];
    }
}

<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesListingSpecification;
use App\Models\Category;
use App\Models\Listing;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateListingRequest extends FormRequest
{
    use ValidatesListingSpecification;

    public function authorize(): bool
    {
        /** @var Listing|null $listing */
        $listing = $this->route('listing');

        return $listing !== null && ($this->user()?->can('update', $listing) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'judul' => ['required', 'string', 'max:255'],
            'categories_id_categories' => ['required', 'integer', 'exists:categories,id_categories'],
            'volume_kg' => ['required', 'numeric', 'min:0.01'],
            'stok_kg' => ['required', 'numeric', 'min:0', 'lte:volume_kg'],
            'harga_per_kg' => ['required', 'numeric', 'min:0'],
            'biaya_buang_per_kg' => ['nullable', 'numeric', 'min:0'],
            'metode_buang_sebelumnya' => ['nullable', 'string', 'max:100'],
            'lokasi' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:DRAF,PUBLISHED,DIARSIPKAN'],
            'spesifikasi' => ['nullable', 'array'],
            'surat_non_b3' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'foto_sampel' => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:5120'],
            'tanggal_pengambilan_sampel' => ['nullable', 'date'],
            'sertifikat_lab' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'judul.required' => 'Judul listing wajib diisi.',
            'categories_id_categories.required' => 'Kategori material wajib dipilih.',
            'categories_id_categories.exists' => 'Kategori material yang dipilih tidak valid.',
            'volume_kg.required' => 'Volume material wajib diisi.',
            'stok_kg.required' => 'Stok material wajib diisi.',
            'stok_kg.lte' => 'Stok tersedia tidak boleh melebihi volume total.',
            'harga_per_kg.required' => 'Harga per kg wajib diisi.',
            'lokasi.required' => 'Lokasi asal material wajib diisi.',
            'status.required' => 'Status listing wajib dipilih.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $categoryId = $this->input('categories_id_categories');
            if (! $categoryId) {
                return;
            }

            /** @var Category|null $category */
            $category = Category::find($categoryId);
            if (! $category) {
                return;
            }

            if ((int) $category->is_b3_prohibited === 1) {
                $validator->errors()->add(
                    'categories_id_categories',
                    'Kategori material ini termasuk limbah B3 yang dilarang pada platform SymbioNusa.'
                );
            }

            // Draf boleh belum lengkap; spesifikasi wajib ditegakkan saat publikasi.
            if ($this->input('status') === 'PUBLISHED') {
                $this->validateRequiredSpecifications($validator, $category);
            }

            /** @var Listing|null $listing */
            $listing = $this->route('listing');

            $replacesAttachment = $this->hasFile('surat_non_b3') || $this->hasFile('foto_sampel') || $this->hasFile('sertifikat_lab');
            if ($listing && $replacesAttachment && $listing->isUnderInspection()) {
                $validator->errors()->add(
                    'surat_non_b3',
                    'Lampiran bukti tidak dapat diganti selama listing sedang dalam proses inspeksi.'
                );
            }

            if ($this->input('status') === 'PUBLISHED' && $listing) {
                $hasSurat = $this->hasFile('surat_non_b3')
                    || $listing->attachments()->where('jenis', 'SURAT_NON_B3')->where('status', 'AKTIF')->exists();

                if (! $hasSurat) {
                    $validator->errors()->add(
                        'surat_non_b3',
                        'Surat pernyataan material non-B3 wajib diunggah untuk mempublikasikan listing.'
                    );
                }

                if ((int) $category->wajib_sertifikat_lab === 1) {
                    $hasLab = $this->hasFile('sertifikat_lab')
                        || $listing->attachments()->where('jenis', 'SERTIFIKAT_LAB')->where('status', 'AKTIF')->exists();

                    if (! $hasLab) {
                        $validator->errors()->add(
                            'sertifikat_lab',
                            'Kategori ini mewajibkan lampiran sertifikat uji laboratorium yang masih berlaku.'
                        );
                    }
                }
            }
        });
    }
}

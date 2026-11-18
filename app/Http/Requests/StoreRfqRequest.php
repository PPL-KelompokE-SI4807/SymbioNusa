<?php

namespace App\Http\Requests;

use App\Models\Listing;
use App\Models\RfqContract;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreRfqRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', RfqContract::class) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'listings_id_listings' => ['required', 'integer', 'exists:listings,id_listings'],
            'volume_kg' => ['required', 'numeric', 'gt:0'],
            'harga_per_kg' => ['required', 'numeric', 'gt:0'],
            'tenggat_hari' => ['nullable', 'integer', 'min:1', 'max:7'],
            'catatan' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $listingId = $this->input('listings_id_listings');
            if (! $listingId) {
                return;
            }

            /** @var Listing|null $listing */
            $listing = Listing::find($listingId);
            if (! $listing) {
                $validator->errors()->add('listings_id_listings', 'Listing material tidak ditemukan.');

                return;
            }

            // Aturan 1: Listing harus berstatus PUBLISHED
            if (! $listing->isPublished()) {
                $validator->errors()->add('listings_id_listings', 'Hanya listing yang telah dipublikasikan yang dapat diajukan penawaran.');
            }

            // Aturan 2: Seller dan Buyer tidak boleh entitas yang sama
            if ($this->user()?->id_company && $listing->companies_id_company === $this->user()->id_company) {
                $validator->errors()->add('listings_id_listings', 'Anda tidak dapat mengajukan RFQ pada listing milik perusahaan sendiri.');
            }

            // Aturan 3: Volume yang diajukan tidak boleh melebihi stok yang tersedia
            $requestedVolume = (float) $this->input('volume_kg', 0);
            if ($requestedVolume > (float) $listing->stok_kg) {
                $validator->errors()->add('volume_kg', 'Volume yang diajukan melebihi stok tersedia (maksimal '.number_format((float) $listing->stok_kg, 2).' kg).');
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'listings_id_listings.required' => 'Listing material wajib dipilih.',
            'listings_id_listings.exists' => 'Listing material tidak ditemukan di katalog.',
            'volume_kg.required' => 'Volume pengajuan (kg) wajib diisi.',
            'volume_kg.numeric' => 'Volume pengajuan harus berupa angka.',
            'volume_kg.gt' => 'Volume pengajuan harus lebih besar dari 0.',
            'harga_per_kg.required' => 'Harga penawaran per kg wajib diisi.',
            'harga_per_kg.numeric' => 'Harga penawaran harus berupa angka.',
            'harga_per_kg.gt' => 'Harga penawaran harus lebih besar dari 0.',
            'tenggat_hari.integer' => 'Tenggat hari harus berupa bilangan bulat.',
            'tenggat_hari.min' => 'Tenggat negosiasi minimal 1 hari.',
            'tenggat_hari.max' => 'Tenggat negosiasi maksimal 7 hari.',
            'catatan.max' => 'Catatan penawaran maksimal 255 karakter.',
        ];
    }
}

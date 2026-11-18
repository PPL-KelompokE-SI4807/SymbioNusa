<?php

namespace App\Http\Requests;

use App\Models\Listing;
use App\Models\RfqContract;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class CounterOfferRfqRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var RfqContract|null $rfq */
        $rfq = $this->route('rfq');

        return $rfq !== null && ($this->user()?->can('counter', $rfq) ?? false);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'volume_kg' => ['required', 'numeric', 'gt:0'],
            'harga_per_kg' => ['required', 'numeric', 'gt:0'],
            'catatan' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            /** @var RfqContract|null $rfq */
            $rfq = $this->route('rfq');
            if (! $rfq) {
                return;
            }

            $listing = $rfq->listing;
            if ($listing instanceof Listing) {
                $requestedVolume = (float) $this->input('volume_kg', 0);
                if ($requestedVolume > (float) $listing->stok_kg) {
                    $validator->errors()->add('volume_kg', 'Volume penawaran balik melebihi stok tersedia (maksimal '.number_format((float) $listing->stok_kg, 2).' kg).');
                }
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'volume_kg.required' => 'Volume penawaran balik (kg) wajib diisi.',
            'volume_kg.numeric' => 'Volume penawaran balik harus berupa angka.',
            'volume_kg.gt' => 'Volume penawaran balik harus lebih besar dari 0.',
            'harga_per_kg.required' => 'Harga penawaran balik per kg wajib diisi.',
            'harga_per_kg.numeric' => 'Harga penawaran balik harus berupa angka.',
            'harga_per_kg.gt' => 'Harga penawaran balik harus lebih besar dari 0.',
            'catatan.max' => 'Catatan penawaran balik maksimal 255 karakter.',
        ];
    }
}

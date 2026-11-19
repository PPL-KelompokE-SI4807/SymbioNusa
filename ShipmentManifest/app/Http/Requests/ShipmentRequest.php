<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ShipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // otorisasi dilakukan Policy di controller
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('plat')) {
            $this->merge(['plat' => strtoupper(preg_replace('/\s+/', ' ', trim($this->input('plat'))))]);
        }
    }

    public function rules(): array
    {
        $isCreate = $this->isMethod('post');
        $maxKb = (int) config('symbionusa.upload_max_kb');
        $maxDocs = (int) config('symbionusa.shipment_max_documents');

        return [
            'purchase_order_id' => [Rule::requiredIf($isCreate), 'nullable', 'integer', 'exists:purchase_orders,id'],
            'armada' => ['required', 'string', 'max:100'],
            'plat' => ['required', 'string', 'max:20', 'regex:/^[A-Z]{1,2}\s?\d{1,4}\s?[A-Z]{0,3}$/'],
            'sopir' => ['required', 'string', 'max:100'],
            'kontak_sopir' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s]{6,20}$/'],
            'jadwal_berangkat' => ['required', 'date'],
            'estimasi_tiba' => ['required', 'date', 'after_or_equal:jadwal_berangkat'],
            'rute_asal' => ['required', 'string', 'max:255'],
            'rute_tujuan' => ['required', 'string', 'max:255'],
            'jarak_km' => ['nullable', 'numeric', 'min:0', 'max:99999'],
            'berat_dikirim_kg' => ['required', 'numeric', 'gt:0', 'max:999999999'],
            'alasan_di_bawah_batas' => ['nullable', 'string', 'max:500'],
            'catatan' => ['nullable', 'string', 'max:1000'],
            'dokumen' => ['nullable', 'array', "max:{$maxDocs}"],
            'dokumen.*' => ['file', 'mimes:pdf,jpg,jpeg,png', "max:{$maxKb}"],
        ];
    }

    public function attributes(): array
    {
        return [
            'purchase_order_id' => 'order',
            'armada' => 'armada',
            'plat' => 'plat nomor',
            'sopir' => 'nama sopir',
            'kontak_sopir' => 'kontak sopir',
            'jadwal_berangkat' => 'jadwal berangkat',
            'estimasi_tiba' => 'estimasi tiba',
            'rute_asal' => 'rute asal',
            'rute_tujuan' => 'rute tujuan',
            'jarak_km' => 'jarak',
            'berat_dikirim_kg' => 'berat dikirim',
            'alasan_di_bawah_batas' => 'alasan',
            'dokumen.*' => 'surat jalan',
        ];
    }

    public function messages(): array
    {
        return [
            'plat.regex' => 'Format plat nomor tidak valid (contoh: D 1234 ABC).',
            'kontak_sopir.regex' => 'Kontak sopir hanya boleh berisi angka, spasi, + atau -.',
            'dokumen.*.mimes' => 'Surat jalan harus berformat PDF, JPG, atau PNG.',
            'dokumen.*.max' => 'Ukuran tiap surat jalan maksimal 5 MB.',
            'estimasi_tiba.after_or_equal' => 'Estimasi tiba tidak boleh lebih awal dari jadwal berangkat.',
        ];
    }
}

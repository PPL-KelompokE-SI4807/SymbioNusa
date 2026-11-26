<?php

namespace App\Http\Requests;

use App\Services\Shipment\ShipmentService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Validasi form pengiriman PBI-11 (SYM-15). Otorisasi lewat ShipmentManifestPolicy di controller. */
class ShipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('plat')) {
            $this->merge(['plat' => strtoupper((string) preg_replace('/\s+/', ' ', trim((string) $this->input('plat'))))]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $isCreate = $this->isMethod('post');

        return [
            'purchase_orders_id_purchase_order' => [Rule::requiredIf($isCreate), 'nullable', 'integer', 'exists:purchase_orders,id_purchase_order'],
            'armada' => ['required', 'string', 'max:100'],
            'plat' => ['required', 'string', 'max:20', 'regex:/^[A-Z]{1,2}\s?\d{1,4}\s?[A-Z]{0,3}$/'],
            'sopir' => ['required', 'string', 'max:100'],
            'jadwal' => ['required', 'date'],
            'rute' => ['required', 'string', 'max:255'],
            'berat_dikirim_kg' => ['required', 'numeric', 'gt:0', 'max:9999999999'],
            'alasan_berat' => ['nullable', 'string', 'max:500'],
            'aksi' => ['nullable', Rule::in(['draf', 'terbitkan'])],
            'dokumen' => ['nullable', 'array', 'max:'.ShipmentService::MAX_DOCUMENTS],
            'dokumen.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:'.ShipmentService::MAX_UPLOAD_KB],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'purchase_orders_id_purchase_order' => 'order',
            'plat' => 'nomor pelat',
            'sopir' => 'nama sopir',
            'jadwal' => 'jadwal berangkat',
            'berat_dikirim_kg' => 'berat dikirim',
            'alasan_berat' => 'alasan berat kurang',
            'dokumen.*' => 'surat jalan',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'plat.regex' => 'Format nomor pelat tidak valid (contoh: D 1234 ABC).',
            'dokumen.max' => 'Maksimal '.ShipmentService::MAX_DOCUMENTS.' surat jalan per pengiriman.',
            'dokumen.*.mimes' => 'Surat jalan harus berformat PDF, JPG, atau PNG.',
            'dokumen.*.max' => 'Ukuran tiap surat jalan maksimal 5 MB.',
        ];
    }

    public function wantsIssue(): bool
    {
        return $this->input('aksi') === 'terbitkan';
    }
}

<?php

namespace App\Http\Requests;

use App\Models\Payment;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class VerifyPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Payment|null $payment */
        $payment = $this->route('payment');

        if (! $payment instanceof Payment) {
            return false;
        }

        return $this->user()?->can('verify', $payment) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', 'in:VALID,DITOLAK'],
            'alasan_tolak' => ['required_if:status,DITOLAK', 'nullable', 'string', 'max:512'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'status.required' => 'Status verifikasi pembayaran wajib dipilih.',
            'status.in' => 'Status verifikasi pembayaran hanya boleh VALID atau DITOLAK.',
            'alasan_tolak.required_if' => 'Alasan penolakan wajib diisi jika pembayaran ditolak.',
            'alasan_tolak.max' => 'Alasan penolakan tidak boleh lebih dari 512 karakter.',
        ];
    }
}

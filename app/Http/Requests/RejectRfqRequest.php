<?php

namespace App\Http\Requests;

use App\Models\RfqContract;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RejectRfqRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var RfqContract|null $rfq */
        $rfq = $this->route('rfq');

        return $rfq !== null && ($this->user()?->can('respond', $rfq) ?? false);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'alasan_penolakan' => ['required', 'string', 'min:3', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'alasan_penolakan.required' => 'Alasan penolakan wajib diisi.',
            'alasan_penolakan.min' => 'Alasan penolakan minimal 3 karakter.',
            'alasan_penolakan.max' => 'Alasan penolakan maksimal 255 karakter.',
        ];
    }
}

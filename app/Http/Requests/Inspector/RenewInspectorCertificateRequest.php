<?php

namespace App\Http\Requests\Inspector;

use App\Models\InspectorProfile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Perpanjang sertifikat: masa berlaku baru wajib, nomor dan berkas baru opsional.
 */
class RenewInspectorCertificateRequest extends FormRequest
{
    public function authorize(): bool
    {
        $profile = $this->route('inspector');

        return $profile instanceof InspectorProfile
            && ($this->user()?->can('renew', $profile) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var InspectorProfile $profile */
        $profile = $this->route('inspector');

        return [
            'berlaku_sampai' => ['required', 'date', 'after:'.InspectorProfile::today()->toDateString()],
            'no_sertifikat' => [
                'required', 'string', 'max:100',
                Rule::unique('inspector_profiles', 'no_sertifikat')->ignore($profile->id_inspector, 'id_inspector'),
            ],
            'sertifikat' => InspectorRules::certificateFile(required: false),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return InspectorRules::messages();
    }
}

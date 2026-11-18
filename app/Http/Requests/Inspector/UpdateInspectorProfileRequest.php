<?php

namespace App\Http\Requests\Inspector;

use App\Models\InspectorProfile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Ubah kompetensi dan identitas sertifikat. Masa berlaku diubah lewat Perpanjang.
 */
class UpdateInspectorProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        $profile = $this->route('inspector');

        return $profile instanceof InspectorProfile
            && ($this->user()?->can('update', $profile) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var InspectorProfile $profile */
        $profile = $this->route('inspector');

        return [
            ...InspectorRules::competency(),
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

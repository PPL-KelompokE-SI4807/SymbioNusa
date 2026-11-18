<?php

namespace App\Http\Requests\Inspector;

use App\Models\InspectorProfile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInspectorProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', InspectorProfile::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'id_user' => [
                'required',
                'integer',
                Rule::exists('users', 'id_user')->where('role', 'INSPECTOR')->where('status', 'AKTIF'),
                Rule::unique('inspector_profiles', 'id_user'),
            ],
            ...InspectorRules::competency(),
            'no_sertifikat' => ['required', 'string', 'max:100', Rule::unique('inspector_profiles', 'no_sertifikat')],
            'berlaku_sampai' => ['required', 'date', 'after:'.InspectorProfile::today()->toDateString()],
            'sertifikat' => InspectorRules::certificateFile(required: true),
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

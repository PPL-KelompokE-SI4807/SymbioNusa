<?php

namespace App\Http\Requests\Inspector;

use App\Models\InspectorProfile;
use Illuminate\Foundation\Http\FormRequest;

class DeactivateInspectorRequest extends FormRequest
{
    public function authorize(): bool
    {
        $profile = $this->route('inspector');

        return $profile instanceof InspectorProfile
            && ($this->user()?->can('deactivate', $profile) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'alasan' => ['required', 'string', 'min:10', 'max:500'],
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

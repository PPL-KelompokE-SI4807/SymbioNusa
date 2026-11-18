<?php

namespace App\Http\Requests\Inspector;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Inspector mengunggah berkas sertifikat perpanjangan untuk profilnya sendiri (UC-33).
 * Masa berlaku dan status tetap diubah Admin.
 */
class UploadOwnCertificateRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Route berada di grup middleware auth, jadi user selalu ada.
        $user = $this->user();
        $profile = $user->inspectorProfile;

        return $profile !== null && $user->can('uploadOwnCertificate', $profile);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
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

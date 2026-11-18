{{--
    Unggah berkas sertifikat (name="sertifikat"). Form induk wajib enctype="multipart/form-data".
    $label, $helper, $required.
--}}
@php
    $invalid = $errors->has('sertifikat');
@endphp

<x-ui.field :label="$label" for="sertifikat" name="sertifikat" :helper="$helper" :required="$required">
    <input type="file" id="sertifikat" name="sertifikat" accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png"
        @if ($required) required @endif
        @if ($invalid) aria-invalid="true" @endif
        aria-describedby="sertifikat-helper{{ $invalid ? ' sertifikat-error' : '' }}"
        @class([
            'block w-full rounded-sm border border-dashed bg-background px-3 py-3 text-body-md text-on-surface',
            'file:mr-4 file:h-10 file:cursor-pointer file:rounded-sm file:border-0 file:bg-primary-container file:px-3 file:text-label-md file:text-on-primary-container',
            'hover:file:bg-primary hover:file:text-on-primary',
            'focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus-ring',
            $invalid ? 'border-error' : 'border-outline-input',
        ])>
</x-ui.field>

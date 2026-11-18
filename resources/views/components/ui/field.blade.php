{{--
    Pembungkus field form (DESIGN.md › Forms): label di atas, helper 13 px di bawah, pesan galat merah.
    name: nama input untuk mengambil pesan galat validasi secara otomatis.
    Contoh:
    <x-ui.field label="Nama kategori" for="nama" name="nama" helper="Maksimal 100 karakter." required>
        <x-ui.input id="nama" name="nama" :value="old('nama')" />
    </x-ui.field>
--}}
@props(['label', 'for' => null, 'name' => null, 'helper' => null, 'error' => null, 'required' => false])

@php
    $message = $error ?? ($name ? $errors->first($name) : null);
@endphp

<div {{ $attributes->class(['space-y-1.5']) }}>
    <label @if ($for) for="{{ $for }}" @endif class="block text-label-md text-on-surface">
        {{ $label }}
        @if ($required)
            <span class="text-error" aria-hidden="true">*</span>
            <span class="sr-only">(wajib)</span>
        @endif
    </label>

    {{ $slot }}

    @if ($helper)
        <p @if ($for) id="{{ $for }}-helper" @endif class="text-body-sm text-on-surface-muted">{{ $helper }}</p>
    @endif
    @if ($message)
        <p @if ($for) id="{{ $for }}-error" @endif class="text-body-sm text-error">{{ $message }}</p>
    @endif
</div>

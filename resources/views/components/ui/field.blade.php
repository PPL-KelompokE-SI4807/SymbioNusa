{{--
    Pembungkus field form (DESIGN.md › Forms): label di atas, helper 13 px di bawah, pesan galat merah dengan ikon
    tepat di bawah field yang bermasalah. Slot "aside" untuk tautan di kanan label (mis. "Lupa kata sandi?").
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

<div {{ $attributes->class(['space-y-2']) }}>
    <div class="flex items-baseline justify-between gap-3">
        <label @if ($for) for="{{ $for }}" @endif class="block text-label-md font-semibold text-on-surface">
            {{ $label }}
            @if ($required)
                <span class="text-error" aria-hidden="true">*</span>
                <span class="sr-only">(wajib)</span>
            @endif
        </label>
        @isset($aside)
            {{ $aside }}
        @endisset
    </div>

    {{ $slot }}

    @if ($helper && ! $message)
        <p @if ($for) id="{{ $for }}-helper" @endif class="text-body-sm text-on-surface-subtle">{{ $helper }}</p>
    @endif
    @if ($message)
        <p @if ($for) id="{{ $for }}-error" @endif class="flex items-center gap-1.5 text-body-sm font-medium text-error">
            <x-ui.icon name="alert" class="size-4 shrink-0" />
            {{ $message }}
        </p>
    @endif
</div>

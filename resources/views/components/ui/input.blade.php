{{--
    Input teks (DESIGN.md › input-field, amandemen FND v8): tinggi 44 px, radius 10 px, border outline-input (≥ 3:1).
    Otomatis ditandai tidak valid bila ada galat validasi untuk name-nya (atau atur lewat prop invalid).
    icon: nama ikon x-ui.icon di sisi kiri (mis. "mail", "lock"), opsional.
--}}
@props(['name' => null, 'type' => 'text', 'icon' => null, 'invalid' => null])

@php
    // invalid=false menonaktifkan penanda galat otomatis (mis. galat kredensial yang ditampilkan sebagai alert).
    $invalid = $invalid ?? ($name && $errors->has($name));
    $classes = [
        'block h-11 w-full rounded-sm border bg-surface text-body-md text-on-surface placeholder:text-on-surface-subtle',
        'focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus-ring',
        'disabled:cursor-not-allowed disabled:bg-surface-muted disabled:opacity-50',
        $invalid ? 'border-error' : 'border-outline-input',
        $icon ? 'pl-11 pr-3.5' : 'px-3.5',
    ];
@endphp

@if ($icon)
    <div class="relative">
        <x-ui.icon :name="$icon" class="pointer-events-none absolute top-1/2 left-3.5 size-5 -translate-y-1/2 text-on-surface-subtle" />
        <input type="{{ $type }}" @if ($name) name="{{ $name }}" @endif @if ($invalid) aria-invalid="true" @endif
               {{ $attributes->class($classes) }}>
    </div>
@else
    <input type="{{ $type }}" @if ($name) name="{{ $name }}" @endif @if ($invalid) aria-invalid="true" @endif
           {{ $attributes->class($classes) }}>
@endif

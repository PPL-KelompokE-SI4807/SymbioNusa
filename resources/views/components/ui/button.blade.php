{{--
    Tombol SymbioNusa (DESIGN.md › Components › Buttons).
    variant: primary | secondary | danger | text | text-danger
    Dengan atribut href, dirender sebagai <a>; tanpa href sebagai <button>.
    Contoh: <x-ui.button>Simpan</x-ui.button>
            <x-ui.button variant="secondary" href="{{ route('dashboard') }}">Kembali</x-ui.button>
--}}
@props(['variant' => 'primary', 'type' => 'button', 'href' => null])

@php
    $variantClasses = match ($variant) {
        'secondary' => 'h-10 px-3 bg-surface text-on-surface border border-outline-input hover:bg-background',
        'danger' => 'h-10 px-3 bg-surface text-error border border-error hover:bg-error-container',
        'text' => 'text-primary hover:underline',
        'text-danger' => 'text-error hover:underline',
        default => 'h-10 px-3 bg-primary text-on-primary hover:bg-primary-hover',
    };

    $classes = [
        'inline-flex items-center justify-center gap-2 rounded-sm text-label-md whitespace-nowrap transition-colors',
        'focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus-ring',
        'disabled:cursor-not-allowed disabled:opacity-50 aria-disabled:cursor-not-allowed aria-disabled:opacity-50',
        $variantClasses,
    ];
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->class($classes) }}>{{ $slot }}</button>
@endif

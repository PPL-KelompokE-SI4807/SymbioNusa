{{--
    Tombol SymbioNusa (DESIGN.md › Components › Buttons, amandemen FND v8).
    variant: primary | secondary | danger | text | text-danger
    size: md (40 px, bawaan) | lg (44 px) | xl (48 px, form autentikasi)
    Satu tombol primary per wilayah; di daftar hanya aksi baris teratas yang primary.
    Dengan atribut href, dirender sebagai <a>; tanpa href sebagai <button>.
--}}
@props(['variant' => 'primary', 'type' => 'button', 'href' => null, 'size' => 'md'])

@php
    $height = match ($size) {
        'lg' => 'h-11 px-5',
        'xl' => 'h-12 px-5 text-body-lg',
        default => 'h-10 px-4',
    };
    $variantClasses = match ($variant) {
        'secondary' => $height.' bg-surface text-on-surface border border-outline-control hover:bg-surface-muted',
        'danger' => $height.' bg-surface text-error border border-error hover:bg-error-container',
        'text' => 'text-primary hover:underline underline-offset-4',
        'text-danger' => 'text-error hover:underline underline-offset-4',
        default => $height.' bg-primary text-on-primary hover:bg-primary-hover',
    };

    $classes = [
        'inline-flex items-center justify-center gap-2 rounded-sm text-label-md font-semibold whitespace-nowrap transition-colors',
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

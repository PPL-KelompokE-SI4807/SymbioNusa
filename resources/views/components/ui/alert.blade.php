{{--
    Pesan umpan balik (sukses, peringatan, galat, info) memakai pasangan warna status DESIGN.md.
    variant: success | warning | danger | info
--}}
@props(['variant' => 'info', 'title' => null])

@php
    $variantClasses = match ($variant) {
        'success' => 'bg-success-container text-on-success-container',
        'warning' => 'bg-warning-container text-on-warning-container',
        'danger' => 'bg-error-container text-on-error-container',
        default => 'bg-info-container text-on-info-container',
    };
    $icon = match ($variant) {
        'success' => 'check',
        'warning' => 'alert',
        'danger' => 'x-circle',
        default => 'info',
    };
@endphp

<div {{ $attributes->class(['flex items-start gap-3 rounded-md px-4 py-3 text-body-md', $variantClasses]) }}
     role="{{ $variant === 'danger' ? 'alert' : 'status' }}">
    <x-ui.icon :name="$icon" class="mt-0.5 size-5 shrink-0" />
    <div class="min-w-0">
        @if ($title)
            <p class="font-semibold">{{ $title }}</p>
        @endif
        {{ $slot }}
    </div>
</div>

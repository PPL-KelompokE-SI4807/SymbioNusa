{{--
    Badge status (DESIGN.md › Status badges). Selalu berisi teks, tidak pernah warna saja.
    variant: success | warning | danger | info | neutral — ambil dari docs/design/prompts/03_STATUS_MAP.md.
    Contoh: <x-ui.badge :variant="$status->badge()">{{ $status->label() }}</x-ui.badge>
--}}
@props(['variant' => 'neutral'])

@php
    $variantClasses = match ($variant) {
        'success' => 'bg-success-container text-on-success-container',
        'warning' => 'bg-warning-container text-on-warning-container',
        'danger' => 'bg-error-container text-on-error-container',
        'info' => 'bg-info-container text-on-info-container',
        default => 'bg-neutral-container text-on-neutral-container',
    };
@endphp

<span {{ $attributes->class(['inline-flex items-center whitespace-nowrap rounded-full px-2 py-0.5 text-label-sm', $variantClasses]) }}>
    {{ $slot }}
</span>

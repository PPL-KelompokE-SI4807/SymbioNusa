{{--
    Pesan umpan balik (DESIGN.md › Components › Alerts, amandemen FND v8): wadah terang + garis 1 px
    dengan rona yang sama, ikon polos 20 px (tanpa kotak ikon), judul tebal.
    variant: success | warning | danger | info
--}}
@props(['variant' => 'info', 'title' => null])

@php
    $variantClasses = match ($variant) {
        'success' => 'bg-success-container text-on-success-container border-success-line',
        'warning' => 'bg-warning-container text-on-warning-container border-warning-line',
        'danger' => 'bg-error-container text-on-error-container border-error-line',
        default => 'bg-info-container text-on-info-container border-info-line',
    };
    $iconColor = match ($variant) {
        'success' => 'text-success',
        'warning' => 'text-warning',
        'danger' => 'text-error',
        default => 'text-info',
    };
    $icon = match ($variant) {
        'success' => 'check-circle',
        'warning' => 'alert',
        'danger' => 'alert',
        default => 'info',
    };
@endphp

<div {{ $attributes->class(['flex items-start gap-3 rounded-md border px-4 py-3 text-body-md', $variantClasses]) }}
     role="{{ $variant === 'danger' ? 'alert' : 'status' }}">
    <x-ui.icon :name="$icon" class="mt-px size-5 shrink-0 {{ $iconColor }}" />
    <div class="min-w-0">
        @if ($title)
            <p class="font-semibold">{{ $title }}</p>
        @endif
        {{ $slot }}
    </div>
</div>

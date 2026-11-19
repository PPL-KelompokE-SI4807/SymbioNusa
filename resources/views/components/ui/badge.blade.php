{{--
    Status indicator (DESIGN.md › Components › Status indicator, amandemen FND v8).
    Glyph 16 px + label teks; bentuk dan warna mengikuti variant dari docs/design/prompts/03_STATUS_MAP.md:
      warning = cincin putus-putus (menunggu seseorang), info = cincin setengah terisi (berjalan),
      success = lingkaran penuh bercentang, danger = lingkaran penuh bersilang, neutral = lingkaran penuh bergaris.
    API lama tetap: <x-ui.badge :variant="$status->badge()">{{ $status->label() }}</x-ui.badge>
--}}
@props(['variant' => 'neutral'])

@php
    $variant = in_array($variant, ['success', 'warning', 'danger', 'info', 'neutral'], true) ? $variant : 'neutral';
    $glyphColor = match ($variant) {
        'success' => 'text-success',
        'warning' => 'text-warning',
        'danger' => 'text-error',
        'info' => 'text-info',
        default => 'text-on-surface-subtle',
    };
@endphp

<span {{ $attributes->class(['inline-flex items-center gap-2 whitespace-nowrap text-label-md text-on-surface']) }}>
    <svg class="size-4 shrink-0 {{ $glyphColor }}" viewBox="0 0 16 16" aria-hidden="true" focusable="false">
        @switch($variant)
            @case('warning')
                <circle cx="8" cy="8" r="6.25" fill="none" stroke="currentColor" stroke-width="1.75" stroke-dasharray="2.6 2.2" />
                @break
            @case('info')
                <circle cx="8" cy="8" r="6.25" fill="none" stroke="currentColor" stroke-width="1.75" />
                <path d="M8 4 A4 4 0 0 1 8 12 Z" fill="currentColor" />
                @break
            @default
                <circle cx="8" cy="8" r="8" fill="currentColor" />
                @if ($variant === 'success')
                    <path d="M4.7 8.3 L7 10.6 L11.3 5.7" fill="none" class="stroke-surface" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" />
                @elseif ($variant === 'danger')
                    <path d="M5.3 5.3 L10.7 10.7 M10.7 5.3 L5.3 10.7" fill="none" class="stroke-surface" stroke-width="1.75" stroke-linecap="round" />
                @else
                    <path d="M5 8 L11 8" fill="none" class="stroke-surface" stroke-width="1.75" stroke-linecap="round" />
                @endif
        @endswitch
    </svg>
    <span>{{ $slot }}</span>
</span>

{{--
    Tile KPI (DESIGN.md › Components › KPI tile, amandemen FND v8). Tanpa ikon, lingkaran, atau sparkline.
    tone memberi arti warna:
      sun    = tindakan milik pengguna sendiri dengan jumlah > 0
      sky    = sedang berjalan / menunggu pihak lain
      green  = selesai atau positif
      forest = ringkasan netral di bidang gelap
      plain  = hitungan netral (permukaan putih)
      zero   = nilai nol (permukaan putih, angka hijau) — dipakai otomatis bila zero="true"
    Contoh: <x-ui.kpi label="RFQ belum dibalas" value="2" caption="Tenggat terdekat besok" tone="sun" />
--}}
@props(['label', 'value', 'unit' => null, 'caption' => null, 'exact' => null, 'tone' => 'plain', 'zero' => false])

@php
    $tone = $zero ? 'zero' : $tone;
    $field = match ($tone) {
        'sun' => 'bg-linear-to-br from-tile-sun-from to-tile-sun-to text-on-tile-sun',
        'green' => 'bg-linear-to-br from-tile-green-from to-tile-green-to text-on-tile',
        'sky' => 'bg-linear-to-br from-tile-sky-from to-tile-sky-to text-on-tile',
        'forest' => 'bg-linear-to-br from-tile-forest-from to-tile-forest-to text-on-tile',
        default => 'bg-surface text-on-surface shadow-card',
    };
    $muted = in_array($tone, ['plain', 'zero'], true);
@endphp

<div {{ $attributes->class(['flex min-h-32 flex-col rounded-lg px-5 py-4', $field]) }}>
    <p @class(['text-label-md', 'text-on-surface-muted' => $muted])>{{ $label }}</p>
    <p class="mt-auto flex items-baseline gap-1.5 pt-3 tabular-nums" @if ($exact) title="{{ $exact }}" @endif>
        <span @class(['text-display-metric', 'text-primary' => $tone === 'zero'])>{{ $value }}</span>
        @if ($unit)
            <span class="text-body-lg font-medium">{{ $unit }}</span>
        @endif
    </p>
    @if ($caption)
        <p @class(['mt-1 text-body-sm', 'text-on-surface-subtle' => $muted, 'opacity-90' => ! $muted])>{{ $caption }}</p>
    @endif
</div>

{{--
    Satu metrik di dalam x-ui.summary-strip (DESIGN.md › Summary strip; panduan Senja §7.4). Bukan tile: tanpa latar
    sendiri, tanpa tint, tanpa ikon. Label 13 px meta, nilai 32 px tabular, satu baris keterangan di bawahnya.
    tone: plain (bawaan) | warning | danger | success — hanya mewarnai NILAI bila maknanya memang semantik;
    permukaan tetap putih. Nilai tone lama (sun, sky, green, forest, zero) diterima dan dipetakan ke plain/success.
    Contoh: <x-ui.kpi label="Perlu verifikasi" value="2" caption="Bukti bayar masuk" tone="warning" />
--}}
@props(['label', 'value', 'unit' => null, 'caption' => null, 'exact' => null, 'tone' => 'plain', 'zero' => false])

@php
    $valueColor = match ($tone) {
        'warning' => 'text-warning',
        'danger' => 'text-error',
        'success' => 'text-success',
        default => 'text-on-surface',
    };
@endphp

<div {{ $attributes->class(['flex min-w-0 flex-col bg-surface p-[18px] sm:px-6 sm:py-5']) }}>
    <dt class="text-label-md font-semibold text-on-surface-muted">{{ $label }}</dt>
    <dd class="mt-1.5 flex items-baseline gap-1.5 tabular-nums" @if ($exact) title="{{ $exact }}" @endif>
        <span @class(['text-dash-metric', $valueColor])>{{ $value }}</span>
        @if ($unit)
            <span class="text-body-md font-bold text-on-surface-muted">{{ $unit }}</span>
        @endif
    </dd>
    @if ($caption)
        <dd class="mt-1.5 text-body-sm font-medium text-on-surface-muted">{{ $caption }}</dd>
    @endif
</div>

{{--
    Kartu KPI (DESIGN.md › KPI card): label redup di atas, nilai 28 px tabular satu baris, keterangan di bawah.
    Pakai bentuk ringkas untuk nilai ("Rp 214,6 jt") dan isi exact untuk nilai lengkap (tooltip).
--}}
@props(['label', 'value', 'caption' => null, 'exact' => null])

<div {{ $attributes->class(['rounded-md border border-outline bg-surface p-5']) }}>
    <p class="text-body-sm text-on-surface-muted">{{ $label }}</p>
    <p class="mt-2 truncate text-display-metric tabular-nums text-on-surface" @if ($exact) title="{{ $exact }}" @endif>{{ $value }}</p>
    @if ($caption)
        <p class="mt-1 text-body-sm text-on-surface-muted">{{ $caption }}</p>
    @endif
</div>

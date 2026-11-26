{{--
    Strip ringkasan (DESIGN.md › Components › Summary strip; panduan Senja §7.4): SATU permukaan putih berisi 2–4
    metrik x-ui.kpi yang dipisah garis rambut (silang 2×2 di layar sempit), tanpa tile, tint, atau ikon per metrik.
    Di dalam x-app.kaca permukaannya menjadi kaca (data-card) dan sel-selnya bening dengan garis rambut (data-summary).
    label: nama wilayah untuk pembaca layar. cols: jumlah kolom di layar lebar (2, 3, atau 4).
    Contoh:
    <x-ui.summary-strip label="Ringkasan kategori">
        <x-ui.kpi label="Total kategori" value="12" />
        <x-ui.kpi label="Aktif" value="9" />
    </x-ui.summary-strip>
--}}
@props(['label' => null, 'cols' => 4])

@php
    $grid = match ((int) $cols) {
        2 => 'grid-cols-2',
        3 => 'grid-cols-2 xl:grid-cols-3',
        default => 'grid-cols-2 xl:grid-cols-4',
    };
@endphp

<section data-card data-summary {{ $attributes->class(['overflow-hidden rounded-lg bg-surface shadow-card']) }} @if ($label) aria-label="{{ $label }}" @endif>
    <dl @class(['grid gap-px bg-outline-hairline', $grid])>
        {{ $slot }}
    </dl>
</section>

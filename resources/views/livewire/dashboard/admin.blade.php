{{--
    Dasbor Admin / DLH, varian Kaca (DESIGN.md, amandemen FND v12, keputusan PM 25 Nov 2026). Susunan mengikuti SymbioNusa Lab
    (D:\PPL\SymbioNusa-Lab): strip ringkasan → antrean + inspeksi per jam → Sungai Order → peta → tagihan + aktivitas
    → beban Inspector + tonase → band dampak. Data dari DashboardService::admin()['kaca'] (App\Services\Dashboard\AdminKaca);
    panel yang DDL belum mendukung (perbandingan periode, peta) memakai contoh Lab dan bertanda "Data demo".
    Pembungkus .dash-kaca ada di dashboard-overview.blade.php.
--}}
@php
    $paletteOrders = collect($kaca['river']['stages'])->flatMap(fn ($s) => collect($s['orders'])->take(6)->map(fn ($o) => [
        'code' => $o['code'], 'material' => $o['material'], 'seller' => $o['seller'], 'stage' => $s['label'], 'key' => $s['key'],
    ]))->values()->all();
    $commands = collect([
        ['label' => 'Buka Verifikasi Mitra', 'route' => 'admin.companies.index'],
        ['label' => 'Buka Registri Inspector', 'route' => 'admin.inspectors.index'],
        ['label' => 'Buka Kategori Material', 'route' => 'admin.categories.index'],
    ])->filter(fn ($c) => Route::has($c['route']))->map(fn ($c) => ['label' => $c['label'], 'url' => route($c['route'])])->values()->all();
    $root = ['queue' => $kaca['queue'], 'periods' => $kaca['periods'], 'orders' => $paletteOrders, 'commands' => $commands];
@endphp

<div class="mx-auto max-w-[1680px]" x-data="kacaAdmin({{ Js::from($root) }})" @keydown.window="onKey($event)">
    @include('livewire.dashboard.kaca.header')

    <div class="dash-grid grid gap-4 lg:grid-cols-12">
        <div class="lg:col-span-12" data-rise style="--i: 2">@include('livewire.dashboard.kaca.summary')</div>
        <div class="lg:col-span-7" data-rise style="--i: 3">@include('livewire.dashboard.kaca.queue')</div>
        <div class="lg:col-span-5" data-rise style="--i: 4">@include('livewire.dashboard.kaca.hourly')</div>
        <div class="lg:col-span-12" data-rise style="--i: 5">@include('livewire.dashboard.kaca.river')</div>
        <div class="lg:col-span-12" data-rise style="--i: 6">@include('livewire.dashboard.kaca.region')</div>
        <div class="lg:col-span-6" data-rise style="--i: 7">@include('livewire.dashboard.kaca.aging')</div>
        <div class="lg:col-span-6" data-rise style="--i: 8">@include('livewire.dashboard.kaca.panels', ['only' => 'activity'])</div>
        <div class="lg:col-span-6" data-rise style="--i: 9">@include('livewire.dashboard.kaca.panels', ['only' => 'inspectors'])</div>
        <div class="lg:col-span-6" data-rise style="--i: 10">@include('livewire.dashboard.kaca.panels', ['only' => 'donut'])</div>
        <div class="lg:col-span-12" data-rise style="--i: 11">@include('livewire.dashboard.kaca.panels', ['only' => 'impact'])</div>
    </div>

    <p class="glass mt-6 rounded-[14px] px-4 py-3 text-[12px] font-medium text-ink">
        Dasbor Admin varian Kaca. Angka tahap, antrean, jadwal, tagihan, aktivitas, dan dampak dari data transaksi; perbandingan periode dan peta pengawasan adalah contoh (Data demo).
        Foto material dari Wikimedia Commons (CC BY / CC BY-SA), lihat Kredit foto.
    </p>

    @include('livewire.dashboard.kaca.overlays')
</div>

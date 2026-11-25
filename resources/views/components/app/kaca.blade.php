{{--
    Pembungkus layar Kaca (DESIGN.md FND v12; docs/design/PANDUAN_KACA.md §5). Satu-satunya cara memasang Kaca pada sebuah layar:
    kelas .dash-kaca, latar empat foto material yang diburamkan (ukuran dan posisi tetap, karena kontras diukur di atasnya),
    kilau kaca yang mengikuti kursor pada [data-card], dan tombol jeda gerak latar (WCAG 2.2.2).
    enabled=false merender <div> biasa dengan atribut yang sama, tanpa Kaca (untuk layar yang Kaca-nya bergantung pada peran).
    Contoh:
    <x-app.kaca>
        <x-ui.page-header title="Kategori Material" subtitle="…" />
        <div class="dash-grid grid gap-4 lg:grid-cols-12">…</div>
    </x-app.kaca>
--}}
@props(['enabled' => true])

@php
    // Satu elemen akar dalam segala kondisi: Livewire menempelkan wire:id ke tag pertama, jadi @if tidak boleh membungkus akar.
    $root = $enabled
        ? $attributes->class(['dash-kaca pb-16'])->merge(['x-data' => 'kacaPage', 'x-bind:class' => "{ 'kaca-still': still }", '@pointermove.passive' => 'sheen($event)'])
        : $attributes;
@endphp
<div {{ $root }}>
    @if ($enabled)
        <div class="kaca-backdrop" aria-hidden="true">
            <div class="kaca-blob" style="background-image: url('{{ asset('images/material/kain.jpg') }}'); width: 46vw; height: 52vh; left: -6vw; top: -8vh"></div>
            <div class="kaca-blob" style="background-image: url('{{ asset('images/material/ampas-tahu.jpg') }}'); width: 40vw; height: 46vh; right: -4vw; top: 10vh"></div>
            <div class="kaca-blob" style="background-image: url('{{ asset('images/material/serbuk-kayu.jpg') }}'); width: 52vw; height: 50vh; left: 22vw; bottom: -14vh"></div>
            <div class="kaca-blob" style="background-image: url('{{ asset('images/material/plastik-2.jpg') }}'); width: 30vw; height: 36vh; right: 2vw; bottom: 4vh"></div>
        </div>
    @endif

    {{ $slot }}

    @if ($enabled)
        <button type="button" class="kaca-still-toggle glass" @click="toggleStill()" x-bind:aria-pressed="still.toString()"
                aria-label="Jeda gerak latar" x-bind:title="still ? 'Jalankan lagi gerak latar' : 'Jeda gerak latar'">
            <x-ui.icon name="pause" class="size-[18px]" x-show="! still" />
            <x-ui.icon name="play" class="size-[18px]" x-show="still" x-cloak />
        </button>
    @endif
</div>

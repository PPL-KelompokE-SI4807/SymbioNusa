{{--
    Aksi per baris registri (SCREENS 02): Aktif → Lihat · Ubah · Keluarkan;
    Segera Berakhir → Lihat · Ubah · Perpanjang; Nonaktif → Lihat · Perpanjang.
    $touch: true di tampilan kartu agar target sentuh ≥ 40 px.
--}}
@use('App\Enums\InspectorStatus')

@php
    $size = $touch ? 'min-h-10' : '';
    $nama = $ins->user->nama;
@endphp

<x-ui.button variant="text" href="{{ route('admin.inspectors.show', $ins) }}" class="{{ $size }}" aria-label="Lihat {{ $nama }}">Lihat</x-ui.button>

@if ($status !== InspectorStatus::NONAKTIF)
    <x-ui.button variant="text" href="{{ route('admin.inspectors.edit', $ins) }}" class="{{ $size }}" aria-label="Ubah {{ $nama }}">Ubah</x-ui.button>
@endif

@if ($status === InspectorStatus::AKTIF)
    <x-ui.button variant="text-danger" href="{{ route('admin.inspectors.show', $ins) }}#keluarkan" class="{{ $size }}" aria-label="Keluarkan {{ $nama }}">Keluarkan</x-ui.button>
@else
    <x-ui.button variant="text" href="{{ route('admin.inspectors.renew.form', $ins) }}" class="{{ $size }}" aria-label="Perpanjang sertifikat {{ $nama }}">Perpanjang</x-ui.button>
@endif

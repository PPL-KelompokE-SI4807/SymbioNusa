{{--
    Ikon garis (DESIGN.md › Icons): outline, stroke 1,75, grid 24, warna mengikuti teks (currentColor).
    Path digambar sendiri untuk SymbioNusa (bukan salinan pustaka ikon), jadi tidak butuh paket atau lisensi tambahan.
    Ukuran lewat kelas: size-5 (20 px, bawaan) atau size-4 (16 px di tabel/badge).
    Tambah ikon baru ke $paths; lingkaran digambar sebagai dua busur setengah.
--}}
@props(['name'])

@php
    $circle = 'M3 12 A9 9 0 1 1 21 12 A9 9 0 1 1 3 12 Z';
    $paths = [
        'menu' => 'M4 6 L20 6 M4 12 L20 12 M4 18 L20 18',
        'x' => 'M6 6 L18 18 M18 6 L6 18',
        'check' => 'M5 12.5 L10 17.5 L19 7',
        'alert' => 'M12 4 L21 19.5 L3 19.5 Z M12 10 L12 14 M12 17 L12.01 17',
        'info' => $circle.' M12 11 L12 16 M12 8 L12.01 8',
        'x-circle' => $circle.' M9 9 L15 15 M15 9 L9 15',
        'bell' => 'M6 16.5 L6 11 C6 7.7 8.7 5 12 5 C15.3 5 18 7.7 18 11 L18 16.5 L19.5 18 L4.5 18 Z M10 21 L14 21',
        'logout' => 'M10 4 L5 4 L5 20 L10 20 M14 8 L18 12 L14 16 M18 12 L9 12',
        'chevron-right' => 'M9 6 L15 12 L9 18',
        'home' => 'M3 11 L12 3.5 L21 11 M5.5 9 L5.5 20.5 L18.5 20.5 L18.5 9',
        'search' => 'M4 11 A7 7 0 1 1 18 11 A7 7 0 1 1 4 11 Z M16 16 L20.5 20.5',
        'plus' => 'M12 5 L12 19 M5 12 L19 12',
    ];
@endphp

<svg {{ $attributes->class(['size-5' => ! str_contains($attributes->get('class', ''), 'size-')]) }}
     viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"
     stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
    <path d="{{ $paths[$name] ?? $paths['info'] }}" />
</svg>

{{--
    Ikon garis (DESIGN.md › Icons): outline, stroke 1,75, grid 24, warna mengikuti teks (currentColor).
    Path digambar sendiri untuk SymbioNusa (bukan salinan pustaka ikon), jadi tidak butuh paket atau lisensi tambahan.
    Ikon menu sidebar dan halaman FND v8 ada di daftar ini (grid, shield, layers, chat, receipt, truck, dll.).
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
        'check-circle' => $circle.' M8 12.5 L11 15.5 L16.5 9.5',
        'chevron-down' => 'M6 9 L12 15 L18 9',
        'arrow-right' => 'M5 12 L19 12 M13 6 L19 12 L13 18',
        'arrow-left' => 'M19 12 L5 12 M11 6 L5 12 L11 18',
        'grid' => 'M4 4 L10 4 L10 10 L4 10 Z M14 4 L20 4 L20 10 L14 10 Z M4 14 L10 14 L10 20 L4 20 Z M14 14 L20 14 L20 20 L14 20 Z',
        'shield' => 'M12 3 L19 6 L19 11.5 C19 16 16 19.3 12 21 C8 19.3 5 16 5 11.5 L5 6 Z M9 12 L11.2 14.2 L15.5 9.8',
        'layers' => 'M12 4 L21 8.5 L12 13 L3 8.5 Z M3 12.5 L12 17 L21 12.5 M3 16.5 L12 21 L21 16.5',
        'leaf' => 'M5 19 C5 10.5 10 5.5 20 4 C18.5 14 13.5 19 5 19 Z M5 19 L13 11',
        'building' => 'M5 20.5 L5 4 L15 4 L15 20.5 M15 9 L19 9 L19 20.5 M3 20.5 L21 20.5 M8 8 L8 8.5 M12 8 L12 8.5 M8 12 L8 12.5 M12 12 L12 12.5 M8 16 L8 16.5 M12 16 L12 16.5',
        'clipboard' => 'M8 4.5 L6 4.5 L6 20.5 L18 20.5 L18 4.5 L16 4.5 M9 3 L15 3 L15 6 L9 6 Z M9 12.5 L11 14.5 L15 10.5',
        'calendar' => 'M4 6 L20 6 L20 20.5 L4 20.5 Z M4 10 L20 10 M8 3.5 L8 7.5 M16 3.5 L16 7.5',
        'scale' => 'M12 4 L12 20 M7 20 L17 20 M5 7 L19 7 M5 7 L2.5 13 L7.5 13 Z M19 7 L16.5 13 L21.5 13 Z',
        'eye' => 'M2.5 12 C4.8 7.5 8.1 5.5 12 5.5 C15.9 5.5 19.2 7.5 21.5 12 C19.2 16.5 15.9 18.5 12 18.5 C8.1 18.5 4.8 16.5 2.5 12 Z M9 12 A3 3 0 1 1 15 12 A3 3 0 1 1 9 12 Z',
        'eye-off' => 'M3 3 L21 21 M10.6 6.1 C11 6 11.5 6 12 6 C15.9 6 19.2 8 21.5 12.5 C20.8 13.8 20 14.9 19.1 15.8 M14.1 14.6 C13.6 15.2 12.8 15.5 12 15.5 C10.3 15.5 9 14.2 9 12.5 C9 11.7 9.3 10.9 9.9 10.4 M6.3 7.7 C4.8 8.8 3.5 10.4 2.5 12.5 C4.8 17 8.1 19 12 19 C13.9 19 15.6 18.5 17.1 17.6',
        'star' => 'M12 3.5 L14.6 9 L20.5 9.6 L16 13.6 L17.3 19.5 L12 16.5 L6.7 19.5 L8 13.6 L3.5 9.6 L9.4 9 Z',
        'chat' => 'M4 5 L20 5 L20 16 L9.5 16 L5.5 19.5 L5.5 16 L4 16 Z',
        'file' => 'M6 3.5 L14 3.5 L19 8.5 L19 20.5 L6 20.5 Z M14 3.5 L14 8.5 L19 8.5 M9 13 L16 13 M9 16.5 L14 16.5',
        'receipt' => 'M6 3.5 L18 3.5 L18 20.5 L15 18.5 L12 20.5 L9 18.5 L6 20.5 Z M9 8 L15 8 M9 11.5 L15 11.5 M9 15 L13 15',
        'truck' => 'M3 6 L14 6 L14 16.5 L3 16.5 Z M14 10 L18 10 L21 13 L21 16.5 L14 16.5 M5.5 18.5 A1.5 1.5 0 1 1 8.5 18.5 A1.5 1.5 0 1 1 5.5 18.5 Z M15.5 18.5 A1.5 1.5 0 1 1 18.5 18.5 A1.5 1.5 0 1 1 15.5 18.5 Z',
        'user' => 'M8 8 A4 4 0 1 1 16 8 A4 4 0 1 1 8 8 Z M4.5 20.5 C4.5 16.5 7.8 14 12 14 C16.2 14 19.5 16.5 19.5 20.5',
        'flask' => 'M9 3.5 L15 3.5 M10 3.5 L10 9.5 L4.5 19 L5.5 20.5 L18.5 20.5 L19.5 19 L14 9.5 L14 3.5 M7 15 L17 15',
        'clock' => $circle.' M12 7 L12 12 L15.5 14',
        'map-pin' => 'M12 21 C12 21 5 14.8 5 9.5 C5 5.6 8.1 2.75 12 2.75 C15.9 2.75 19 5.6 19 9.5 C19 14.8 12 21 12 21 Z M9.5 9.5 A2.5 2.5 0 1 1 14.5 9.5 A2.5 2.5 0 1 1 9.5 9.5 Z',
        'mail' => 'M3.5 6 L20.5 6 L20.5 18 L3.5 18 Z M3.5 6.5 L12 13 L20.5 6.5',
        'lock' => 'M5 11 L19 11 L19 20.5 L5 20.5 Z M8 11 L8 7.5 C8 5.3 9.8 3.5 12 3.5 C14.2 3.5 16 5.3 16 7.5 L16 11',
        'loop' => 'M6 9 A7 7 0 0 1 18.5 8 M18.5 8 L18.5 3.5 M18.5 8 L14 8 M18 15 A7 7 0 0 1 5.5 16 M5.5 16 L5.5 20.5 M5.5 16 L10 16',
    ];
@endphp

<svg {{ $attributes->class(['size-5' => ! str_contains($attributes->get('class', ''), 'size-')]) }}
     viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"
     stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
    <path d="{{ $paths[$name] ?? $paths['info'] }}" />
</svg>

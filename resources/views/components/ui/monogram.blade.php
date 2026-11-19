{{--
    Monogram perusahaan (DESIGN.md › Components › Monogram, amandemen FND v8): inisial di atas rona tipis,
    teks dalam nada gelap rona yang sama (≥ 4.5:1). Warna dipilih stabil dari nama (hash), jadi perusahaan
    yang sama selalu berwarna sama. Lingkaran hanya untuk orang: person="true".
    Contoh: <x-ui.monogram :name="$order->buyer->nama" />
--}}
@props(['name' => '', 'size' => 'md', 'person' => false, 'initials' => null])

@php
    $words = collect(preg_split('/\s+/', trim(preg_replace('/^(PT|CV|UD|Koperasi)\.?\s+/i', '', (string) $name))))->filter();
    $initials = $initials ?? ($words->filter(fn ($w) => preg_match('/\pL/u', $w))->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('') ?: '?');
    // Kelas token pasangan [latar, tinta] dari app.css (kontras ≥ 4.5:1).
    $palette = [
        'bg-mono-green text-on-mono-green',
        'bg-mono-clay text-on-mono-clay',
        'bg-mono-sky text-on-mono-sky',
        'bg-mono-sun text-on-mono-sun',
        'bg-mono-zinc text-on-mono-zinc',
    ];
    $tone = $person ? 'bg-primary-container text-on-primary-container' : $palette[abs(crc32((string) $name)) % count($palette)];
    $box = $size === 'sm' ? 'size-8 text-label-sm' : 'size-10 text-body-sm';
@endphp

<span {{ $attributes->class(['inline-flex shrink-0 items-center justify-center font-semibold', $box, $tone, $person ? 'rounded-full' : 'rounded-sm']) }}
      aria-hidden="true">{{ $initials }}</span>

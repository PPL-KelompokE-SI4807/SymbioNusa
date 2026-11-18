{{--
    Kartu putih dengan garis tipis (DESIGN.md › card). Datar, tanpa bayangan.
    title (opsional) tampil sebagai judul kartu 18 px; slot "actions" di kanan judul.
    padding: false untuk isi yang mengatur jaraknya sendiri (mis. tabel penuh).
--}}
@props(['title' => null, 'padding' => true])

<section {{ $attributes->class(['rounded-md border border-outline bg-surface text-on-surface', 'p-6' => $padding]) }}>
    @if ($title || isset($actions))
        <div @class(['mb-4 flex flex-wrap items-center justify-between gap-3', 'px-6 pt-6' => ! $padding])>
            @if ($title)
                <h2 class="text-headline-md">{{ $title }}</h2>
            @endif
            @isset($actions)
                <div class="flex items-center gap-2">{{ $actions }}</div>
            @endisset
        </div>
    @endif

    {{ $slot }}
</section>

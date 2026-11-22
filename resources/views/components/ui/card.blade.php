{{--
    Kartu putih (DESIGN.md › card, amandemen FND v8): radius 20 px, bayangan lembut netral di atas latar sage.
    title (opsional) tampil sebagai judul kartu 18 px; slot "actions" di kanan judul.
    padding: false untuk isi yang mengatur jaraknya sendiri (mis. daftar atau tabel penuh).
--}}
@props(['title' => null, 'padding' => true])

<section {{ $attributes->class(['rounded-lg bg-surface text-on-surface shadow-card', 'p-6' => $padding]) }}>
    @if ($title || isset($actions))
        <div @class(['flex flex-wrap items-center justify-between gap-3', 'mb-4' => $padding, 'px-6 pt-5 pb-2' => ! $padding])>
            @if ($title)
                <h2 class="text-headline-md">{{ $title }}</h2>
            @endif
            @isset($actions)
                <div class="flex items-center gap-3">{{ $actions }}</div>
            @endisset
        </div>
    @endif

    {{ $slot }}
</section>

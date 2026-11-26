{{--
    Kartu putih (DESIGN.md › card; panduan Senja §8.9): radius 14 px, garis 1 px line + bayangan lembut keemasan.
    title (opsional) tampil sebagai judul kartu 19 px / 800 (text-dash-title); slot "actions" di kanan judul.
    padding: false untuk isi yang mengatur jaraknya sendiri (mis. daftar atau tabel penuh).
--}}
@props(['title' => null, 'padding' => true])

<section data-card {{ $attributes->class(['rounded-lg bg-surface text-on-surface shadow-card', 'p-[18px] sm:p-6' => $padding]) }}>
    @if ($title || isset($actions))
        <div @class(['flex flex-wrap items-center justify-between gap-3', 'mb-4' => $padding, 'px-[18px] pt-5 pb-2 sm:px-6' => ! $padding])>
            @if ($title)
                <h2 class="text-dash-title">{{ $title }}</h2>
            @endif
            @isset($actions)
                <div class="flex items-center gap-3">{{ $actions }}</div>
            @endisset
        </div>
    @endif

    {{ $slot }}
</section>

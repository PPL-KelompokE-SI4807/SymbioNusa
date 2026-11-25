{{-- Bilah atas + kepala halaman varian Kaca (SymbioNusa Lab › TopBar, PageHeader). Tidak memakai x-ui.page-header. --}}
@use('App\Support\Format')
<div class="flex items-center gap-2 sm:gap-3" data-rise style="--i: 0">
    <div class="hidden items-center gap-1.5 text-[14px] font-semibold text-ink lg:flex">
        <span>Admin</span>
        <x-ui.icon name="chevron-right" class="size-4 opacity-60" />
        <span class="text-ink">Dashboard</span>
    </div>
    <button type="button" @click="openPalette()" aria-haspopup="dialog"
            class="glass ml-auto flex h-11 min-w-0 flex-1 items-center gap-2.5 rounded-[14px]! px-3.5 text-left text-[14px] text-meta transition hover:border-line-strong lg:max-w-[420px]">
        <x-ui.icon name="search" class="size-[18px] shrink-0" />
        <span class="min-w-0 flex-1 truncate">Cari antrean atau ketik perintah</span>
        <kbd class="mono hidden rounded-[6px] border border-line-strong px-1.5 py-0.5 text-[12px] sm:inline">Ctrl K</kbd>
    </button>
    <span class="glass hidden h-11 items-center gap-2 rounded-[14px]! px-3.5 text-[13px] font-semibold text-meta md:flex" title="Waktu halaman dimuat">
        <span class="relative flex size-2">
            <span class="kaca-ping absolute inline-flex size-full rounded-full bg-primary opacity-60"></span>
            <span class="relative inline-flex size-2 rounded-full bg-primary"></span>
        </span>
        Dimuat {{ str_replace(':', '.', $kaca['now']['time']) }} WIB
    </span>
    <x-app.notification-bell class="glass max-lg:hidden rounded-[14px]!" />
</div>

<header class="mt-6 mb-5 flex flex-wrap items-end justify-between gap-x-6 gap-y-4" data-rise style="--i: 1">
    <div class="max-w-[760px] min-w-0">
        <p class="mb-2 flex items-center gap-2 text-[13px] font-semibold text-ink">
            {{ $kaca['now']['label'] }}
            @if (! app()->environment('production'))
                <span class="rounded-[6px] border border-dashed border-ink/40 px-1.5 py-px text-[12px]">Data demo</span>
            @endif
        </p>
        <h1 class="title-xl text-balance">Dashboard</h1>
        <p class="mt-2 text-[17px] leading-snug text-ink/90">
            {{ Format::number($kaca['summary']['running']) }} order berjalan.
            <strong class="font-extrabold underline decoration-accent decoration-2 underline-offset-4">{{ Format::number($kaca['summary']['pending']) }} menunggu keputusan Anda</strong>@if ($kaca['summary']['stuck']),
                dan {{ $kaca['summary']['stuck']['label'] }} tertahan rata-rata {{ Format::number($kaca['summary']['stuck']['avg'], 1) }} hari.@else.@endif
        </p>
    </div>
    <div class="flex items-center gap-2">
        <div class="glass kaca-seg flex h-11 items-center gap-1 rounded-[14px]! p-1" style="--r: 10px" x-kaca-seg role="group" aria-label="Periode ringkasan (contoh data)">
            <span class="kaca-thumb" aria-hidden="true"></span>
            @foreach (array_keys($kaca['periods']) as $p)
                <button type="button" @click="setPeriod('{{ $p }}')" :aria-pressed="(period === '{{ $p }}').toString()"
                        class="h-9 rounded-[10px] px-3 text-[14px] font-semibold">{{ $p }}</button>
            @endforeach
        </div>
        <button type="button" disabled aria-label="Unduh rekap" title="Unduh rekap belum tersedia"
                class="glass flex h-11 cursor-not-allowed items-center gap-2 rounded-[14px]! px-4 text-[14px] font-bold opacity-60">
            <x-ui.icon name="download" class="size-4" />
            <span class="hidden sm:inline">Unduh rekap</span>
        </button>
    </div>
</header>

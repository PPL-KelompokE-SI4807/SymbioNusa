{{-- Aktivitas, Beban Inspector, Tonase per kategori, Band dampak (Lab › charts/Panels.jsx). --}}
@use('App\Support\Format')
@use('App\Support\DotMatrix')
@php
    $only = $only ?? null;
    // Warna teks inisial di atas cakram bg-field: versi teks Kaca, bukan warna isi (--danger, --accent).
    $tone = ['primary' => 'var(--color-primary-hover)', 'info' => 'var(--info)', 'danger' => 'var(--color-danger)', 'meta' => 'var(--meta)', 'accent' => 'var(--color-accent-text)'];
    $mat = $kaca['materials'];
    $R = 70; $C = 2 * M_PI * $R; $off = 0.0; $arcs = [];
    foreach ($mat['rows'] as $i => $row) {
        $len = $row['share'] * $C;
        $arcs[] = $row + ['dash' => max(0.5, $len - 3), 'offset' => -$off, 'i' => $i];
        $off += $len;
    }
    $donutRows = array_map(fn ($a) => ['name' => $a['name'], 'ton' => $a['ton']], $arcs);
@endphp

@if ($only === 'activity')
{{-- ---------------- Aktivitas ---------------- --}}
<section class="glass flex h-full flex-col p-4 sm:p-5" aria-labelledby="feed-title">
    <div class="flex items-center justify-between">
        <h2 id="feed-title" class="card-title">Aktivitas terbaru</h2>
        <span class="text-[12px] font-semibold text-meta">Riwayat status order</span>
    </div>
    @if ($kaca['activity'] === [])
        <p class="mt-3 text-[14px] text-meta">Belum ada perpindahan status tercatat.</p>
    @else
        <ul class="mt-3 flex-1 space-y-0.5">
            @foreach ($kaca['activity'] as $a)
                <li class="grid grid-cols-[32px_1fr_auto] items-start gap-3 rounded-[12px] px-1.5 py-2">
                    <span class="grid size-8 place-items-center rounded-full bg-field text-[12px] font-black" style="box-shadow: inset 0 0 0 1.5px {{ $tone[$a['tone']] }}; color: {{ $tone[$a['tone']] }}">{{ $a['initials'] }}</span>
                    <p class="text-[14px] leading-snug"><strong class="font-bold">{{ $a['who'] }}</strong> <span class="text-meta">{{ $a['what'] }}</span></p>
                    <span class="mono pt-0.5 text-[12px] text-meta">{{ $a['time'] }}</span>
                </li>
            @endforeach
        </ul>
    @endif
</section>
@endif

@if ($only === 'inspectors')
{{-- ---------------- Beban Inspector ---------------- --}}
<section class="glass flex h-full flex-col p-4 sm:p-5" aria-labelledby="load-title">
    <h2 id="load-title" class="card-title">Beban Inspector</h2>
    <p class="mt-1 text-[14px] text-meta">Tugas selesai dibanding seluruh tugas yang diberikan.</p>
    @if ($kaca['inspectors']['rows'] === [])
        <p class="mt-3 text-[14px] text-meta">Belum ada Inspector aktif.</p>
    @else
        <ul class="mt-3 flex-1 space-y-3">
            @foreach ($kaca['inspectors']['rows'] as $p)
                @php($full = $p['total'] > 0 && $p['done'] >= $p['cap'])
                <li class="grid grid-cols-[36px_1fr] items-center gap-3">
                    <span class="grid size-9 place-items-center rounded-full border border-line-strong text-[12px] font-black">{{ $p['initials'] }}</span>
                    <div class="min-w-0">
                        <div class="flex items-baseline justify-between gap-2 text-[14px]">
                            <span class="truncate font-bold">{{ $p['name'] }} <span class="font-medium text-meta">· {{ $p['area'] }}</span></span>
                            <span class="shrink-0 font-bold tabular-nums">{{ $p['done'] }} dari {{ $p['total'] }}</span>
                        </div>
                        <div class="mt-1.5 flex gap-1" aria-hidden="true">
                            @for ($k = 0; $k < min(12, $p['cap']); $k++)
                                <span class="h-1.5 flex-1 rounded-full" style="background: {{ $k < $p['done'] ? ($full ? 'var(--accent)' : 'var(--c-done)') : 'var(--track)' }}"></span>
                            @endfor
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>
        @if ($kaca['inspectors']['note'])
            <p class="mt-4 border-t border-line pt-3 text-[13px] leading-snug text-meta">{{ $kaca['inspectors']['note'] }}</p>
        @endif
    @endif
</section>
@endif

@if ($only === 'donut')
{{-- ---------------- Tonase per kategori ---------------- --}}
<section class="glass flex h-full flex-col p-4 sm:p-5" aria-labelledby="donut-title" x-data="{ hover: null, rows: {{ Js::from($donutRows) }}, total: {{ (float) $mat['total'] }},
    get active() { return this.hover === null ? null : this.rows[this.hover] },
    fmt(v) { return new Intl.NumberFormat('id-ID', { minimumFractionDigits: 1, maximumFractionDigits: 1 }).format(v) } }">
    <h2 id="donut-title" class="card-title">Tonase per kategori</h2>
    <p class="mt-1 text-[14px] text-meta">Material yang selesai didaur ulang, total tercatat.</p>
    @if ($arcs === [])
        <p class="mt-4 text-[14px] text-meta">Belum ada order selesai yang tercatat dampaknya.</p>
    @else
        <div class="mt-3 flex flex-1 flex-col items-center gap-4 sm:flex-row lg:flex-col xl:flex-row">
            <div class="relative size-[176px] shrink-0">
                <svg viewBox="0 0 176 176" class="size-full -rotate-90" role="img" aria-label="Donat tonase per kategori material">
                    <circle cx="88" cy="88" r="{{ $R }}" fill="none" stroke="var(--track)" stroke-width="22" />
                    @foreach ($arcs as $a)
                        <circle cx="88" cy="88" r="{{ $R }}" fill="none" stroke="var(--color-category-{{ $a['color'] }})" stroke-dasharray="{{ round($a['dash'], 2) }} {{ round($C, 2) }}" stroke-dashoffset="{{ round($a['offset'], 2) }}"
                                :stroke-width="hover === {{ $a['i'] }} ? 28 : 22" class="kaca-arc" style="--c: {{ round($C, 2) }}; --d: {{ $a['i'] * 120 }}ms; transition: stroke-width .2s" @mouseenter="hover = {{ $a['i'] }}" @mouseleave="hover = null" />
                    @endforeach
                </svg>
                <div class="pointer-events-none absolute inset-0 grid place-items-center text-center">
                    <div>
                        <p class="text-[26px] leading-none font-black tabular-nums" x-text="fmt(active ? active.ton : total)">{{ Format::number($mat['total'], 1) }}</p>
                        <p class="mt-1 text-[12px] font-semibold text-meta" x-text="active ? 'ton ' + active.name : 'ton total'">ton total</p>
                    </div>
                </div>
            </div>
            <ul class="w-full min-w-0 space-y-1">
                @foreach ($arcs as $a)
                    <li>
                        <button type="button" @mouseenter="hover = {{ $a['i'] }}" @mouseleave="hover = null" @focus="hover = {{ $a['i'] }}" @blur="hover = null"
                                :class="hover === {{ $a['i'] }} ? 'bg-primary-soft' : ''" class="grid w-full grid-cols-[28px_1fr_auto_auto] items-center gap-2.5 rounded-[10px] px-1.5 py-1 text-left text-[14px]">
                            <img src="{{ $a['photo'] }}" alt="" class="size-7 rounded-[7px] object-cover" style="box-shadow: 0 0 0 2px var(--color-category-{{ $a['color'] }})">
                            <span class="truncate font-semibold">{{ $a['name'] }}</span>
                            <span class="font-bold tabular-nums">{{ Format::number($a['ton'], 1) }}</span>
                            <span class="w-10 text-right text-[13px] text-meta tabular-nums">{{ (int) round($a['share'] * 100) }}%</span>
                        </button>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
</section>
@endif

@if ($only === 'impact')
{{-- ---------------- Band dampak ---------------- --}}
<section class="impact-band relative overflow-hidden rounded-[24px] p-5 text-[#fef9c3] sm:p-7" aria-labelledby="impact-title">
    <img src="{{ asset('images/material/serbuk-kayu.jpg') }}" alt="" class="absolute inset-0 size-full object-cover opacity-[.14] mix-blend-luminosity">
    <div class="relative flex flex-wrap items-baseline justify-between gap-2">
        <h2 id="impact-title" class="text-[18px] font-extrabold">Dampak lingkungan tercatat</h2>
        <p class="text-[13px] text-[#e3d99a]">Total sejak platform berjalan{{ app()->environment('production') ? '' : ' · Data demo' }}</p>
    </div>
    <ul class="relative mt-5 grid gap-6 sm:grid-cols-2 xl:grid-cols-4 xl:gap-0">
        @foreach ($kaca['impact'] as $i => $m)
            @php($dm = DotMatrix::layout(Format::number($m['value'], $m['decimals']), 6, 2.2))
            <li class="xl:px-6 {{ $i ? 'xl:border-l xl:border-[#fef9c3]/15' : 'xl:pl-0' }}">
                <p class="text-[13px] font-semibold text-[#e3d99a]">{{ $m['label'] }}</p>
                <div class="mt-2.5 flex items-end gap-2">
                    <svg viewBox="0 0 {{ $dm['width'] }} {{ $dm['height'] }}" width="{{ $dm['width'] }}" height="{{ $dm['height'] }}" class="block max-w-full" role="img" aria-label="{{ Format::number($m['value'], $m['decimals']) }}">
                        @foreach ($dm['dots'] as $d)
                            <circle cx="{{ $d['x'] }}" cy="{{ $d['y'] }}" r="3" fill="{{ $d['on'] ? '#fef9c3' : 'rgba(254,249,195,.12)' }}" />
                        @endforeach
                    </svg>
                    <span class="pb-0.5 text-[14px] font-bold text-[#fef9c3]/85">{{ $m['unit'] }}</span>
                </div>
                <p class="mt-2 text-[13px] text-[#e3d99a]">{{ $m['note'] }}</p>
            </li>
        @endforeach
    </ul>
</section>
@endif


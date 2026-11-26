{{-- Inspeksi per jam + Sedang dan berikutnya (Lab › HourlyChart). Data dari inspections.jadwal. --}}
@php
    $h = $kaca['hourly'];
    $series = [
        ['done', 'Selesai', 'var(--c-done)', false], ['run', 'Berjalan', 'var(--c-run)', false], ['wait', 'Menunggu Inspector', 'var(--c-wait)', false],
        ['fail', 'Gagal', 'var(--c-fail)', false], ['plan', 'Terjadwal', 'var(--c-plan)', true],
    ];
    $W = 520; $H = 210; $PL = 26; $PR = 8; $PT = 34; $PB = 28;
    $max = $h['max']; $iw = $W - $PL - $PR; $ih = $H - $PT - $PB; $bw = $iw / count($h['rows']);
    $y = fn ($v) => $PT + $ih - ($v / $max) * $ih;
    $ticks = array_values(array_unique([0, $max / 2, $max]));
    $nowX = $h['nowHour'] !== null ? $PL + ($h['nowHour'] - $h['rows'][0]['h']) * $bw : null;
    $nowX = $nowX !== null && $nowX >= $PL && $nowX <= $W - $PR ? $nowX : null;
    $dotColor = ['run' => 'var(--c-run)', 'wait' => 'var(--accent)', 'plan' => 'var(--c-plan)', 'done' => 'var(--c-done)'];
@endphp
<section class="glass flex h-full flex-col p-4 sm:p-5" aria-labelledby="hourly-title" x-data="{ view: 'bar', hover: null }">
    <div class="flex items-start justify-between gap-3">
        <h2 id="hourly-title" class="card-title">{{ $h['title'] }}</h2>
        <div class="kaca-seg flex shrink-0 rounded-[11px] border border-line-strong p-0.5" style="--r: 9px" x-kaca-seg role="group" aria-label="Bentuk grafik">
            <span class="kaca-thumb" aria-hidden="true"></span>
            @foreach (['bar' => 'Batang', 'dot' => 'Titik'] as $k => $l)
                <button type="button" @click="view = '{{ $k }}'" :aria-pressed="(view === '{{ $k }}').toString()"
                        class="h-9 rounded-[9px] px-2.5 text-[13px] font-bold">{{ $l }}</button>
            @endforeach
        </div>
    </div>
    <p class="mt-1 text-[14px] text-meta">
        @if ($h['totals']['all'] > 0)
            {{ $h['totals']['all'] }} jadwal di semua wilayah: {{ $h['totals']['done'] }} selesai, {{ $h['totals']['wait'] }} masih menunggu Inspector.
        @else
            Belum ada jadwal inspeksi tercatat.
        @endif
    </p>
    <ul class="mt-3 flex flex-wrap gap-x-3.5 gap-y-1">
        @foreach ($series as [$k, $label, $color, $hatch])
            <li class="flex items-center gap-1.5 text-[12px] font-semibold text-meta">
                <span class="size-2.5 rounded-[3px] {{ $hatch ? 'hatch-bg' : '' }}" style="{{ $hatch ? 'box-shadow: inset 0 0 0 1px var(--line-strong)' : 'background: '.$color }}"></span>
                {{ $label }}
            </li>
        @endforeach
    </ul>

    <div class="relative mt-2">
        <svg viewBox="0 0 {{ $W }} {{ $H }}" class="block h-auto w-full" role="img"
             aria-label="Grafik inspeksi per jam{{ $nowX !== null ? ', penanda sekarang pukul '.$h['nowTime'] : '' }}">
            <defs>
                <pattern id="plan-hatch" width="6" height="6" patternUnits="userSpaceOnUse" patternTransform="rotate(45)">
                    <rect width="6" height="6" fill="var(--c-plan)" /><line x1="0" y1="0" x2="0" y2="6" stroke="var(--hatch)" stroke-width="1.6" />
                </pattern>
            </defs>
            @foreach ($ticks as $v)
                <line x1="{{ $PL }}" x2="{{ $W - $PR }}" y1="{{ $y($v) }}" y2="{{ $y($v) }}" stroke="var(--line)" @if ($v) stroke-dasharray="2 4" @endif />
                <text x="{{ $PL - 8 }}" y="{{ $y($v) + 4 }}" font-size="13" text-anchor="end" fill="var(--meta)">{{ $v }}</text>
            @endforeach
            @foreach ($h['rows'] as $n => $r)
                @php
                    $acc = 0; $x = $PL + $n * $bw + $bw * 0.18; $w = $bw * 0.64;
                    $isNow = $h['nowHour'] !== null && (int) floor($h['nowHour']) === $r['h'];
                @endphp
                <g @mouseenter="hover = {{ $n }}" @mouseleave="hover = null">
                    <rect x="{{ $PL + $n * $bw }}" y="{{ $PT }}" width="{{ $bw }}" height="{{ $ih }}" rx="8" :fill="hover === {{ $n }} ? 'var(--primary-soft)' : 'transparent'" />
                    @foreach ($series as [$k, $label, $color, $hatch])
                        @php($v = $r[$k])
                        @if ($v)
                            @php($y0 = $y($acc + $v))
                            <rect x="{{ $x }}" y="{{ $y0 }}" width="{{ $w }}" height="{{ $y($acc) - $y0 }}" rx="5" fill="{{ $hatch ? 'url(#plan-hatch)' : $color }}"
                                  stroke="var(--panel-strong)" stroke-width="1.5" x-show="view === 'bar'" style="transform-box: fill-box; transform-origin: bottom; animation: kaca-grow-y .7s var(--ease-hover) both; animation-delay: {{ $n * 40 }}ms" />
                            @for ($d = 0; $d < $v; $d++)
                                <circle cx="{{ $x + $w / 2 }}" cy="{{ $y($acc + $d + 0.5) }}" r="{{ min($bw * 0.3, ($ih / $max) * 0.42) }}" fill="{{ $hatch ? 'url(#plan-hatch)' : $color }}"
                                        stroke="{{ $hatch ? 'var(--line-strong)' : 'var(--panel-strong)' }}" stroke-width="1.5" x-show="view === 'dot'" x-cloak class="kaca-dot" style="--d: {{ $n * 30 + ($acc + $d) * 40 }}ms" />
                            @endfor
                            @php($acc += $v)
                        @endif
                    @endforeach
                    <text x="{{ $x + $w / 2 }}" y="{{ $H - 7 }}" font-size="13" text-anchor="middle" fill="{{ $isNow ? 'var(--ink)' : 'var(--meta)' }}" font-weight="{{ $isNow ? 800 : 500 }}">{{ str_pad((string) $r['h'], 2, '0', STR_PAD_LEFT) }}</text>
                </g>
            @endforeach
            @if ($nowX !== null)
                <line x1="{{ $nowX }}" x2="{{ $nowX }}" y1="{{ $PT - 6 }}" y2="{{ $PT + $ih }}" stroke="var(--accent)" stroke-width="1.6" stroke-dasharray="3 3" />
                <rect x="{{ $nowX - 56 }}" y="3" width="112" height="24" rx="12" fill="var(--accent)" />
                <text x="{{ $nowX }}" y="19.5" font-size="13.5" font-weight="800" text-anchor="middle" fill="var(--accent-ink)">Sekarang {{ $h['nowTime'] }}</text>
            @endif
        </svg>

        @foreach ($h['rows'] as $n => $r)
            <div x-show="hover === {{ $n }}" x-cloak class="glass-strong pointer-events-none absolute top-8 z-10 w-48 rounded-[14px] p-3 text-[13px]"
                 style="left: clamp(0px, calc({{ (($PL + $n * $bw + $bw) / $W) * 100 }}% + 6px), calc(100% - 12rem))">
                <p class="mb-1.5 font-extrabold">{{ str_pad((string) $r['h'], 2, '0', STR_PAD_LEFT) }}.00–{{ str_pad((string) ($r['h'] + 1), 2, '0', STR_PAD_LEFT) }}.00</p>
                @php($any = false)
                @foreach ($series as [$k, $label, $color, $hatch])
                    @if ($r[$k])
                        @php($any = true)
                        <p class="flex items-center justify-between gap-3">
                            <span class="flex items-center gap-1.5 text-meta"><span class="size-2 rounded-[2px] {{ $hatch ? 'hatch-bg' : '' }}" style="{{ $hatch ? '' : 'background: '.$color }}"></span>{{ $label }}</span>
                            <strong class="tabular-nums">{{ $r[$k] }}</strong>
                        </p>
                    @endif
                @endforeach
                @unless ($any)<p class="text-meta">Tidak ada jadwal</p>@endunless
            </div>
        @endforeach
    </div>

    <h3 class="mt-4 border-t border-line pt-4 text-[15px] font-extrabold">{{ $h['isToday'] ? 'Sedang dan berikutnya' : 'Jadwal hari itu' }}</h3>
    @if ($h['next'] === [])
        <p class="mt-3 text-[14px] text-meta">Tidak ada jadwal inspeksi.</p>
    @else
        <ol class="relative mt-3 space-y-3 before:absolute before:top-2 before:bottom-2 before:left-[53px] before:w-px before:bg-[var(--line-strong)]">
            @foreach ($h['next'] as $n)
                @php($dot = $dotColor[$n['state']] ?? 'var(--c-plan)')
                <li class="relative grid grid-cols-[40px_16px_1fr] items-start gap-x-2">
                    <span class="mono pt-0.5 text-[13px] font-bold tabular-nums">{{ $n['time'] }}</span>
                    <span class="relative mt-1.5 grid size-3 place-items-center">
                        @if ($n['state'] === 'run')<span class="kaca-ping absolute size-3 rounded-full" style="background: {{ $dot }}; opacity: .5"></span>@endif
                        <span class="relative size-2.5 rounded-full" style="background: {{ $dot }}; box-shadow: 0 0 0 3px var(--panel-strong)"></span>
                    </span>
                    <div class="flex min-w-0 items-start gap-3">
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-[14px] font-bold">{{ $n['material'] }} <span class="mono text-[12px] font-semibold text-meta">{{ $n['po'] }}</span></p>
                            <p class="truncate text-[13px] text-meta">{{ $n['who'] }} · {{ $n['place'] }} · {{ $n['step'] }}</p>
                        </div>
                        <img src="{{ $n['photo'] }}" alt="" class="size-9 shrink-0 rounded-[10px] object-cover">
                    </div>
                </li>
            @endforeach
        </ol>
    @endif
</section>

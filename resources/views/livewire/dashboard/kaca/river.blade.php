{{-- Sungai Order (Lab › OrderRiver). Hitungan tahap, jalur keluar, dan baris order dari DashboardService::orderFlow(). --}}
@use('App\Support\Format')
@php
    $r = $kaca['river'];
    $active = array_values(array_filter($r['stages'], fn ($s) => $s['key'] !== 'SELESAI'));
    $W = 1000; $H = 172; $CY = 62; $n = count($active); $COL = $W / $n;
    $maxCount = max(1, max(array_column($active, 'count')));
    $thick = fn ($c) => 18 + 70 * sqrt($c / $maxCount);
    $pts = [['x' => 0, 'h' => $thick($active[0]['count']) / 2]];
    foreach ($active as $i => $s) { $pts[] = ['x' => $COL * $i + $COL / 2, 'h' => $thick($s['count']) / 2]; }
    $pts[] = ['x' => $W, 'h' => $thick($active[$n - 1]['count']) / 2];
    $seg = function ($a, $b, $sign) use ($CY) { $mx = ($a['x'] + $b['x']) / 2; return sprintf('C %.2f %.2f %.2f %.2f %.2f %.2f', $mx, $CY + $sign * $a['h'], $mx, $CY + $sign * $b['h'], $b['x'], $CY + $sign * $b['h']); };
    $path = sprintf('M %.2f %.2f', $pts[0]['x'], $CY - $pts[0]['h']);
    for ($i = 1; $i < count($pts); $i++) { $path .= ' '.$seg($pts[$i - 1], $pts[$i], -1); }
    $path .= sprintf(' L %.2f %.2f', $W, $CY + end($pts)['h']);
    for ($i = count($pts) - 2; $i >= 0; $i--) { $path .= ' '.$seg($pts[$i + 1], $pts[$i], 1); }
    $path .= ' Z';
    $catColor = fn ($c) => 'var(--color-category-'.($c ?? 'lainnya').')';
    $stageByKey = collect($r['stages'])->concat($r['exits'])->keyBy('key');
    // Warna teks (bukan isi): merah teks Kaca #7f1d1d, bukan --danger #b91c1c (4,06:1 di atas kaca).
    $exitTone = ['danger' => 'var(--color-danger)', 'meta' => 'var(--meta)'];
@endphp
<section class="glass p-4 sm:p-6" aria-labelledby="river-title" x-data="kacaRiver('{{ $r['selected'] }}')">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h2 id="river-title" class="card-title">Sungai Order</h2>
            <p class="mt-1 text-[14px] text-meta">
                {{ Format::number($r['active_total']) }} order mengalir dari draf sampai diterima. Tebal sungai mengikuti jumlah order; angka kecil adalah rata-rata lama di tahap bila riwayat statusnya tercatat. Pilih tahap untuk melihat isinya.
            </p>
        </div>
        <div class="flex items-baseline gap-2 rounded-[14px] border border-line px-3 py-2">
            <span class="text-[26px] leading-none font-black tabular-nums">{{ Format::number($r['done']['count']) }}</span>
            <span class="text-[13px] font-semibold text-meta">selesai · {{ Format::number($r['done']['ton'], 1) }} ton</span>
        </div>
    </div>

    <div class="-mx-4 mt-4 overflow-x-auto px-4 sm:mx-0 sm:px-0">
        <div class="relative min-w-[600px]">
            <div class="grid" style="grid-template-columns: repeat({{ $n }}, 1fr)">
                @foreach ($active as $s)
                    <button type="button" @click="select('{{ $s['key'] }}')" :aria-pressed="(selected === '{{ $s['key'] }}').toString()"
                            :class="selected === '{{ $s['key'] }}' ? 'bg-[var(--panel-strong)] shadow-[0_0_0_1px_var(--line-strong)]' : 'hover:bg-primary-soft'"
                            class="rounded-[12px] px-2 py-1.5 text-left transition-colors">
                        <span class="block truncate text-[12px] font-bold {{ $s['stuck'] ? 'text-accent-text' : 'text-meta' }}">{{ $s['short'] }}</span>
                        <span class="block text-[24px] leading-tight font-black tabular-nums">{{ $s['count'] }}</span>
                        <span class="block text-[12px] font-semibold tabular-nums {{ $s['stuck'] ? 'text-accent-text' : 'text-meta' }}">{{ $s['avg'] === null ? '–' : Format::number($s['avg'], 1).' hari' }}</span>
                    </button>
                @endforeach
            </div>

            <svg viewBox="0 0 {{ $W }} {{ $H }}" class="mt-1 block h-auto w-full" role="img" aria-label="Sungai order: tebal tiap tahap sebanding dengan jumlah order">
                <defs>
                    <pattern id="river-hatch" width="7" height="7" patternUnits="userSpaceOnUse" patternTransform="rotate(45)">
                        <rect width="7" height="7" fill="var(--accent)" /><line x1="0" y1="0" x2="0" y2="7" stroke="var(--accent-ink)" stroke-opacity=".35" stroke-width="2.4" />
                    </pattern>
                    <clipPath id="river-clip"><path d="{{ $path }}" /></clipPath>
                    @foreach ($active as $i => $s)
                        <clipPath id="col-{{ $s['key'] }}"><rect x="{{ $COL * $i + 1 }}" y="0" width="{{ $COL - 2 }}" height="{{ $H }}" /></clipPath>
                    @endforeach
                </defs>
                @foreach ($active as $i => $s)
                    <path d="{{ $path }}" clip-path="url(#col-{{ $s['key'] }})" fill="{{ $s['stuck'] ? 'url(#river-hatch)' : 'var(--c-done)' }}" style="cursor: pointer; transition: opacity .3s ease; transform-box: fill-box; transform-origin: center; animation: kaca-grow-y .6s var(--ease-hover) both; animation-delay: {{ $i * 50 }}ms"
                          :opacity="selected === '{{ $s['key'] }}' ? 1 : {{ $s['stuck'] ? 0.92 : 0.5 + ($i % 2) * 0.1 }}" @click="select('{{ $s['key'] }}')" />
                @endforeach
                <g clip-path="url(#river-clip)" class="river-flow" pointer-events="none">
                    @foreach ([-22, 0, 22] as $i => $dy)
                        <path d="M 0 {{ $CY + $dy }} C 250 {{ $CY + $dy - 10 }}, 500 {{ $CY + $dy + 10 }}, 1000 {{ $CY + $dy }}" fill="none" stroke="var(--panel-strong)" stroke-opacity="0.55" stroke-width="1.6"
                              stroke-dasharray="{{ 10 + $i * 4 }} {{ 26 + $i * 6 }}" style="animation: kaca-flow {{ 7 + $i * 2 }}s linear infinite" />
                    @endforeach
                </g>
                @foreach ($r['exits'] as $x)
                    @php
                        $i = array_search($x['from'], array_column($active, 'key'), true);
                    @endphp
                    @if ($i !== false)
                        @php
                            $cx = $COL * $i + $COL / 2; $hh = $thick($active[$i]['count']) / 2; $dir = $i > $n / 2 ? -1 : 1; $col = $exitTone[$x['tone']] ?? 'var(--meta)';
                        @endphp
                        <g>
                            <path d="M {{ $cx }} {{ $CY + $hh }} C {{ $cx }} {{ $CY + $hh + 26 }}, {{ $cx + $dir * 16 }} {{ $CY + $hh + 30 }}, {{ $cx + $dir * 30 }} {{ $CY + $hh + 34 }}" fill="none" stroke="{{ $col }}" stroke-width="1.6" stroke-dasharray="3 3" />
                            <text x="{{ $cx + $dir * 34 }}" y="{{ $CY + $hh + 39 }}" font-size="14" font-weight="700" text-anchor="{{ $dir < 0 ? 'end' : 'start' }}" fill="{{ $col }}" stroke="var(--panel-strong)" stroke-width="4" stroke-linejoin="round" paint-order="stroke" style="cursor: pointer" @click="select('{{ $x['key'] }}')">{{ $x['label'] }} · {{ $x['count'] }}</text>
                        </g>
                    @endif
                @endforeach
            </svg>
        </div>
    </div>

    <div class="mt-1 flex flex-wrap items-center gap-3 rounded-[16px] border border-[var(--accent-soft)] bg-[var(--accent-soft)] px-4 py-3">
        @if ($r['stuck'])
            <p class="min-w-0 flex-1 text-[14px] leading-snug">
                <strong class="font-extrabold text-accent-text">{{ $r['stuck']['label'] }} adalah titik tertahan.</strong>
                {{ $r['stuck']['count'] }} order menunggu rata-rata {{ Format::number($r['stuck']['avg'], 1) }} hari, {{ Format::number($r['stuck']['ratio'], 1) }} kali tahap lain{{ $r['stuck']['over7'] > 0 ? '; '.$r['stuck']['over7'].' di antaranya sudah lewat 7 hari' : '' }}.
            </p>
            <button type="button" @click="select('{{ $r['stuck']['key'] }}')" class="flex h-10 items-center gap-1.5 rounded-[12px] bg-accent px-3.5 text-[14px] font-bold text-accent-ink">
                Lihat {{ $r['stuck']['count'] }} order <x-ui.icon name="arrow-right" class="size-4" />
            </button>
        @elseif ($r['busiest'])
            <p class="min-w-0 flex-1 text-[14px] leading-snug">
                <strong class="font-extrabold text-accent-text">{{ $r['busiest']['label'] }} memegang order terbanyak.</strong>
                {{ $r['busiest']['count'] }} dari {{ Format::number($r['active_total']) }} order berjalan. Belum ada tahap yang jelas lebih lama dari yang lain.
            </p>
            <button type="button" @click="select('{{ $r['busiest']['key'] }}')" class="flex h-10 items-center gap-1.5 rounded-[12px] bg-accent px-3.5 text-[14px] font-bold text-accent-ink">
                Lihat {{ $r['busiest']['count'] }} order <x-ui.icon name="arrow-right" class="size-4" />
            </button>
        @endif
    </div>

    <div x-ref="stageBox">
        @foreach ($r['stages'] as $s)
            @include('livewire.dashboard.kaca.river-stage', ['s' => $s, 'exit' => false])
        @endforeach
        @foreach ($r['exits'] as $x)
            @include('livewire.dashboard.kaca.river-stage', ['s' => $x + ['avg' => null], 'exit' => true])
        @endforeach
    </div>
</section>

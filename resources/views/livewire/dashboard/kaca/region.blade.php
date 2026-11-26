{{-- Peta pengawasan, kartogram ubin Jawa (Lab › RegionMap). Isinya contoh Lab: DDL tidak menyimpan kota perusahaan atau temuan DLH. --}}
@use('App\Support\Format')
@php
    $regions = $kaca['regions'];
    $status = [
        'patuh' => ['Patuh', 'var(--c-done)', 'var(--primary-ink)', 'text-primary'],
        'temuan' => ['Ada temuan', 'var(--accent)', 'var(--accent-ink)', 'text-accent-text'],
        'peringatan' => ['Peringatan', 'var(--danger)', '#ffffff', 'text-danger'],
        'sepi' => ['Tanpa aktivitas', 'transparent', 'var(--meta)', 'text-meta'],
    ];
    $counts = array_count_values(array_column($regions, 'status'));
    $first = collect($regions)->firstWhere('code', 'BDG')['code'] ?? $regions[0]['code'];
@endphp
<section class="glass flex h-full flex-col p-4 sm:p-5" aria-labelledby="map-title" x-data="{ sel: '{{ $first }}' }">
    <div class="flex flex-wrap items-start justify-between gap-2">
        <div>
            <h2 id="map-title" class="card-title">Peta pengawasan</h2>
            <p class="mt-1 text-[14px] text-meta">Satu ubin satu kota industri di Jawa. Isi ubin adalah order aktif; warnanya status kepatuhan DLH.</p>
        </div>
        <span class="rounded-[6px] border border-dashed border-line-strong px-1.5 py-px text-[12px] text-meta">Data demo</span>
    </div>
    <ul class="mt-3 flex flex-wrap gap-x-3.5 gap-y-1">
        @foreach ($status as $k => [$label, $fill])
            <li class="flex items-center gap-1.5 text-[12px] font-semibold text-meta">
                <span class="size-2.5 rounded-[3px]" style="background: {{ $fill }}; {{ $k === 'sepi' ? 'box-shadow: inset 0 0 0 1.5px var(--line-strong)' : '' }}"></span>
                {{ $label }} · {{ $counts[$k] ?? 0 }}
            </li>
        @endforeach
    </ul>

    <div class="mt-4 grid flex-1 gap-4 xl:grid-cols-[minmax(0,1fr)_280px]">
        <div class="-mx-1 overflow-x-auto px-1 pb-1">
            <div class="grid min-w-[560px] gap-2" style="grid-template-columns: repeat(12, minmax(0, 1fr)); grid-template-rows: repeat(3, auto)" role="listbox" aria-label="Wilayah pengawasan">
                @foreach ($regions as $r)
                    @php([$label, $fill, $ink] = $status[$r['status']])
                    <button type="button" role="option" :aria-selected="(sel === '{{ $r['code'] }}').toString()" @click="sel = '{{ $r['code'] }}'"
                            :class="sel === '{{ $r['code'] }}' ? 'z-10 ring-2 ring-[var(--ink)] ring-offset-2 ring-offset-transparent' : 'hover:-translate-y-0.5'"
                            class="kaca-tile relative flex aspect-square flex-col items-center justify-center rounded-[14px] text-center outline-offset-2 transition-transform"
                            style="--d: {{ ($r['c'] + $r['r'] * 2) * 22 }}ms; grid-column: {{ $r['c'] + 1 }}; grid-row: {{ $r['r'] + 1 }}; background: {{ $r['status'] === 'sepi' ? 'var(--field)' : $fill }}; color: {{ $ink }}; box-shadow: {{ $r['status'] === 'sepi' ? 'inset 0 0 0 1.5px var(--line-strong)' : '0 8px 16px -10px '.$fill }}">
                        <span class="text-[13px] font-black tracking-wide">{{ $r['code'] }}</span>
                        <span class="text-[18px] leading-none font-black tabular-nums">{{ $r['orders'] }}</span>
                        <span class="sr-only">: {{ $r['name'] }}, {{ $label }}, {{ $r['orders'] }} order aktif</span>
                        @if (! in_array($r['status'], ['patuh', 'sepi'], true))
                            <span class="absolute top-1.5 right-1.5 size-2.5 rounded-full border-2 border-[var(--panel-strong)]" style="background: {{ $fill }}">
                                <span class="kaca-ping absolute inset-0 rounded-full" style="background: {{ $fill }}; opacity: .6"></span>
                            </span>
                        @endif
                    </button>
                @endforeach
            </div>
            <p class="mt-2 flex justify-between text-[12px] font-semibold text-meta" aria-hidden="true">
                <span>← Banten</span><span>Jawa Barat</span><span>Jawa Tengah</span><span>Jawa Timur →</span>
            </p>
        </div>

        @foreach ($regions as $r)
            @php([$label, , , $tone] = $status[$r['status']])
            <div x-show="sel === '{{ $r['code'] }}'" x-cloak x-transition:enter="kaca-spring" x-transition:enter-start="opacity-0 translate-x-3" x-transition:enter-end="opacity-100 translate-x-0" class="flex flex-col rounded-[16px] border border-line bg-field p-4" aria-live="polite">
                <p class="text-[12px] font-semibold text-meta">{{ $r['prov'] }}</p>
                <h3 class="text-[19px] font-black tracking-tight">{{ $r['name'] }}</h3>
                <p class="mt-0.5 text-[13px] font-bold {{ $tone }}">{{ $label }}</p>
                <dl class="mt-3 grid grid-cols-3 gap-2 text-center">
                    @foreach ([['package', $r['orders'], 'order'], ['weight', Format::number($r['ton'], 1), 'ton'], ['users', $r['inspectors'], 'Inspector']] as [$icon, $v, $l])
                        <div class="rounded-[12px] border border-line px-1 py-2">
                            <x-ui.icon :name="$icon" class="mx-auto size-4 text-meta" />
                            <dd class="mt-1 text-[17px] font-black tabular-nums">{{ $v }}</dd>
                            <dt class="text-[12px] text-meta">{{ $l }}</dt>
                        </div>
                    @endforeach
                </dl>
                @if ($r['note'])
                    <p class="mt-3 text-[13px] leading-snug"><span class="mono mr-1 text-[12px] font-bold text-meta">{{ $r['noteId'] }}</span>{{ $r['note'] }}</p>
                @else
                    <p class="mt-3 text-[13px] leading-snug text-meta">{{ $r['status'] === 'sepi' ? 'Belum ada transaksi tercatat di wilayah ini.' : 'Tidak ada temuan terbuka.' }}</p>
                @endif
                @if ($r['inspectors'] === 0 && $r['orders'] > 0)
                    <p class="mt-2 text-[13px] font-bold text-accent-text">Belum ada Inspector di wilayah ini.</p>
                @endif
                <div class="mt-auto pt-3">
                    <span class="flex h-11 w-full cursor-not-allowed items-center justify-center gap-1.5 rounded-[12px] border border-line-strong text-[13px] font-bold opacity-60" title="Halaman Pengawasan DLH segera hadir">
                        Buka di Pengawasan DLH <x-ui.icon name="arrow-right" class="size-4" />
                    </span>
                </div>
            </div>
        @endforeach
    </div>
</section>

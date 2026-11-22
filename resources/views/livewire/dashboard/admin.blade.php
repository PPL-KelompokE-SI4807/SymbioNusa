{{--
    Dasbor Admin / DLH — varian "Senja di Sawah" (panduan dashboard Senja §6.1, keputusan PM 22 Nov 2026).
    Urutan: Sungai Order (alur sistem) → Zona 1 "Perlu tindakan Anda" → Zona 2 "Sedang berjalan"
    → Zona 3 "Gambaran total tercatat". Semua angka dari DashboardService::admin(); satu fakta = satu sumber nilai.
    Token Senja berlaku lewat pembungkus .dash-senja di dashboard-overview.blade.php.
--}}
@use('App\Support\Format')
@php
    $stages = collect($flow['stages']);
    $exitsByOrigin = collect($flow['exits'])->keyBy('origin');
    $flowSum = $stages->sum('count') + collect($flow['exits'])->sum('count');
    // Bobot minimum agar tahap kosong tetap terlihat dan dapat diketuk; lebar tetap mengkodekan jumlah order.
    $base = max(1, round($flowSum * 0.06, 2));

    $chartStages = $stages->map(fn ($s) => collect($s)->only(['key', 'label', 'short', 'count', 'kg', 'nilai', 'orders']))->values();

    [$hematValue, $hematUnit] = Format::rupiahParts($metrics['hemat_seller'] + $metrics['hemat_buyer']);

    // Donut: busur dari bagian terhadap total seluruh set; pemisah putih 3 px.
    $radius = 79;
    $circ = 2 * M_PI * $radius;
    $offset = 0.0;
    $arcs = [];
    foreach ($materials['rows'] as $row) {
        $len = $row['share'] * $circ;
        $gap = count($materials['rows']) > 1 ? 3 : 0;
        $arcs[] = ['key' => $row['key'], 'color' => $row['color'] ?? 'lainnya', 'dash' => max(0.5, $len - $gap), 'offset' => -$offset];
        $offset += $len;
    }
    $donutJs = ['rows' => $materials['rows'], 'totalTon' => $materials['total_ton']];
    $groupIcons = ['sengketa' => 'scale', 'sertifikat' => 'shield', 'penugasan' => 'calendar', 'verifikasi' => 'building'];
    $focusRing = 'focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus-ring';

    // Zona 3: strip metrik dan hasil akhir. Dihitung di sini (bukan di blok @php di tengah file) karena Blade
    // menggabungkan @php() sebaris dengan blok @php berikutnya.
    $m = $metrics;
    $metricRows = [
        [
            'label' => 'Tonase terdaur ulang', 'value' => Format::number($m['tonase'], 1), 'unit' => 'ton',
            'segs' => [[$m['tonase'], 'primary'], [$m['aktif_ton'], 'flow-segment-line']],
            'legend' => [['primary', 'selesai, '.Format::number($m['orders_selesai']).' order'], ['flow-segment-line', Format::number($m['aktif_ton'], 1).' ton masih di alur']],
        ],
        [
            'label' => 'Penghematan biaya', 'value' => $hematValue, 'unit' => $hematUnit,
            'segs' => [[$m['hemat_seller'], 'primary'], [$m['hemat_buyer'], 'flow-segment-line']],
            'legend' => [['primary', 'Seller '.Format::rupiahShort($m['hemat_seller'])], ['flow-segment-line', 'Buyer '.Format::rupiahShort($m['hemat_buyer'])]],
        ],
        [
            'label' => 'Emisi ditekan', 'value' => Format::number($m['co2e_kg'] / 1000, 1), 'unit' => 'ton CO₂e',
            'segs' => [[$m['co2e_kg'], 'primary'], [$m['co2e_angkutan_kg'], 'warning']],
            'legend' => [['primary', 'bersih'], ['warning', 'angkutan '.Format::number($m['co2e_angkutan_kg'] / 1000, 1).' dari '.Format::number($m['co2e_dihindari_kg'] / 1000, 1).' ton dihindari']],
        ],
        [
            'label' => 'Inspector aktif', 'value' => Format::number($m['inspector_aktif']), 'unit' => null,
            'segs' => [[$m['inspector_aktif'], 'primary'], [max(0, $m['inspector_total'] - $m['inspector_aktif']), 'outline']],
            'legend' => [['primary', 'aktif'], ['outline', 'dari '.Format::number($m['inspector_total']).' terdaftar']],
        ],
    ];
    $byKey = collect([...$flow['stages'], ...$flow['exits']])->keyBy('key');
    $outcomes = collect([['SELESAI', 'primary'], ['DITOLAK', 'error'], ['GAGAL_INSPEKSI', 'warning'], ['DIBATALKAN', 'flow-segment-line']])
        ->map(fn ($o) => ['key' => $o[0], 'color' => $o[1]] + collect($byKey[$o[0]])->only(['label', 'count', 'nilai', 'orders'])->all())
        ->values();
    $endedTotal = $outcomes->sum('count');
@endphp

<div class="space-y-6">
    {{-- ================= ALUR SISTEM · Sungai Order ================= --}}
    <x-ui.card :padding="false" class="p-[18px] sm:p-6" aria-labelledby="flow-title" x-data="sungaiOrder({{ Js::from(['stages' => $flow['stages'], 'exits' => $flow['exits']]) }})">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <p class="dash-eyebrow">Alur sistem</p>
                <h2 id="flow-title" class="mt-2 text-dash-title">Sungai Order</h2>
            </div>
            <button type="button" x-show="!reduced" x-cloak @click="togglePause()" :aria-pressed="paused.toString()"
                    class="inline-flex h-10 items-center gap-2 rounded-sm border border-outline-control bg-surface px-3 text-label-md font-semibold text-on-surface hover:bg-surface-muted {{ $focusRing }}">
                <span x-text="paused ? 'Putar aliran' : 'Jeda aliran'">Jeda aliran</span>
            </button>
        </div>

        <div class="flow-groups mt-5" role="group" aria-label="Tahap alur order">
            @foreach ($stages->groupBy('group') as $group => $groupStages)
                <div class="flow-group" style="flex-grow: {{ $groupStages->sum(fn ($s) => $s['count'] + $base) }}">
                    <p class="flow-group-label">{{ $group }}</p>
                    <div class="flow-row">
                        @foreach ($groupStages as $s)
                            @php($exit = $exitsByOrigin[$s['key']] ?? null)
                            <div class="flow-stage" style="flex-grow: {{ $s['count'] + $base }}">
                                <button type="button" class="flow-seg {{ $focusRing }}"
                                        @click="select('{{ $s['key'] }}')" :aria-pressed="isOn('{{ $s['key'] }}').toString()"
                                        aria-label="{{ $s['label'] }}, {{ Format::number($s['count']) }} order" title="{{ $s['label'] }}">
                                    <canvas data-flow-canvas data-count="{{ $s['count'] }}" aria-hidden="true"></canvas>
                                    <span class="flow-seg-num">{{ Format::number($s['count']) }}</span>
                                </button>
                                <span class="flow-seg-label" aria-hidden="true">{{ $s['short'] }}</span>
                                @if ($exit)
                                    <button type="button" @click="select('{{ $exit['key'] }}')" :aria-pressed="isOn('{{ $exit['key'] }}').toString()"
                                            aria-label="{{ $exit['label'] }} · {{ Format::number($exit['count']) }} order, jalur keluar dari {{ $s['label'] }}"
                                            @class(['flow-exit', $focusRing, 'text-on-surface-muted!' => $exit['key'] === 'DIBATALKAN'])>
                                        <span aria-hidden="true">↳</span> {{ $exit['label'] }} · {{ Format::number($exit['count']) }}
                                    </button>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Panel detail menempel: hanya berubah oleh klik tahap/jalur di atas. --}}
        <div x-ref="panel" class="mt-6 -mx-2 rounded-md border-t border-outline-hairline px-2 pt-5" role="region" aria-live="polite"
             :aria-label="current.label + ', ' + current.count + ' order'">
            <div x-ref="panelBody">
                <div class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1">
                    <h3 class="text-dash-sub" x-text="title"></h3>
                    <p class="text-[22px] leading-none font-black tabular-nums" x-text="total"></p>
                </div>
                <p x-show="partial" x-cloak class="mt-1 text-body-sm text-on-surface-muted" x-text="partial"></p>
                <p x-show="current.orders.length === 0" x-cloak class="mt-3 text-body-md text-on-surface-muted">Tidak ada order di tahap ini.</p>
                <ul class="mt-3" x-show="current.orders.length > 0">
                    <template x-for="o in current.orders" :key="o.id">
                        <li class="flex flex-wrap items-center gap-x-4 gap-y-1 border-t border-outline-hairline py-3 sm:flex-nowrap">
                            <span class="w-16 shrink-0 text-dash-row tabular-nums" x-text="o.code"></span>
                            <span class="min-w-0 flex-1 basis-[calc(100%-5rem)] text-body-sm text-on-surface-muted sm:basis-0" x-text="o.seller + ' → ' + o.buyer"></span>
                            <span class="shrink-0 text-label-md font-bold tabular-nums" x-text="money(o.nilai)"></span>
                            <span class="flex w-[132px] shrink-0 items-center justify-end gap-2">
                                <template x-if="o.age !== null">
                                    <span class="flex items-center gap-2">
                                        <span class="age-bar" aria-hidden="true"><span :style="'width:' + ageWidth(o)"></span></span>
                                        <span class="text-label-sm font-semibold tabular-nums" x-text="num(o.age) + ' hari'"></span>
                                    </span>
                                </template>
                            </span>
                        </li>
                    </template>
                </ul>
                <div x-show="linkedRows.length > 0" x-cloak class="mt-3">
                    <button type="button" @click="toQueue()" data-press
                            class="inline-flex h-10 items-center rounded-sm text-label-md font-bold text-primary hover:underline underline-offset-4 {{ $focusRing }}">
                        Lihat di antrean ↓
                    </button>
                </div>
            </div>
        </div>
    </x-ui.card>

    @include('livewire.dashboard.partials.queue', ['queue' => $queue, 'groupIcons' => $groupIcons, 'queueEmpty' => 'Tidak ada sengketa, sertifikat, penugasan, atau verifikasi yang menunggu keputusan Anda.'])

    @include('livewire.dashboard.partials.running', ['running' => $running, 'runningEmpty' => 'Tidak ada inspeksi atau pengiriman yang sedang berjalan.'])

    {{-- ================= ZONA 3 · Gambaran total tercatat ================= --}}
    <section aria-labelledby="overview-title" class="space-y-6">
        <div>
            <p class="dash-eyebrow">Zona 3 · Gambaran</p>
            <h2 id="overview-title" class="mt-2 text-dash-title">Gambaran total tercatat</h2>
        </div>

        @include('livewire.dashboard.partials.metrics', ['metricRows' => $metricRows])

        {{-- Hasil akhir order: bagian dari order yang sudah berakhir (Selesai vs jalur keluar), dari angka tahap Sungai Order yang sama. --}}
        <x-ui.card :padding="false" class="p-[18px] sm:p-6" aria-labelledby="outcome-title" x-data="hasilAkhir({{ Js::from($outcomes) }})">
            <h3 id="outcome-title" class="text-dash-title">Hasil akhir order</h3>
            <p class="mt-1 text-body-sm text-on-surface-muted">Order yang sudah berakhir · Total tercatat</p>
            @if ($endedTotal === 0)
                <p class="mt-4 text-body-md text-on-surface-muted">Belum ada order yang berakhir.</p>
            @else
                {{-- Segmen bilah dapat diklik dengan mouse; kontrol aksesibel (keyboard, pembaca layar, sentuh) ada di baris legenda. --}}
                <div class="outcome-bar mt-4" aria-hidden="true">
                    @foreach ($outcomes as $o)
                        @if ($o['count'] > 0)
                            <span @click="pick('{{ $o['key'] }}')" :class="sel && sel !== '{{ $o['key'] }}' ? 'opacity-35' : ''"
                                  style="flex-grow: {{ $o['count'] }}; background: var(--color-{{ $o['color'] }})"></span>
                        @endif
                    @endforeach
                </div>
                <ul class="mt-3 grid gap-x-6 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach ($outcomes as $o)
                        <li>
                            <button type="button" @click="pick('{{ $o['key'] }}')" :aria-expanded="(sel === '{{ $o['key'] }}').toString()" aria-controls="outcome-detail"
                                    @disabled($o['count'] === 0)
                                    :class="sel === '{{ $o['key'] }}' ? 'bg-surface-muted' : ''"
                                    class="flex min-h-11 w-full items-start gap-2.5 rounded-md px-1 py-2 text-left hover:bg-surface-muted disabled:cursor-default disabled:hover:bg-transparent {{ $focusRing }}">
                                <span class="mt-1.5 size-2.5 shrink-0" style="background: var(--color-{{ $o['color'] }})" aria-hidden="true"></span>
                                <span>
                                    <span class="block text-dash-row">{{ $o['label'] }} · {{ Format::number($o['count']) }}</span>
                                    <span class="block text-body-sm text-on-surface-muted tabular-nums">{{ (int) round(100 * $o['count'] / $endedTotal) }}% dari {{ Format::number($endedTotal) }} order berakhir</span>
                                </span>
                            </button>
                        </li>
                    @endforeach
                </ul>
                <div x-ref="detail" id="outcome-detail" style="height: 0; overflow: hidden" aria-live="polite">
                    <template x-if="current">
                        <div class="mt-3 border-t border-outline-hairline pt-4">
                            <div class="flex flex-wrap items-baseline justify-between gap-2">
                                <p class="text-dash-sub" x-text="current.label + ' · ' + num(current.count) + ' order · Total ' + money(current.nilai)"></p>
                                <button type="button" @click="pick(sel)" class="inline-flex h-10 items-center rounded-sm text-label-md font-bold text-primary hover:underline underline-offset-4 {{ $focusRing }}">Tutup</button>
                            </div>
                            <ul class="mt-1">
                                <template x-for="o in current.orders" :key="o.id">
                                    <li class="flex flex-wrap items-center gap-x-4 gap-y-1 border-t border-outline-hairline py-2.5 first:border-t-0">
                                        <span class="w-16 shrink-0 text-label-md font-bold tabular-nums" x-text="o.code"></span>
                                        <span class="min-w-0 flex-1 text-body-sm text-on-surface-muted" x-text="o.seller + ' → ' + o.buyer"></span>
                                        <span class="shrink-0 text-label-md font-bold tabular-nums" x-text="money(o.nilai)"></span>
                                    </li>
                                </template>
                            </ul>
                            <p x-show="current.count > current.orders.length" class="mt-1 text-body-sm text-on-surface-muted"
                               x-text="'Menampilkan ' + num(current.orders.length) + ' dari ' + num(current.count)"></p>
                        </div>
                    </template>
                </div>
            @endif
        </x-ui.card>

        <div class="grid gap-6 2xl:grid-cols-2">
            @include('livewire.dashboard.partials.stage-chart', ['chartStages' => $chartStages, 'chartTitle' => 'Volume per tahap', 'chartSub' => 'Order yang sedang di tiap tahap · posisi saat ini'])

            {{-- Komposisi material: donut + ledger (persen terhadap total seluruh set). --}}
            <x-ui.card :padding="false" class="min-w-0 p-[18px] sm:p-6" aria-labelledby="material-title" x-data="donutMaterial({{ Js::from($donutJs) }})">
                <h3 id="material-title" class="text-dash-title">Komposisi material</h3>
                <p class="mt-1 text-body-sm text-on-surface-muted">Order selesai · Total tercatat</p>

                @if ($materials['rows'] === [])
                    <p class="mt-5 text-body-md text-on-surface-muted">Belum ada order selesai yang tercatat dampaknya.</p>
                @else
                    <div class="mt-5 flex flex-col items-center gap-6 lg:flex-row lg:items-start">
                        <div class="donut-box">
                            <svg width="184" height="184" viewBox="0 0 184 184" aria-hidden="true">
                                @foreach ($arcs as $arc)
                                    <circle cx="92" cy="92" r="{{ $radius }}" stroke="var(--color-category-{{ $arc['color'] }})"
                                            stroke-dasharray="{{ round($arc['dash'], 2) }} {{ round($circ, 2) }}" stroke-dashoffset="{{ round($arc['offset'], 2) }}"
                                            :style="dim('{{ $arc['key'] }}') ? 'opacity:.3' : ''"
                                            @mouseenter="hover = '{{ $arc['key'] }}'" @mouseleave="hover = null" @click="pick('{{ $arc['key'] }}')"></circle>
                                @endforeach
                            </svg>
                            <div class="donut-center">
                                <p class="text-[26px] leading-none font-black tabular-nums" x-text="centerNum">{{ Format::number($materials['total_ton'], 1) }}</p>
                                <p class="mt-1 text-label-sm font-semibold text-on-surface-muted" x-text="centerLbl">ton · Total tercatat</p>
                                <p class="text-label-sm font-bold tabular-nums" x-show="centerSub" x-cloak x-text="centerSub"></p>
                            </div>
                        </div>

                        <ul class="w-full min-w-0 flex-1">
                            @foreach ($materials['rows'] as $row)
                                @php($pct = (int) round($row['share'] * 100))
                                <li>
                                    <button type="button" @click="pick('{{ $row['key'] }}')" @mouseenter="hover = '{{ $row['key'] }}'" @mouseleave="hover = null"
                                            :aria-expanded="(selected === '{{ $row['key'] }}').toString()" aria-controls="material-detail"
                                            :class="selected === '{{ $row['key'] }}' ? 'bg-surface-muted' : ''"
                                            class="flex w-full items-center gap-3 rounded-md border-t border-outline-hairline px-1 py-2.5 text-left hover:bg-surface-muted {{ $focusRing }}">
                                        @if ($row['photo'])
                                            <img src="{{ $row['photo'] }}" alt="" class="size-12 shrink-0 rounded-[6px] object-cover">
                                        @else
                                            <span class="size-12 shrink-0 rounded-[6px] bg-outline-hairline" aria-hidden="true"></span>
                                        @endif
                                        <span class="min-w-0 flex-1">
                                            <span class="flex items-center gap-2 text-dash-row">
                                                <span class="size-2.5 shrink-0" style="background: var(--color-category-{{ $row['color'] ?? 'lainnya' }})" aria-hidden="true"></span>
                                                {{ $row['nama'] }}
                                            </span>
                                            <span class="mt-1.5 block h-1.5 w-full rounded-full bg-outline-hairline" aria-hidden="true">
                                                <span class="block h-full rounded-full" style="width: {{ max(1, round($row['share'] * 100, 1)) }}%; background: var(--color-category-{{ $row['color'] ?? 'lainnya' }})"></span>
                                            </span>
                                            <span class="mt-1 block text-body-sm text-on-surface-muted tabular-nums">
                                                {{ $pct === 0 && $row['share'] > 0 ? '<1' : $pct }}% dari total {{ Format::number($materials['total_ton'], 1) }} ton · {{ Format::number($row['ton'], 1) }} ton · {{ Format::rupiahShort($row['nilai']) }}
                                            </span>
                                        </span>
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    {{-- Panel detail kategori: mengembang di tempat (spring Gentle), ditutup dengan klik ulang atau "Tutup". --}}
                    <div x-ref="detail" id="material-detail" class="rounded-md" style="height: 0; overflow: hidden" aria-live="polite">
                        <template x-if="sel">
                            <div class="mt-4 border-t border-outline-hairline pt-4">
                                <div class="flex flex-wrap items-baseline justify-between gap-2">
                                    <p class="text-dash-sub" x-text="sel.nama + ' · ' + num(sel.count) + ' order'"></p>
                                    <button type="button" @click="close()" class="inline-flex h-10 items-center rounded-sm text-label-md font-bold text-primary hover:underline underline-offset-4 {{ $focusRing }}">Tutup</button>
                                </div>
                                <p class="text-body-sm text-on-surface-muted" x-text="ton(sel.ton) + ' · ' + money(sel.nilai) + ' · ' + sel.kategori.join(', ')"></p>
                                <ul class="mt-2">
                                    <template x-for="o in sel.orders" :key="o.id">
                                        <li class="flex flex-wrap items-center gap-x-4 gap-y-1 border-t border-outline-hairline py-2.5">
                                            <span class="w-16 shrink-0 text-label-md font-bold tabular-nums" x-text="o.code"></span>
                                            <span class="min-w-0 flex-1 text-body-sm text-on-surface-muted" x-text="o.seller + ' → ' + o.buyer"></span>
                                            <span class="shrink-0 text-label-md font-bold tabular-nums" x-text="ton(o.ton) + ' · ' + money(o.nilai)"></span>
                                        </li>
                                    </template>
                                </ul>
                                <p x-show="sel.count > sel.orders.length" class="mt-1 text-body-sm text-on-surface-muted"
                                   x-text="'Menampilkan ' + num(sel.orders.length) + ' dari ' + num(sel.count)"></p>
                            </div>
                        </template>
                    </div>
                @endif
            </x-ui.card>
        </div>
    </section>
</div>

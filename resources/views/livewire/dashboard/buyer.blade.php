{{--
    Dasbor Buyer — varian "Senja di Sawah" (panduan dashboard Senja §6.3, keputusan PM 22 Nov 2026).
    Urutan: Zona 1 "Perlu tindakan Anda" → Zona 2 "Sedang berjalan" (order + pelacak rute) → Zona 3 "Gambaran total tercatat".
    Semua angka dari DashboardService::buyer(); hitungan tahap memakai orderFlow() yang sama dengan Sungai Order Admin.
--}}
@use('App\Support\Format')
@php
    $focusRing = 'focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus-ring';
    $groupIcons = ['bayar' => 'receipt', 'rfq' => 'chat', 'terima' => 'truck'];
    $m = $metrics;

    [$doneValue, $doneUnit] = Format::rupiahParts($m['nilai_selesai']);
    [$billValue, $billUnit] = Format::rupiahParts($m['tagihan_terbuka']);
    [$saveValue, $saveUnit] = Format::rupiahParts($m['hemat']);
    $metricRows = [
        [
            'label' => 'Nilai pembelian selesai', 'value' => $doneValue, 'unit' => $doneUnit,
            'segs' => [[$m['nilai_selesai'], 'primary'], [$m['nilai_berjalan'], 'flow-segment-line']],
            'legend' => [['primary', 'selesai, '.Format::number($m['order_selesai']).' order'], ['flow-segment-line', Format::rupiahShort($m['nilai_berjalan']).' masih berjalan']],
        ],
        [
            'label' => 'Material diterima', 'value' => Format::number($m['ton_diterima'], 1), 'unit' => 'ton',
            'segs' => [[$m['ton_diterima'], 'primary'], [$m['ton_berjalan'], 'flow-segment-line']],
            'legend' => [['primary', 'dari order selesai'], ['flow-segment-line', Format::number($m['ton_berjalan'], 1).' ton masih di alur']],
        ],
        [
            'label' => 'Tagihan terbuka', 'value' => $billValue, 'unit' => $billUnit,
            'segs' => [[$m['tagihan_terbuka'], 'warning'], [$m['tagihan_dibayar'], 'primary']],
            'legend' => array_values(array_filter([
                ['warning', 'belum dibayar'],
                ['primary', Format::rupiahShort($m['tagihan_dibayar']).' sudah dibayar'],
                $m['tagihan_lewat'] > 0 ? ['error', Format::number($m['tagihan_lewat']).' lewat jatuh tempo'] : null,
            ])),
        ],
        [
            'label' => 'Penghematan biaya', 'value' => $saveValue, 'unit' => $saveUnit,
            'segs' => [[$m['hemat'], 'primary'], [max(0, $m['nilai_selesai'] - $m['hemat']), 'flow-segment-line']],
            'legend' => [['primary', 'hemat'], ['flow-segment-line', 'dari '.Format::rupiahShort($m['nilai_selesai']).' nilai pembelian selesai']],
        ],
    ];
    $chartStages = collect($flow['stages'])->map(fn ($s) => collect($s)->only(['key', 'label', 'short', 'count', 'kg', 'nilai', 'orders']))->values();
@endphp

<div class="space-y-6">
    @include('livewire.dashboard.partials.queue', ['queueEmpty' => 'Tidak ada tagihan, penawaran, atau penerimaan yang menunggu keputusan Anda.'])

    @include('livewire.dashboard.partials.running', ['runningEmpty' => 'Belum ada order pembelian yang sedang berjalan.'])

    {{-- Pelacak rute (panduan §7.7): pilih pengiriman di daftar untuk memfokuskan rutenya. --}}
    <x-ui.card :padding="false" class="p-[18px] sm:p-6" aria-labelledby="route-title">
        <h3 id="route-title" class="text-dash-title">Pengiriman ke Anda · {{ Format::number($shipments['count']) }}</h3>
        @if ($shipments['items'] === [])
            <p class="mt-4 text-body-md text-on-surface-muted">Belum ada pengiriman yang dijadwalkan ke gudang Anda.</p>
        @else
            <div x-data="ruteKirim({{ Js::from($shipments['items']) }})" class="mt-4 grid gap-5 xl:grid-cols-[minmax(0,1.4fr)_minmax(0,1fr)]">
                <div class="min-w-0">
                    <div x-ref="box" class="route-box rounded-md" aria-live="polite">
                        <div class="flex flex-wrap items-baseline justify-between gap-2">
                            <p class="text-dash-sub" x-text="current.title"></p>
                            <p class="text-label-md font-bold" :class="current.stage === 2 ? 'text-on-surface' : 'text-primary'" x-text="current.status"></p>
                        </div>
                        <p class="mt-0.5 text-label-sm font-semibold text-on-surface-muted">Posisi menurut status manifes (tanpa GPS)</p>
                        <svg viewBox="0 0 600 120" class="mt-2" role="img" :aria-label="'Rute ' + current.from + ' ke ' + current.to + ', posisi: ' + current.status">
                            <path x-ref="path" class="route-base" d="M20,92 C150,104 210,24 330,36 C430,46 500,26 580,22"></path>
                            <path x-ref="done" class="route-done" d="M20,92 C150,104 210,24 330,36 C430,46 500,26 580,22" :style="'stroke-dasharray:' + len"></path>
                            <circle cx="20" cy="92" r="5" fill="var(--color-on-surface)"></circle>
                            <circle cx="580" cy="22" r="5" fill="var(--color-on-surface)"></circle>
                            <g x-ref="marker"><circle r="11" fill="var(--color-primary)"></circle><circle r="4" fill="var(--color-surface)"></circle></g>
                        </svg>
                        <div class="flex justify-between gap-4 text-label-md font-bold">
                            <span x-text="current.from"></span>
                            <span class="text-right" x-text="current.to"></span>
                        </div>
                    </div>
                    <p class="mt-3 flex flex-wrap gap-x-6 gap-y-1 text-body-sm text-on-surface-muted">
                        <span x-show="current.armada">Armada · <b class="text-on-surface" x-text="current.armada + (current.plat ? ' (' + current.plat + ')' : '')"></b></span>
                        <span>Muatan · <b class="text-on-surface" x-text="current.muatan"></b></span>
                        <span x-show="current.jarak">Jarak · <b class="text-on-surface" x-text="current.jarak"></b></span>
                        <span>Seller · <b class="text-on-surface" x-text="current.seller"></b></span>
                    </p>
                    <div class="mt-3 flex flex-wrap gap-2" role="group" aria-label="Titik perjalanan">
                        <template x-for="(m, i) in current.milestones" :key="i">
                            <button type="button" class="ms-btn {{ $focusRing }}" :data-state="msState(i)" @click="pickMs(i)" :aria-pressed="(ms === i).toString()">
                                <i aria-hidden="true"></i><span x-text="m.label"></span>
                            </button>
                        </template>
                    </div>
                    <p x-show="ms !== null" x-cloak class="mt-2 rounded-md bg-surface-muted px-3 py-2 text-body-sm font-semibold" aria-live="polite"
                       x-text="ms === null ? '' : current.milestones[ms].label + ' · ' + (current.milestones[ms].time ?? 'waktu tidak tercatat di manifes')"></p>
                </div>

                <ul class="min-w-0" aria-label="Daftar pengiriman">
                    @foreach ($shipments['items'] as $i => $s)
                        <li class="border-t border-outline-hairline first:border-t-0">
                            <button type="button" class="ship-pick flex items-center gap-4 px-2 py-3 {{ $focusRing }}" @click="pick({{ $i }})" :aria-pressed="(sel === {{ $i }}).toString()">
                                <span class="date-block w-[72px] shrink-0">
                                    @if ($s['date'])
                                        <b>{{ $s['date']['day'] }}</b><span>{{ $s['date']['month'] }}<br>jadwal</span>
                                    @else
                                        <b aria-hidden="true">–</b><span>belum dijadwalkan</span>
                                    @endif
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span class="block text-dash-row">{{ $s['title'] }}</span>
                                    <span class="mt-0.5 block text-body-sm text-on-surface-muted">{{ $s['from'] }} → {{ $s['to'] }}</span>
                                    <span @class(['mt-1 block text-label-md font-semibold', 'text-primary' => $s['stage'] === 1, 'text-on-surface' => $s['stage'] !== 1])>{{ $s['status'] }}</span>
                                </span>
                            </button>
                        </li>
                    @endforeach
                </ul>
            </div>
            @if ($shipments['count'] > count($shipments['items']))
                <p class="mt-3 text-body-sm text-on-surface-muted">Menampilkan {{ Format::number(count($shipments['items'])) }} dari {{ Format::number($shipments['count']) }}</p>
            @endif
        @endif
    </x-ui.card>

    {{-- ================= ZONA 3 · Gambaran total tercatat ================= --}}
    <section aria-labelledby="overview-title" class="space-y-6">
        <div>
            <p class="dash-eyebrow">Zona 3 · Gambaran</p>
            <h2 id="overview-title" class="mt-2 text-dash-title">Gambaran total tercatat</h2>
        </div>

        @include('livewire.dashboard.partials.metrics')

        @include('livewire.dashboard.partials.stage-chart', ['chartTitle' => 'Pembelian Anda per tahap', 'chartSub' => 'Order Anda yang sedang di tiap tahap · posisi saat ini'])

        {{-- Material yang bisa dibeli: kartu foto (panduan §7.8); seluruh kartu menuju formulir RFQ untuk listing itu. --}}
        <x-ui.card :padding="false" class="p-[18px] sm:p-6" aria-labelledby="rec-title">
            <h3 id="rec-title" class="text-dash-title">Material yang bisa Anda beli · {{ Format::number($recommend['count']) }}</h3>
            <p class="mt-1 text-body-sm text-on-surface-muted">{{ $recommend['by_demand'] ? 'Kategori kebutuhan terbuka Anda lebih dulu' : 'Listing aktif terbaru' }} · stok tersedia</p>
            @if ($recommend['items'] === [])
                <p class="mt-4 text-body-md text-on-surface-muted">Belum ada listing aktif dari Seller lain.</p>
            @else
                <ul class="mt-4 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach ($recommend['items'] as $l)
                        @php($pct = (int) round($l['terjual'] * 100))
                        <li>
                            <a @if ($l['url']) href="{{ $l['url'] }}" @endif
                               @class(['block h-full overflow-hidden rounded-[16px] border border-line bg-field', 'transition-shadow hover:shadow-[var(--shadow-raised)] '.$focusRing => $l['url']])>
                                <span class="relative block h-36 bg-outline-hairline">
                                    @if ($l['photo'])
                                        <img src="{{ $l['photo'] }}" alt="" class="size-full object-cover">
                                        <span class="photo-scrim" aria-hidden="true"></span>
                                    @endif
                                    <span class="absolute bottom-3 left-3 flex flex-wrap gap-1.5">
                                        <span class="photo-label">Stok {{ Format::number($l['stok_kg'] / 1000, 1) }} ton</span>
                                        @if ($l['verified'])
                                            <span class="photo-label">Terverifikasi DLH</span>
                                        @endif
                                    </span>
                                </span>
                                <span class="block p-4">
                                    <span class="block text-dash-row">{{ $l['judul'] }}</span>
                                    <span class="mt-0.5 block text-body-sm text-on-surface-muted">{{ collect([$l['seller'], $l['lokasi']])->filter()->implode(' · ') }}</span>
                                    @if ($l['match'])
                                        <span class="mt-1.5 flex items-center gap-1.5 text-label-sm font-bold text-primary"><x-ui.icon name="check" class="size-4" /> Sesuai kebutuhan Anda</span>
                                    @endif
                                    <span class="mini-bar mt-3" aria-hidden="true">
                                        @if ($l['terjual'] > 0)
                                            <span style="width: {{ round($l['terjual'] * 100, 1) }}%; background: var(--color-primary)"></span>
                                        @endif
                                    </span>
                                    <span class="mt-2 block text-body-sm font-medium text-on-surface-muted tabular-nums">
                                        <b class="text-on-surface">{{ Format::rupiahShort($l['harga_per_kg'] * 1000) }} per ton</b>
                                        · terjual {{ $pct === 0 && $l['terjual'] > 0 ? '<1' : $pct }}% dari {{ Format::number($l['volume_kg'] / 1000, 1) }} ton
                                    </span>
                                    @if ($l['url'])
                                        <span class="mt-3 inline-flex items-center gap-1 text-label-md font-bold text-primary">Ajukan RFQ <x-ui.icon name="arrow-right" class="size-4" /></span>
                                    @endif
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-ui.card>
    </section>
</div>

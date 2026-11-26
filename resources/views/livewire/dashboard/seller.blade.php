{{--
    Dasbor Seller — varian "Senja di Sawah" (panduan dashboard Senja §6.2, keputusan PM 22 Nov 2026).
    Urutan: Zona 1 "Perlu tindakan Anda" → Zona 2 "Sedang berjalan" → Zona 3 "Gambaran total tercatat".
    Semua angka dari DashboardService::seller(); hitungan tahap memakai orderFlow() yang sama dengan Sungai Order Admin.
--}}
@use('App\Support\Format')
@use('App\Support\MaterialPhoto')
@php
    $focusRing = 'focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus-ring';
    $groupIcons = ['konfirmasi' => 'file', 'rfq' => 'chat', 'bukti' => 'receipt'];
    $m = $metrics;

    [$doneValue, $doneUnit] = Format::rupiahParts($m['nilai_selesai']);
    [$unpaidValue, $unpaidUnit] = Format::rupiahParts($m['invoice_belum']);
    $metricRows = [
        [
            'label' => 'Nilai order selesai', 'value' => $doneValue, 'unit' => $doneUnit,
            'segs' => [[$m['nilai_selesai'], 'primary'], [$m['nilai_berjalan'], 'flow-segment-line']],
            'legend' => [['primary', 'selesai, '.Format::number($m['order_selesai']).' order'], ['flow-segment-line', Format::rupiahShort($m['nilai_berjalan']).' masih berjalan']],
        ],
        [
            'label' => 'Tonase terdaur ulang', 'value' => Format::number($m['ton_terdaur'], 1), 'unit' => 'ton',
            'segs' => [[$m['ton_terdaur'], 'primary'], [$m['ton_berjalan'], 'flow-segment-line']],
            'legend' => [['primary', 'dari order selesai'], ['flow-segment-line', Format::number($m['ton_berjalan'], 1).' ton masih di alur']],
        ],
        [
            'label' => 'Invoice belum dibayar', 'value' => $unpaidValue, 'unit' => $unpaidUnit,
            'segs' => [[$m['invoice_belum'], 'warning'], [$m['invoice_dibayar'], 'primary']],
            'legend' => array_values(array_filter([
                ['warning', 'menunggu Buyer'],
                ['primary', Format::rupiahShort($m['invoice_dibayar']).' sudah dibayar'],
                $m['invoice_lewat'] > 0 ? ['error', Format::number($m['invoice_lewat']).' lewat jatuh tempo'] : null,
            ])),
        ],
        [
            'label' => 'Stok listing aktif', 'value' => Format::number($m['stok_ton'], 1), 'unit' => 'ton',
            'segs' => [[$m['stok_ton'], 'primary'], [$m['terjual_ton'], 'flow-segment-line']],
            'legend' => [['primary', 'sisa stok, '.Format::number($m['listing_aktif']).' listing'], ['flow-segment-line', Format::number($m['terjual_ton'], 1).' ton terjual']],
        ],
    ];
    $chartStages = collect($flow['stages'])->map(fn ($s) => collect($s)->only(['key', 'label', 'short', 'count', 'kg', 'nilai', 'orders']))->values();
@endphp

<div class="space-y-6">
    @include('livewire.dashboard.partials.queue', ['queueEmpty' => 'Tidak ada order, RFQ, atau bukti bayar yang menunggu keputusan Anda.'])

    @include('livewire.dashboard.partials.running', ['runningEmpty' => 'Belum ada order yang berjalan atau RFQ dalam negosiasi.'])

    {{-- ================= ZONA 3 · Gambaran total tercatat ================= --}}
    <section aria-labelledby="overview-title" class="space-y-6">
        <div>
            <p class="dash-eyebrow">Zona 3 · Gambaran</p>
            <h2 id="overview-title" class="mt-2 text-dash-title">Gambaran total tercatat</h2>
        </div>

        @include('livewire.dashboard.partials.metrics')

        @include('livewire.dashboard.partials.stage-chart', ['chartTitle' => 'Order Anda per tahap', 'chartSub' => 'Order Anda yang sedang di tiap tahap · posisi saat ini'])

        {{-- Kartu listing foto (panduan §7.8): foto material asli, label stok blok solid di atas scrim. --}}
        <x-ui.card :padding="false" class="p-[18px] sm:p-6" aria-labelledby="listing-title">
            <h3 id="listing-title" class="text-dash-title">Listing aktif Anda · {{ Format::number($listings['count']) }}</h3>
            <p class="mt-1 text-body-sm text-on-surface-muted">Dipublikasikan · stok dan bagian terjual saat ini</p>
            @if ($listings['items'] === [])
                <p class="mt-4 text-body-md text-on-surface-muted">Belum ada listing yang dipublikasikan.</p>
            @else
                <ul class="mt-4 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach ($listings['items'] as $l)
                        @php($pct = (int) round($l['terjual'] * 100))
                        <li class="overflow-hidden rounded-[16px] border border-line bg-field">
                            <div class="relative h-36 bg-outline-hairline">
                                @if ($l['photo'])
                                    <img src="{{ $l['photo'] }}" alt="" class="size-full object-cover">
                                    <span class="photo-scrim" aria-hidden="true"></span>
                                @endif
                                <span class="photo-label absolute bottom-3 left-3">Stok {{ Format::number($l['stok_kg'] / 1000, 1) }} ton</span>
                            </div>
                            <div class="p-4">
                                <div class="flex items-start gap-3">
                                    <div class="min-w-0 flex-1">
                                        <p class="text-dash-row">{{ $l['judul'] }}</p>
                                        <p class="mt-0.5 text-body-sm text-on-surface-muted">{{ collect([$l['kategori'], $l['lokasi']])->filter()->implode(' · ') }}</p>
                                    </div>
                                    {{-- Cincin kemajuan: bagian volume yang sudah terjual. --}}
                                    <svg class="size-11 shrink-0 -rotate-90" viewBox="0 0 44 44" aria-hidden="true">
                                        <circle cx="22" cy="22" r="18" fill="none" stroke="var(--color-outline-hairline)" stroke-width="5"></circle>
                                        <circle cx="22" cy="22" r="18" fill="none" stroke="var(--color-primary)" stroke-width="5"
                                                stroke-dasharray="{{ round(2 * M_PI * 18 * $l['terjual'], 2) }} {{ round(2 * M_PI * 18, 2) }}"></circle>
                                    </svg>
                                </div>
                                <span class="mini-bar mt-3" aria-hidden="true">
                                    @if ($l['terjual'] > 0)
                                        <span style="width: {{ round($l['terjual'] * 100, 1) }}%; background: var(--color-primary)"></span>
                                    @endif
                                </span>
                                <p class="mt-2 text-body-sm font-medium text-on-surface-muted tabular-nums">
                                    <span class="font-bold text-on-surface">{{ Format::rupiahShort($l['harga_per_kg'] * 1000) }} per ton</span>
                                    · terjual {{ $pct === 0 && $l['terjual'] > 0 ? '<1' : $pct }}% dari {{ Format::number($l['volume_kg'] / 1000, 1) }} ton
                                </p>
                            </div>
                        </li>
                    @endforeach
                </ul>
                @if ($listings['count'] > count($listings['items']))
                    <p class="mt-3 text-body-sm text-on-surface-muted">Menampilkan {{ Format::number(count($listings['items'])) }} dari {{ Format::number($listings['count']) }}</p>
                @endif
            @endif
        </x-ui.card>

        {{-- Kebutuhan Buyer terbuka: peluang listing baru, menuju Papan Kebutuhan. --}}
        <x-ui.card :padding="false" class="p-[18px] sm:p-6" aria-labelledby="demand-title">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h3 id="demand-title" class="text-dash-title">Kebutuhan Buyer terbuka · {{ Format::number($demands_count) }}</h3>
                    <p class="mt-1 text-body-sm text-on-surface-muted">Dari perusahaan lain · terbaru</p>
                </div>
                @if (Route::has('seller.demands.index'))
                    <x-ui.button variant="secondary" :href="route('seller.demands.index')" data-press>Buka Papan Kebutuhan</x-ui.button>
                @endif
            </div>
            @if ($demands->isEmpty())
                <p class="mt-4 text-body-md text-on-surface-muted">Belum ada kebutuhan Buyer yang terbuka.</p>
            @else
                <ul class="mt-3">
                    @foreach ($demands as $demand)
                        @php($photo = MaterialPhoto::forCategory($demand->category->nama ?? null))
                        <li class="flex items-center gap-3 border-t border-outline-hairline py-3 first:border-t-0">
                            @if ($photo)
                                <img src="{{ $photo }}" alt="" class="size-12 shrink-0 rounded-[6px] object-cover">
                            @else
                                <x-ui.monogram :name="$demand->category->nama ?? 'Kebutuhan'" />
                            @endif
                            <div class="min-w-0 flex-1">
                                <p class="text-dash-row">{{ $demand->category->nama ?? 'Kebutuhan' }}</p>
                                <p class="mt-0.5 text-body-sm text-on-surface-muted">{{ $demand->company->nama ?? '–' }} · {{ Format::kg($demand->volume_kg) }}</p>
                            </div>
                            @if ($demand->berlaku_sampai)
                                <p class="date-block shrink-0" title="Berlaku sampai {{ Format::date($demand->berlaku_sampai) }}">
                                    <b>{{ \Illuminate\Support\Carbon::parse($demand->berlaku_sampai)->format('j') }}</b>
                                    <span>{{ \Illuminate\Support\Str::after(Format::dayMonth($demand->berlaku_sampai), ' ') }}<br>berlaku s.d.</span>
                                </p>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-ui.card>
    </section>
</div>

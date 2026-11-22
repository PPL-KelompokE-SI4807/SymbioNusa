{{--
    Dasbor Inspector — varian "Senja di Sawah" (panduan dashboard Senja §6.4, keputusan PM 22 Nov 2026).
    Urutan: Zona 1 "Perlu tindakan Anda" → Zona 2 "Sedang berjalan" (garis waktu jam, 7 hari) → Zona 3 "Gambaran total
    tercatat" + kartu kredensial. Semua angka dari DashboardService::inspector() untuk Inspector yang login.
--}}
@use('App\Support\Format')
@php
    $focusRing = 'focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus-ring';
    $groupIcons = ['tugas' => 'calendar', 'laporan' => 'flask', 'dokumen' => 'file', 'sertifikat' => 'shield'];
    $m = $metrics;
    $hasil = $m['lulus'] + $m['gagal'];
    $metricRows = [
        [
            'label' => 'Inspeksi selesai', 'value' => Format::number($m['selesai']), 'unit' => null,
            'segs' => [[$m['selesai'], 'primary'], [$m['berjalan'], 'flow-segment-line']],
            'legend' => [['primary', 'selesai'], ['flow-segment-line', Format::number($m['berjalan']).' masih berjalan']],
        ],
        [
            'label' => 'Hasil uji lulus', 'value' => Format::number($m['lulus']), 'unit' => $hasil > 0 ? 'dari '.Format::number($hasil) : null,
            'segs' => [[$m['lulus'], 'primary'], [$m['gagal'], 'error']],
            'legend' => $hasil > 0
                ? [['primary', (int) round(100 * $m['lulus'] / $hasil).'% lulus'], ['error', Format::number($m['gagal']).' gagal']]
                : [['outline', 'belum ada hasil final']],
        ],
        [
            'label' => 'Verifikasi dokumen selesai', 'value' => Format::number($m['dok_selesai']), 'unit' => null,
            'segs' => [[$m['dok_selesai'], 'primary'], [$m['dok_menunggu'], 'warning']],
            'legend' => [['primary', 'diperiksa'], ['warning', Format::number($m['dok_menunggu']).' menunggu']],
        ],
        [
            'label' => 'Inspeksi lapangan', 'value' => Format::number($m['lapangan']), 'unit' => null,
            'segs' => [[$m['lapangan'], 'primary'], [$m['desk'], 'flow-segment-line']],
            'legend' => [['primary', 'lapangan'], ['flow-segment-line', Format::number($m['desk']).' desk review']],
        ],
    ];
    $c = $credential;
@endphp

<div class="space-y-6">
    @include('livewire.dashboard.partials.queue', ['queueEmpty' => 'Tidak ada tugas inspeksi, laporan, atau berkas yang menunggu Anda.'])

    {{-- ================= ZONA 2 · Sedang berjalan: garis waktu jam ================= --}}
    <x-ui.card :padding="false" class="p-[18px] sm:p-6" aria-labelledby="running-title" x-data="garisWaktu({{ Js::from($timeline) }})">
        <p class="dash-eyebrow">Zona 2 · Berjalan</p>
        <h2 id="running-title" class="mt-2 text-dash-title">Sedang berjalan · {{ Format::number($m['berjalan']) }}</h2>

        @php($weekMax = max(1, collect($timeline['week'])->max('count')))
        <div class="mt-4">
            <p class="text-label-sm font-semibold text-on-surface-muted">Jadwal 7 hari · {{ $timeline['week'][0]['label'] }} s.d. {{ $timeline['week'][6]['label'] }}</p>
            <div class="mt-2 grid grid-cols-7 gap-1.5" role="tablist" aria-label="Pilih hari garis waktu" @keydown="keyDays($event)">
                @foreach ($timeline['week'] as $d)
                    <button type="button" role="tab" data-tab="{{ $d['date'] }}" aria-controls="tl-panel" @click="pickDay('{{ $d['date'] }}')"
                            :aria-selected="(day === '{{ $d['date'] }}').toString()" :tabindex="day === '{{ $d['date'] }}' ? 0 : -1"
                            class="week-day {{ $focusRing }}">
                        <span class="week-bar-track" aria-hidden="true">
                            @if ($d['count'] > 0)
                                <span class="week-bar" style="height: {{ round(100 * $d['count'] / $weekMax) }}%"></span>
                            @endif
                        </span>
                        <span class="text-label-md font-extrabold tabular-nums">{{ $d['count'] > 0 ? Format::number($d['count']) : '–' }}</span>
                        <span @class(['text-label-sm text-on-surface-muted', 'font-bold text-on-surface!' => $loop->first])>{{ $d['dow'] }}</span>
                        <span class="text-label-sm text-on-surface-muted tabular-nums">{{ $d['day'] }}</span>
                        <span class="sr-only">— {{ Format::number($d['count']) }} inspeksi pada {{ $d['label'] }}</span>
                    </button>
                @endforeach
            </div>
        </div>

        <div id="tl-panel" role="tabpanel" class="mt-5" :aria-label="'Garis waktu ' + dayLabel">
            <p class="text-dash-sub" x-text="(isToday ? 'Hari ini · ' : '') + dayLabel"></p>
            <p x-show="entries.length === 0" x-cloak class="mt-2 text-body-md text-on-surface-muted">Tidak ada inspeksi terjadwal.</p>

            <div x-ref="rail" class="tl mt-3 rounded-md" x-show="entries.length > 0">
                <template x-for="(e, idx) in entries" :key="e.id">
                    <div>
                        <div x-show="idx === nowIndex" class="tl-now" role="note">
                            <span><i aria-hidden="true"></i>SEKARANG · <span x-text="tl.now_label + ' WIB'"></span></span>
                        </div>
                        <div class="tl-item" :data-done="e.done.toString()" :data-next="(e.id === nextId).toString()">
                            <span class="tl-time" x-text="e.time"></span>
                            <span class="tl-node" aria-hidden="true"></span>
                            <button type="button" class="tl-card {{ $focusRing }}" @click="toggle(e.id)" :aria-expanded="(open === e.id).toString()">
                                <span class="flex flex-wrap items-baseline justify-between gap-x-3 gap-y-1">
                                    <span class="text-dash-row" x-text="e.title"></span>
                                    <span class="text-label-md font-semibold" :class="e.done ? 'text-on-surface-muted' : (e.id === nextId ? 'text-primary' : 'text-on-surface')"
                                          x-text="e.id === nextId ? 'Berikutnya · ' + e.status : e.status"></span>
                                </span>
                                <span class="mt-0.5 flex items-center gap-1.5 text-body-sm text-on-surface-muted">
                                    <x-ui.icon name="map-pin" class="size-4 shrink-0" />
                                    <span x-text="e.lokasi + ' · ' + e.mode"></span>
                                </span>
                            </button>
                            <div :data-detail="e.id" style="height: 0; overflow: hidden">
                                <dl class="mt-2 grid gap-x-6 gap-y-2 rounded-md bg-surface-muted px-4 py-3 text-body-sm sm:grid-cols-2">
                                    <div><dt class="text-on-surface-muted">Pihak</dt><dd class="font-semibold" x-text="e.pihak"></dd></div>
                                    <div><dt class="text-on-surface-muted">Muatan</dt><dd class="font-semibold tabular-nums" x-text="e.muatan"></dd></div>
                                    <div x-show="e.alamat"><dt class="text-on-surface-muted">Alamat Seller</dt><dd class="font-semibold" x-text="e.alamat"></dd></div>
                                    <div x-show="e.kontak"><dt class="text-on-surface-muted">Kontak</dt><dd class="font-semibold tabular-nums" x-text="e.kontak"></dd></div>
                                    <div class="sm:col-span-2" x-show="e.catatan"><dt class="text-on-surface-muted">Catatan mode</dt><dd class="font-semibold" x-text="e.catatan"></dd></div>
                                    <div class="sm:col-span-2" x-show="!e.done"><dd class="text-on-surface-muted">Mulai inspeksi dari dasbor segera hadir</dd></div>
                                </dl>
                            </div>
                        </div>
                    </div>
                </template>
                <div x-show="nowIndex === entries.length && entries.length > 0" class="tl-now" role="note">
                    <span><i aria-hidden="true"></i>SEKARANG · <span x-text="tl.now_label + ' WIB'"></span></span>
                </div>
            </div>
        </div>
    </x-ui.card>

    {{-- ================= ZONA 3 · Gambaran total tercatat ================= --}}
    <section aria-labelledby="overview-title" class="space-y-6">
        <div>
            <p class="dash-eyebrow">Zona 3 · Gambaran</p>
            <h2 id="overview-title" class="mt-2 text-dash-title">Gambaran total tercatat</h2>
        </div>

        @include('livewire.dashboard.partials.metrics')

        {{-- Kartu kredensial (panduan §7.11): status dihitung dari tanggal berakhir sebenarnya. --}}
        <x-ui.card :padding="false" class="p-[18px] sm:p-6" aria-labelledby="cred-title">
            <h3 id="cred-title" class="text-dash-title">Kredensial Anda</h3>
            @if ($c === null)
                <p class="mt-3 text-body-md text-on-surface-muted">Profil Inspector Anda belum terdaftar. Hubungi Admin DLH untuk pendaftaran.</p>
            @else
                <div class="mt-4 flex flex-wrap items-center gap-x-5 gap-y-3 border-t border-outline-hairline pt-4">
                    <x-ui.icon name="shield" class="size-6 shrink-0 text-on-surface-muted" />
                    <div class="min-w-0 flex-1 basis-[calc(100%-3rem)] sm:basis-0">
                        <p class="text-dash-row">Sertifikat kompetensi · {{ $c['nomor'] ?: 'tanpa nomor' }}</p>
                        <p class="mt-0.5 text-body-sm text-on-surface-muted">{{ collect([$c['wilayah'] ? 'Wilayah '.$c['wilayah'] : null, $c['keahlian'] ? implode(', ', $c['keahlian']) : null])->filter()->implode(' · ') }}</p>
                        <p class="mt-1.5 flex flex-wrap items-center gap-x-3 gap-y-1">
                            <span @class(['flex items-center gap-1.5 text-label-md font-bold', 'text-success' => $c['tone'] === 'success', 'text-warning' => $c['tone'] === 'warning', 'text-error' => $c['tone'] === 'error', 'text-on-surface-muted' => $c['tone'] === 'meta'])>
                                <span class="size-2 bg-current" aria-hidden="true"></span>{{ $c['label'] }}
                            </span>
                            @if ($c['days'] !== null && $c['days'] >= 0 && $c['days'] <= 30)
                                <span class="age-bar w-28" aria-hidden="true"><span style="width: {{ max(4, round(100 * $c['days'] / 30)) }}%; background: var(--color-{{ $c['days'] <= 7 ? 'error' : 'warning' }})"></span></span>
                                <span class="text-label-sm font-bold tabular-nums">{{ Format::number($c['days']) }} hari lagi</span>
                            @elseif ($c['days'] !== null && $c['days'] > 30)
                                <span class="text-label-sm text-on-surface-muted tabular-nums">{{ Format::number($c['days']) }} hari lagi</span>
                            @endif
                        </p>
                    </div>
                    @if ($c['date'])
                        <p class="date-block shrink-0" title="Berlaku sampai {{ $c['date']['label'] }}">
                            <b @class(['text-error' => $c['tone'] === 'error'])>{{ $c['date']['day'] }}</b>
                            <span>{{ $c['date']['month'] }}<br>berlaku s.d.</span>
                        </p>
                    @endif
                    @if ($c['url'])
                        <x-ui.button variant="secondary" :href="$c['url']" data-press>{{ in_array($c['tone'], ['warning', 'error'], true) ? 'Perbarui sertifikat' : 'Buka profil' }}</x-ui.button>
                    @endif
                </div>
            @endif
        </x-ui.card>
    </section>
</div>

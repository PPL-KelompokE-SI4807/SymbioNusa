{{--
    Dasbor Admin / DLH (FND-04, DESIGN.md amandemen FND v8): tindakan lebih dulu, dampak di bawah.
    Urutan: Alur order (10 tahap, 3 kelompok) → Antrean tindakan + Material → pita Dampak.
--}}
@use('App\Support\Format')
@php
    $stuck = $flow['stuck'];
    $chartColors = ['bg-chart-lead', 'bg-chart-2', 'bg-chart-3', 'bg-chart-4', 'bg-chart-5'];
    [$hematValue, $hematUnit] = Format::rupiahParts($impact['hemat_seller'] + $impact['hemat_buyer']);
@endphp

<div class="space-y-6">
    {{-- Alur order --}}
    <x-ui.card :padding="false">
        <div class="flex flex-wrap items-center justify-between gap-3 px-6 pt-5 pb-1">
            <h2 class="text-headline-md">Alur order</h2>
            <p class="text-body-sm text-on-surface-subtle">Order aktif per tahap · RFQ terbuka {{ Format::number($flow['rfq_open']) }} belum jadi order</p>
        </div>

        <div class="overflow-x-auto px-6 pt-3 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus-ring" tabindex="0" role="region" aria-label="Tahapan alur order">
            <div class="flex min-w-[860px] gap-4">
                @foreach ($flow['groups'] as $group => $stages)
                    <section class="min-w-0" style="flex: {{ count($stages) }} 1 0%" aria-label="{{ $group }}">
                        <h3 class="mb-2 text-body-sm text-on-surface-subtle">{{ $group }}</h3>
                        <ol class="flex gap-2">
                            @foreach ($stages as $stage)
                                <li @class([
                                    'flex min-w-0 flex-1 flex-col gap-1.5 rounded-md px-2.5 py-3',
                                    'bg-stage-stuck text-on-tile-sun' => $stage['stuck'],
                                    'bg-primary-container text-on-primary-container' => $stage['done'] && ! $stage['stuck'],
                                    'bg-surface-muted text-on-surface' => ! $stage['stuck'] && ! $stage['done'],
                                ])>
                                    <span @class(['min-h-[2.4em] text-label-sm leading-snug', 'text-on-surface-muted' => ! $stage['stuck'] && ! $stage['done']])>{{ $stage['label'] }}</span>
                                    <span @class(['text-headline-lg tabular-nums', 'text-primary' => $stage['done'] && ! $stage['stuck']])>{{ Format::number($stage['count']) }}</span>
                                    @if ($stage['stuck'])
                                        <span class="sr-only">(tahap tertahan)</span>
                                    @endif
                                </li>
                            @endforeach
                        </ol>
                    </section>
                @endforeach
            </div>
        </div>

        <div class="flex flex-col gap-3 px-6 pt-4 pb-5 2xl:flex-row 2xl:items-center 2xl:gap-5">
            <p class="text-body-sm text-on-surface-subtle">
                Keluar alur:
                {{ collect($flow['exits'])->map(fn ($e) => $e['label'].' '.Format::number($e['count']))->implode(' · ') }}
            </p>
            @if ($stuck)
                <div class="flex min-w-0 flex-wrap items-center gap-x-3 gap-y-1 rounded-md border border-warning-line bg-warning-container px-4 py-2.5 text-on-warning-container 2xl:flex-1">
                    <x-ui.icon name="alert" class="size-5 shrink-0 text-warning" />
                    <p class="min-w-0 flex-1 basis-[calc(100%-2rem)] text-body-md sm:basis-0">
                        <span class="font-semibold">{{ $stuck['label'] }} tertahan.</span>
                        {{ Format::number($stuck['count']) }} order menunggu{{ $stuck['unassigned'] > 0 ? ', '.Format::number($stuck['unassigned']).' belum punya Inspector' : '' }}.
                    </p>
                    <a href="#antrean-tindakan" class="ml-8 inline-flex sm:ml-0 items-center gap-1 rounded-sm text-label-md font-semibold text-primary hover:underline underline-offset-4 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus-ring">
                        Lihat di antrean <x-ui.icon name="arrow-right" class="size-4" />
                    </a>
                </div>
            @endif
        </div>
    </x-ui.card>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_380px]">
        {{-- Antrean tindakan --}}
        <x-ui.card :padding="false" id="antrean-tindakan">
            <div x-data="{ tab: 'semua' }">
                <div class="flex flex-wrap items-center justify-between gap-3 px-6 pt-5 pb-2">
                    <h2 class="text-headline-md">Antrean tindakan</h2>
                    @php($urgentCount = collect($queue)->where('urgent', true)->count())
                    <div class="flex gap-1 rounded-sm bg-surface-muted p-1" role="tablist" aria-label="Saring antrean">
                        @foreach (['semua' => ['Semua', count($queue)], 'mendesak' => ['Mendesak', $urgentCount]] as $key => [$label, $n])
                            <button type="button" role="tab" @click="tab = '{{ $key }}'" :aria-selected="(tab === '{{ $key }}').toString()"
                                    class="flex h-8 items-center gap-1.5 rounded-[8px] px-3 text-label-md text-on-surface-muted aria-selected:bg-surface aria-selected:font-semibold aria-selected:text-on-surface aria-selected:shadow-card focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus-ring">
                                {{ $label }} <span class="text-body-sm text-on-surface-subtle">{{ $n }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>

                @if ($queue === [])
                    <p class="px-6 pb-6 text-body-md text-on-surface-muted">Tidak ada tindakan yang menunggu. Antrean akan terisi saat ada sengketa, verifikasi mitra, atau sertifikat yang hampir berakhir.</p>
                @else
                    @php($primaryUsed = false)
                    <ul class="px-3 pb-3">
                        @foreach ($queue as $item)
                            <li x-show="tab === 'semua' || {{ $item['urgent'] ? 'true' : 'false' }}"
                                class="flex flex-wrap items-center gap-x-4 gap-y-2 rounded-md p-3 hover:bg-surface-muted sm:flex-nowrap">
                                <x-ui.monogram :name="$item['party']" :person="$item['person'] ?? false" />
                                <div class="min-w-0 flex-1 basis-[calc(100%-3.5rem)] sm:basis-0">
                                    <p class="text-label-md font-semibold text-on-surface">{{ $item['title'] }}</p>
                                    <p class="mt-0.5 text-body-sm text-on-surface-subtle">{{ $item['meta'] }}</p>
                                </div>
                                <div class="shrink-0 pl-14 sm:w-28 sm:pl-0">
                                    @if ($item['urgent'])
                                        <p class="flex items-center gap-1.5 text-label-md font-semibold text-error"><x-ui.flag /> Mendesak</p>
                                    @endif
                                </div>
                                <div class="ml-auto flex shrink-0 justify-end sm:w-28">
                                    @if ($item['url'])
                                        <x-ui.button :href="$item['url']" :variant="$primaryUsed ? 'secondary' : 'primary'" class="h-9!">{{ $item['action'] }}</x-ui.button>
                                        @php($primaryUsed = true)
                                    @else
                                        <span class="text-body-sm text-on-surface-subtle" title="Halaman untuk tindakan ini segera hadir">{{ $item['action'] }} segera</span>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </x-ui.card>

        {{-- Material --}}
        <x-ui.card :padding="false">
            <div class="flex items-center justify-between gap-3 px-5 pt-5 pb-3">
                <h2 class="text-headline-md">Material terdaur ulang</h2>
                <p class="text-label-md text-on-surface-muted tabular-nums">{{ Format::ton($materials['total'], 1) }}</p>
            </div>
            @if ($materials['rows'] === [])
                <p class="px-5 pb-6 text-body-md text-on-surface-muted">Belum ada order selesai yang tercatat dampaknya.</p>
            @else
                <div class="mx-5 flex h-2.5 gap-0.5 overflow-hidden rounded-full" aria-hidden="true">
                    @foreach ($materials['rows'] as $i => $row)
                        <span class="{{ $chartColors[$i] }}" style="width: {{ max(2, $row['share']) }}%"></span>
                    @endforeach
                </div>
                <ul class="px-3 pt-2 pb-3">
                    @foreach ($materials['rows'] as $i => $row)
                        <li class="flex items-center gap-3 rounded-md p-2">
                            @if ($row['photo'])
                                <img src="{{ $row['photo'] }}" alt="" class="size-[52px] shrink-0 rounded-sm object-cover">
                            @else
                                <x-ui.monogram :name="$row['nama']" />
                            @endif
                            <div class="min-w-0 flex-1">
                                <p class="flex items-center gap-2 text-label-md font-semibold">
                                    <span class="size-2.5 shrink-0 rounded-[3px] {{ $chartColors[$i] }}" aria-hidden="true"></span>
                                    <span class="truncate">{{ $row['nama'] }}</span>
                                </p>
                                <p class="text-body-sm text-on-surface-subtle">{{ Format::number($row['orders']) }} order · {{ Format::number($row['sellers']) }} Seller</p>
                            </div>
                            <div class="text-right tabular-nums">
                                <p class="text-label-md font-semibold">{{ Format::ton($row['tonase'], 1) }}</p>
                                <p class="text-body-sm text-on-surface-subtle">{{ $row['share'] }}%</p>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-ui.card>
    </div>

    {{-- Dampak --}}
    <section class="rounded-lg bg-linear-to-br from-tile-forest-from to-tile-forest-to px-6 pt-5 pb-6 text-on-tile" aria-labelledby="dampak-title">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h2 id="dampak-title" class="text-headline-md">Dampak tercatat</h2>
            <p class="text-body-sm text-on-tile-muted">{{ Format::number($impact['orders']) }} order selesai{{ app()->environment('production') ? '' : ' · data demo' }}</p>
        </div>
        <dl class="mt-4 grid gap-y-5 sm:grid-cols-2 xl:grid-cols-4 xl:divide-x xl:divide-on-tile/15">
            @foreach ([
                ['Tonase terdaur ulang', Format::number($impact['tonase'], 1), 'ton', 'Dari order yang selesai', false],
                ['Penghematan biaya', $hematValue, $hematUnit, 'Seller '.Format::rupiahShort($impact['hemat_seller']).' · Buyer '.Format::rupiahShort($impact['hemat_buyer']), false],
                ['Emisi ditekan', Format::number($impact['co2e_kg'] / 1000, 1), 'ton CO₂e', 'Bersih, setelah emisi angkutan', false],
                ['Inspector aktif', Format::number($impact['inspector_aktif']), 'dari '.Format::number($impact['inspector_total']), Format::number($impact['sertifikat_berakhir']).' sertifikat berakhir ≤ 30 hari', $impact['sertifikat_berakhir'] > 0],
            ] as $i => [$label, $value, $unit, $caption, $warn])
                <div @class(['xl:px-5' => $i > 0, 'xl:pr-5' => $i === 0])>
                    <dt class="text-label-md text-on-tile-muted">{{ $label }}</dt>
                    <dd class="mt-1 flex items-baseline gap-1.5 tabular-nums">
                        <span class="text-headline-lg">{{ $value }}</span>
                        <span class="text-body-md font-medium">{{ $unit }}</span>
                    </dd>
                    <dd @class(['mt-1 text-body-sm', 'text-tile-sun-to' => $warn, 'text-on-tile-muted' => ! $warn])>{{ $caption }}</dd>
                </div>
            @endforeach
        </dl>
    </section>
</div>

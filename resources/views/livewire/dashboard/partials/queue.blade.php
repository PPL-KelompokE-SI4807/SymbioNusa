{{--
    Zona 1 "Perlu tindakan Anda" — antrean berkelompok bersama untuk semua peran (panduan dashboard Senja §7.5).
    Parameter: $queue ['groups' => [key, title, count, urgent, reason, action, pending_page, items[]]],
    $groupIcons (key => nama ikon), $queueEmpty (satu baris penyebab saat kosong).
    Item: id, po, title, meta, party, person, urgent, date?, url?, days_left?, age?.
    Hitungan judul kelompok = total sebenarnya; satu tombol terisi per wilayah (item pertama yang punya halaman).
--}}
@use('App\Support\Format')
@php
    $focusRing ??= 'focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus-ring';
    $groupIcons ??= [];
    $qGroups = collect($queue['groups']);
    $qTotal = $qGroups->sum('count');
    $qUrgent = $qGroups->sum('urgent');
    $primaryId = $qGroups->flatMap(fn ($g) => $g['items'])->first(fn ($i) => $i['url'] !== null)['id'] ?? null;
    $queueJs = $qGroups->mapWithKeys(fn ($g) => [$g['key'] => [
        'count' => $g['count'],
        'urgent' => $g['urgent'],
        'loaded' => count($g['items']),
        'urgentLoaded' => collect($g['items'])->where('urgent', true)->count(),
    ]]);
@endphp
<x-ui.card :padding="false" id="antrean-tindakan" class="scroll-mt-6 p-[18px] sm:p-6" aria-labelledby="queue-title" x-data="antreanAdmin({{ Js::from($queueJs) }})">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <p class="dash-eyebrow">Zona 1 · Tindakan</p>
            <h2 id="queue-title" class="mt-2 text-dash-title">Perlu tindakan Anda</h2>
        </div>
        @if ($qTotal > 0)
            <div x-ref="tabs" class="seg-ctl" role="tablist" aria-label="Saring antrean" @keydown="keyTabs($event)">
                <span class="seg-ctl-thumb" aria-hidden="true"></span>
                @foreach (['semua' => ['Semua', $qTotal], 'mendesak' => ['Mendesak', $qUrgent]] as $key => [$label, $n])
                    <button type="button" role="tab" id="queue-tab-{{ $key }}" data-tab="{{ $key }}" aria-controls="queue-panel"
                            @click="setTab('{{ $key }}')" :aria-selected="(tab === '{{ $key }}').toString()" :tabindex="tab === '{{ $key }}' ? 0 : -1"
                            aria-selected="{{ $key === 'semua' ? 'true' : 'false' }}" class="{{ $focusRing }}">
                        {{ $label }} ({{ Format::number($n) }})
                    </button>
                @endforeach
            </div>
        @endif
    </div>

    <div x-ref="list" id="queue-panel" role="tabpanel" :aria-labelledby="'queue-tab-' + tab">
        @forelse ($qGroups as $g)
            @php($urgentRank = 0)
            <section data-queue-group="{{ $g['key'] }}" x-show="groupShown('{{ $g['key'] }}')" x-ref="group-{{ $g['key'] }}"
                     class="mt-6 rounded-md" aria-labelledby="qg-{{ $g['key'] }}">
                <h3 id="qg-{{ $g['key'] }}" class="flex items-center gap-2 text-dash-row font-extrabold">
                    <x-ui.icon :name="$groupIcons[$g['key']] ?? 'clipboard'" class="size-5 shrink-0 text-on-surface-muted" />
                    <span>{{ $g['title'] }} · <span class="tabular-nums" x-text="num(groupCount('{{ $g['key'] }}'))">{{ Format::number($g['count']) }}</span></span>
                </h3>
                <p class="mt-0.5 pl-7 text-body-sm text-on-surface-muted">
                    {{ str_replace(':n', Format::number($g['count']), $g['reason']) }}@if ($g['pending_page']) · {{ $g['pending_page'] }}@endif
                </p>
                <ul class="mt-2">
                    @foreach ($g['items'] as $idx => $item)
                        @php($uIdx = $item['urgent'] ? $urgentRank++ : -1)
                        <li data-queue-row @if ($item['po']) data-queue-po="{{ $item['po'] }}" @endif
                            x-show="rowShown('{{ $g['key'] }}', {{ $idx }}, {{ $uIdx }})" @if ($idx >= 3) x-cloak @endif
                            class="flex flex-wrap items-center gap-x-4 gap-y-2 rounded-md border-t border-outline-hairline px-1 py-3 sm:flex-nowrap">
                            <x-ui.monogram :name="$item['party']" :person="$item['person']" />
                            <div class="min-w-0 flex-1 basis-[calc(100%-3.5rem)] sm:basis-0">
                                <p class="text-dash-row">{{ $item['title'] }}</p>
                                <p class="mt-0.5 text-body-sm text-on-surface-muted">{{ $item['meta'] }}</p>
                                @isset($item['days_left'])
                                    {{-- Hitung mundur dalam jendela 30 hari: panjang bilah = sisa hari; ≤ 7 hari merah, selain itu amber. --}}
                                    <p class="mt-1.5 flex items-center gap-2">
                                        <span class="age-bar w-28" aria-hidden="true"><span style="width: {{ max(4, round(100 * $item['days_left'] / 30)) }}%; background: var(--color-{{ $item['days_left'] <= 7 ? 'error' : 'warning' }})"></span></span>
                                        <span @class(['kaca-flag text-label-sm font-bold tabular-nums', 'text-error' => $item['days_left'] <= 7, 'text-warning' => $item['days_left'] > 7])>{{ $item['days_left'] === 0 ? 'Berakhir hari ini' : Format::number($item['days_left']).' hari lagi' }}</span>
                                    </p>
                                @endisset
                                @if (($item['age'] ?? null) !== null)
                                    {{-- Umur di status saat ini, hanya bila order_status_history mencatat waktunya. --}}
                                    <p class="mt-1.5 flex items-center gap-2">
                                        <span class="age-bar w-28" aria-hidden="true"><span style="width: {{ max(6, min(100, round(100 * $item['age'] / max(1, $item['age_max'] ?? $item['age'])))) }}%"></span></span>
                                        <span class="text-label-sm font-semibold tabular-nums">{{ Format::number($item['age']) }} hari</span>
                                    </p>
                                @endif
                            </div>
                            @if ($item['urgent'])
                                <p class="flex shrink-0 items-center gap-1.5 text-label-sm font-extrabold tracking-wide text-error">
                                    <span class="size-2 bg-error" aria-hidden="true"></span> <span class="kaca-flag">SEGERA</span>
                                </p>
                            @endif
                            @if ($item['date'])
                                <p class="date-block shrink-0" title="{{ $item['date']['label'] }}">
                                    <b @class(['text-error' => $item['date']['overdue']])>{{ $item['date']['day'] }}</b>
                                    <span>{{ $item['date']['month'] }}@isset($item['date_caption'])<br>{{ $item['date_caption'] }}@endisset</span>
                                </p>
                            @endif
                            @if ($item['url'] && $g['action'])
                                <div class="ml-auto shrink-0">
                                    <x-ui.button :href="$item['url']" :variant="$item['id'] === $primaryId ? 'primary' : 'secondary'" data-press>
                                        {{ $g['action'] }}
                                    </x-ui.button>
                                </div>
                            @endif
                        </li>
                    @endforeach
                </ul>
                <div class="mt-1 flex flex-wrap items-center gap-x-4" x-show="hiddenCount('{{ $g['key'] }}') > 0" x-cloak>
                    <button type="button" @click="toggle('{{ $g['key'] }}')" :aria-expanded="(!!expanded['{{ $g['key'] }}']).toString()"
                            class="inline-flex h-10 items-center rounded-sm text-label-md font-bold text-primary hover:underline underline-offset-4 {{ $focusRing }}"
                            x-text="expanded['{{ $g['key'] }}'] ? 'Tampilkan lebih sedikit' : 'Lihat ' + num(hiddenCount('{{ $g['key'] }}')) + ' lainnya'"></button>
                </div>
                @if ($g['count'] > count($g['items']))
                    <p class="mt-1 text-body-sm text-on-surface-muted">Menampilkan {{ Format::number(count($g['items'])) }} dari {{ Format::number($g['count']) }}</p>
                @endif
            </section>
        @empty
            <div class="mt-6">
                <p class="text-dash-sub">Semua selesai</p>
                <p class="mt-1 text-body-md text-on-surface-muted">{{ $queueEmpty }}</p>
            </div>
        @endforelse
    </div>
</x-ui.card>

{{--
    Grafik batang per tahap alur order — bersama. Parameter: $chartStages (stages dari DashboardService::orderFlow),
    $chartTitle, $chartSub (label periode/basis).
--}}
@php($focusRing ??= 'focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus-ring')
<x-ui.card :padding="false" class="min-w-0 p-[18px] sm:p-6" aria-labelledby="chart-title" x-data="grafikTahap({{ Js::from($chartStages) }})">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h3 id="chart-title" class="text-dash-title">{{ $chartTitle }}</h3>
            <p class="mt-1 text-body-sm text-on-surface-muted">{{ $chartSub }}</p>
        </div>
        <div x-ref="modes" class="seg-ctl" role="tablist" aria-label="Satuan grafik" @keydown="keyModes($event)">
            <span class="seg-ctl-thumb" aria-hidden="true"></span>
            @foreach (['ton' => 'Tonase', 'val' => 'Nilai'] as $key => $label)
                <button type="button" role="tab" data-tab="{{ $key }}" @click="setMode('{{ $key }}')" aria-controls="chart-bars"
                        :aria-selected="(mode === '{{ $key }}').toString()" :tabindex="mode === '{{ $key }}' ? 0 : -1"
                        aria-selected="{{ $key === 'ton' ? 'true' : 'false' }}" class="{{ $focusRing }}">{{ $label }}</button>
            @endforeach
        </div>
    </div>

    @if (collect($chartStages)->sum('count') === 0)
        <p class="mt-5 text-body-md text-on-surface-muted">Belum ada order di tahap mana pun.</p>
    @else
        {{-- Container query: batang vertikal bila kartu ≥ 780 px, horizontal (label di kiri) bila lebih sempit; label tidak pernah terpotong. --}}
        <div class="chart mt-4" id="chart-bars" role="tabpanel">
            <p class="mb-1 text-label-sm font-semibold text-on-surface-muted" x-text="mode === 'ton' ? 'ton' : 'Rp'">ton</p>
            <div x-ref="bars" class="bars" @mouseleave="hover = null">
                <div class="bar-crosshair" x-show="hover !== null" x-cloak :style="crossStyle"></div>
                <div class="bar-tip" x-show="hover !== null" x-cloak :style="tipStyle" x-text="tipText" role="status"></div>
                @foreach ($chartStages as $i => $s)
                    @php($barName = $s['short'] === $s['label'] ? $s['label'] : $s['short'].' ('.$s['label'].')')
                    <button type="button" data-bar class="bar-col {{ $focusRing }}" @click="pick({{ $i }})"
                            @mouseenter="tip({{ $i }})" @focus="tip({{ $i }})" @blur="hover = null"
                            :aria-pressed="(selected === {{ $i }}).toString()"
                            :aria-label="'{{ $barName }}: ' + exact(stages[{{ $i }}])" aria-label="{{ $barName }}">
                        <span class="bar-plot">
                            <span class="bar-value"></span>
                            <span class="bar" style="transform: scaleY(0)"></span>
                        </span>
                        <span class="bar-label" aria-hidden="true">{{ $s['short'] }}</span>
                    </button>
                @endforeach
            </div>
        </div>

        <div x-ref="list" class="rounded-md" aria-live="polite">
            <template x-if="sel">
                <div class="mt-4 border-t border-outline-hairline pt-4">
                    <div class="flex flex-wrap items-baseline justify-between gap-2">
                        <p class="text-dash-sub" x-text="sel.label + ' · ' + num(sel.count) + ' order'"></p>
                        <button type="button" @click="reset()" class="inline-flex h-10 items-center rounded-sm text-label-md font-bold text-primary hover:underline underline-offset-4 {{ $focusRing }}">Tampilkan semua</button>
                    </div>
                    <p x-show="sel.orders.length === 0" class="mt-1 text-body-md text-on-surface-muted">Tidak ada order di tahap ini.</p>
                    <ul class="mt-1">
                        <template x-for="o in sel.orders" :key="o.id">
                            <li class="flex flex-wrap items-center gap-x-4 gap-y-1 border-t border-outline-hairline py-2.5 first:border-t-0">
                                <span class="w-16 shrink-0 text-label-md font-bold tabular-nums" x-text="o.code"></span>
                                <span class="min-w-0 flex-1 text-body-sm text-on-surface-muted" x-text="o.seller + ' → ' + o.buyer"></span>
                                <span class="shrink-0 text-label-md font-bold tabular-nums" x-text="rowValue(o)"></span>
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

{{-- Isi satu tahap Sungai Order (Lab › OrderRiver, bagian "Isi tahap terpilih"). Hanya tahap terpilih yang tampil. --}}
@use('App\Support\Format')
@php
    $orders = array_slice($s['orders'], 0, 6);
    $maxAge = max(7, ...array_map(fn ($o) => $o['age'] ?? 0, $orders ?: [['age' => 0]]));
    $catColor = fn ($c) => 'var(--color-category-'.($c ?? 'lainnya').')';
@endphp
<div x-show="selected === '{{ $s['key'] }}'" x-cloak data-stage class="mt-5" aria-live="polite">
    <div class="mb-2 flex flex-wrap items-baseline justify-between gap-2">
        <h3 class="text-[16px] font-extrabold">{{ $s['label'] }} · {{ $s['count'] }} order</h3>
        <p class="text-[13px] text-meta">{{ Format::number($s['ton'], 1) }} ton{{ count($orders) < $s['count'] ? ' · menampilkan '.count($orders).' dari '.$s['count'] : '' }}</p>
    </div>
    @if ($orders === [])
        <p class="border-t border-line pt-3 text-[14px] text-meta">Tidak ada order di tahap ini.</p>
    @else
        <ul class="divide-y divide-line border-t border-line">
            @foreach ($orders as $o)
                <li>
                    <button type="button" @click="openOrder = openOrder === '{{ $o['code'] }}' ? null : '{{ $o['code'] }}'" :aria-expanded="(openOrder === '{{ $o['code'] }}').toString()"
                            class="grid w-full grid-cols-[auto_1fr_auto] items-center gap-x-3 gap-y-1 py-3 text-left sm:grid-cols-[110px_1fr_120px_150px_20px]">
                        <span class="mono text-[13px] font-bold">{{ $o['code'] }}</span>
                        <span class="min-w-0 truncate text-[14px] text-meta">
                            <span class="mr-1.5 inline-block size-2 rounded-full align-middle" style="background: {{ $catColor($o['color']) }}" aria-hidden="true"></span>
                            {{ $o['material'] }} · {{ $o['seller'] }} → {{ $o['buyer'] }}
                        </span>
                        <span class="hidden text-right text-[14px] font-bold tabular-nums sm:block">{{ Format::rupiah($o['nilai']) }}</span>
                        <span class="flex items-center justify-end gap-2">
                            @if ($o['age'] !== null)
                                <span class="hidden h-1.5 w-16 overflow-hidden rounded-full bg-track sm:block" aria-hidden="true">
                                    <span class="block h-full rounded-full" style="width: {{ ($o['age'] / $maxAge) * 100 }}%; background: {{ $o['age'] > 7 ? 'var(--danger)' : ($o['age'] > 3 ? 'var(--accent)' : 'var(--c-plan)') }}"></span>
                                </span>
                            @endif
                            <span class="text-[13px] font-bold tabular-nums {{ ($o['age'] ?? 0) > 7 ? 'text-danger' : '' }}">{{ $o['age'] === null ? '–' : Format::number($o['age']).' hari' }}</span>
                        </span>
                        <x-ui.icon name="chevron-down" class="hidden size-4 text-meta transition-transform sm:block" ::class="openOrder === '{{ $o['code'] }}' ? 'rotate-180' : ''" />
                    </button>
                    <div class="kaca-spring grid" :class="openOrder === '{{ $o['code'] }}' ? 'grid-rows-[1fr] opacity-100' : 'grid-rows-[0fr] opacity-0'" :inert="openOrder !== '{{ $o['code'] }}'">
                        <div class="overflow-hidden"><div class="px-1 pt-1 pb-3">
                            @php($steps = $o['history'])
                            @if ($steps === [])
                                <p class="text-[12px] font-semibold text-meta">Riwayat status order ini belum tercatat.</p>
                            @else
                                @php($total = max(1, array_sum(array_map(fn ($st) => $st['hours'] ?? 0, $steps))))
                                <p class="mb-2 text-[12px] font-semibold text-meta">Riwayat status · tercatat {{ Format::number($total) }} jam sebelum tahap saat ini</p>
                                <ul class="space-y-1.5">
                                    @php($acc = 0)
                                    @foreach ($steps as $st)
                                        @php($hrs = $st['hours'] ?? 0)
                                        <li class="grid grid-cols-[132px_1fr] items-center gap-3 text-[13px] sm:grid-cols-[170px_1fr]" style="--i: {{ $loop->index }}">
                                            <span class="truncate {{ $st['current'] ? 'font-extrabold' : 'text-meta' }}">{{ $st['label'] }}</span>
                                            <span class="relative h-6 rounded-[6px] bg-track">
                                                @if ($st['current'])
                                                    <span class="kaca-bar hatch-bg absolute inset-y-0 flex items-center justify-end rounded-[6px] px-1.5 text-[11px] font-bold" style="left: {{ ($acc / $total) * 100 }}%; right: 0; min-width: 34px; background-color: var(--accent); color: var(--accent-ink)">sekarang</span>
                                                @else
                                                    <span class="kaca-bar absolute inset-y-0 flex items-center justify-end rounded-[6px] px-1.5 text-[11px] font-bold" style="left: {{ ($acc / $total) * 100 }}%; width: {{ max(3.5, ($hrs / $total) * 100) }}%; min-width: 34px; background: var(--c-done); color: var(--primary-ink)">{{ $hrs }} j</span>
                                                @endif
                                            </span>
                                        </li>
                                        @php($acc += $hrs)
                                    @endforeach
                                </ul>
                            @endif
                        </div></div>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif
</div>

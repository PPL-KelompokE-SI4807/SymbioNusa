{{-- FND-04 Dashboard Admin. Komposisi mengikuti docs/design/prototype/dashboard-admin-v2.html. --}}
@use('App\Support\Format')
@php
    $maxFlow = max(1, ...array_column($flow, 'count'));
    $urgent = array_values(array_filter($queue, fn ($item) => $item['mendesak']));
@endphp

<div class="space-y-6">
    {{-- KPI strip --}}
    <section aria-label="Ringkasan dampak" class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-ui.kpi label="Tonase terdaur ulang" :value="Format::ton($impact['tonase'], 1)"
                  :caption="'Dari '.Format::number($impact['orders']).' order selesai'" />
        <x-ui.kpi label="Penghematan biaya" :value="Format::rupiahShort($impact['hemat_seller'] + $impact['hemat_buyer'])"
                  :exact="Format::rupiah($impact['hemat_seller'] + $impact['hemat_buyer'])"
                  :caption="'Seller '.Format::rupiahShort($impact['hemat_seller']).' · Buyer '.Format::rupiahShort($impact['hemat_buyer'])" />
        <x-ui.kpi label="Emisi ditekan" :value="Format::number($impact['co2e_kg'] / 1000, 1).' t CO₂e'"
                  :exact="Format::number($impact['co2e_kg'], 2).' kg CO₂e'" caption="Bersih, setelah emisi angkutan" />
        <x-ui.kpi label="Inspector aktif" :value="Format::number($inspectors['aktif']).' / '.Format::number($inspectors['total'])"
                  :caption="Format::number($inspectors['segera_berakhir']).' sertifikat berakhir ≤ 30 hari'" />
    </section>

    {{-- Alur Order --}}
    <x-ui.card title="Alur Order">
        <p class="-mt-2 mb-4 text-body-sm text-on-surface-muted">Posisi order saat ini di tiap tahap.</p>

        <ol class="grid grid-cols-2 gap-3 sm:grid-cols-4 xl:grid-cols-8">
            @foreach ($flow as $stage)
                @php $isStuck = $stuck && $stage['label'] === $stuck['label']; @endphp
                <li @class([
                    'flex flex-col justify-between rounded-sm border p-3',
                    'border-warning bg-warning-container' => $isStuck,
                    'border-outline bg-surface' => ! $isStuck,
                ])>
                    <span @class(['text-label-sm', 'text-on-warning-container' => $isStuck, 'text-on-surface-muted' => ! $isStuck])>{{ $stage['label'] }}</span>
                    <span class="mt-2 text-headline-md tabular-nums">{{ Format::number($stage['count']) }}</span>
                    <span class="mt-3 block h-1.5 rounded-full bg-neutral-container" aria-hidden="true">
                        <span @class(['block h-full rounded-full', 'bg-warning' => $isStuck, 'bg-primary' => ! $isStuck])
                              style="width: {{ round($stage['count'] / $maxFlow * 100) }}%"></span>
                    </span>
                </li>
            @endforeach
        </ol>

        <div class="mt-4 flex flex-wrap items-center gap-2 text-body-sm text-on-surface-muted">
            <span>Cabang akhir:</span>
            @foreach ($branches as $branch)
                <x-ui.badge :variant="$branch['count'] > 0 ? $branch['variant'] : 'neutral'">{{ $branch['label'] }} {{ Format::number($branch['count']) }}</x-ui.badge>
            @endforeach
        </div>

        @if ($stuck)
            <x-ui.alert variant="warning" class="mt-4">
                Order paling banyak tertahan di tahap <strong>{{ $stuck['label'] }}</strong> ({{ Format::number($stuck['count']) }} order).
                Periksa antrean tindakan di bawah.
            </x-ui.alert>
        @endif
    </x-ui.card>

    <div class="grid grid-cols-1 items-start gap-6 xl:grid-cols-3">
        {{-- Antrean tindakan --}}
        <x-ui.card title="Antrean tindakan" class="xl:col-span-2" :padding="false" x-data="{ tab: 'semua' }">
            <x-slot:actions>
                <div class="flex rounded-sm border border-outline-input p-0.5" role="tablist" aria-label="Filter antrean">
                    @foreach (['semua' => 'Semua ('.count($queue).')', 'mendesak' => 'Mendesak ('.count($urgent).')'] as $key => $label)
                        <button type="button" role="tab" @click="tab = '{{ $key }}'" :aria-selected="(tab === '{{ $key }}').toString()"
                                :class="tab === '{{ $key }}' ? 'bg-primary-container text-on-primary-container' : 'text-on-surface-muted hover:text-on-surface'"
                                class="h-8 rounded-sm px-3 text-label-sm focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus-ring">
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
            </x-slot:actions>

            @if ($queue === [])
                <p class="px-6 pb-6 text-body-md text-on-surface-muted">Tidak ada tindakan yang menunggu. Semua antrean sudah ditangani.</p>
            @else
                <x-ui.table label="Antrean tindakan Admin">
                    <x-slot:head>
                        <th scope="col" class="px-6">Tindakan</th>
                        <th scope="col" class="hidden px-4 sm:table-cell">Jenis</th>
                        <th scope="col" class="hidden px-6 text-right sm:table-cell">Prioritas</th>
                    </x-slot:head>
                    @foreach ($queue as $item)
                        <tr class="border-t border-outline" @if (! $item['mendesak']) x-show="tab === 'semua'" @endif>
                            <td class="px-6 py-3">
                                <p class="text-label-md text-on-surface">{{ $item['tindakan'] }}</p>
                                <p class="mt-0.5 text-body-sm text-on-surface-muted">{{ $item['rincian'] }}</p>
                                <div class="mt-1.5 flex flex-wrap items-center gap-2 sm:hidden">
                                    <x-ui.badge :variant="$item['mendesak'] ? 'danger' : 'warning'">{{ $item['mendesak'] ? 'Mendesak' : 'Normal' }}</x-ui.badge>
                                    <span class="text-body-sm text-on-surface-muted">{{ $item['jenis'] }}</span>
                                </div>
                            </td>
                            <td class="hidden whitespace-nowrap px-4 py-3 text-on-surface-muted sm:table-cell">{{ $item['jenis'] }}</td>
                            <td class="hidden px-6 py-3 text-right sm:table-cell">
                                <x-ui.badge :variant="$item['mendesak'] ? 'danger' : 'warning'">{{ $item['mendesak'] ? 'Mendesak' : 'Normal' }}</x-ui.badge>
                            </td>
                        </tr>
                    @endforeach
                </x-ui.table>
                @if ($urgent === [])
                    <p x-show="tab === 'mendesak'" x-cloak class="px-6 py-4 text-body-md text-on-surface-muted">Tidak ada tindakan mendesak.</p>
                @endif
            @endif
        </x-ui.card>

        {{-- Ringkasan modul (kartu ringkasan per modul, FND-04) --}}
        <x-ui.card title="Modul">
            <p class="-mt-2 mb-3 text-body-sm text-on-surface-muted">Status operasional per modul.</p>
            <dl class="divide-y divide-outline">
                @foreach ($modules as $module)
                    <div class="flex items-center justify-between gap-3 py-2.5">
                        <dt class="min-w-0">
                            <span class="block text-body-md text-on-surface">{{ $module['label'] }}</span>
                            <span class="block text-body-sm text-on-surface-muted">{{ $module['module'] }}</span>
                        </dt>
                        <dd class="text-headline-md tabular-nums">{{ Format::number($module['count']) }}</dd>
                    </div>
                @endforeach
            </dl>
        </x-ui.card>
    </div>
</div>

{{-- FND-04 Dashboard Inspector. --}}
@use('App\Support\Format')
@use('App\Support\StatusMap')

<div class="space-y-6">
    <section aria-label="Ringkasan Inspector" class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($kpis as $kpi)
            <x-ui.kpi :label="$kpi['label']" :value="$kpi['value']" />
        @endforeach
    </section>

    <x-ui.card title="Tugas inspeksi aktif" :padding="false">
        @if ($active->isEmpty())
            <p class="px-6 pb-6 text-body-md text-on-surface-muted">Tidak ada tugas aktif. Tugas baru muncul di sini setelah Admin menugaskan Anda.</p>
        @else
            <x-ui.table label="Tugas inspeksi aktif">
                <x-slot:head>
                    <th scope="col" class="px-6">Inspeksi</th>
                    <th scope="col" class="px-4">Seller → Buyer</th>
                    <th scope="col" class="hidden px-4 sm:table-cell">Mode</th>
                    <th scope="col" class="hidden px-4 sm:table-cell">Jadwal</th>
                    <th scope="col" class="hidden px-6 sm:table-cell">Status</th>
                </x-slot:head>
                @foreach ($active as $inspection)
                    <tr class="border-t border-outline">
                        <td class="whitespace-nowrap px-6 py-3">
                            <span class="block text-label-md">#{{ $inspection->id_inspection }}</span>
                            <span class="block text-body-sm text-on-surface-muted">PO-{{ $inspection->purchase_orders_id_purchase_order }}<span class="sm:hidden"> · {{ Format::date($inspection->jadwal) }}</span></span><span class="mt-1 block sm:hidden"><x-ui.badge :variant="StatusMap::badge('inspections.status', $inspection->status)">{{ StatusMap::label('inspections.status', $inspection->status) }}</x-ui.badge></span>
                        </td>
                        <td class="px-4 py-3">{{ $inspection->purchaseOrder?->seller?->nama ?? '–' }} → {{ $inspection->purchaseOrder?->buyer?->nama ?? '–' }}</td>
                        <td class="hidden px-4 py-3 sm:table-cell">{{ StatusMap::label('inspections.mode_verifikasi', $inspection->mode_verifikasi) }}</td>
                        <td class="hidden whitespace-nowrap px-4 py-3 sm:table-cell">{{ Format::date($inspection->jadwal) }}</td>
                        <td class="hidden px-6 py-3 sm:table-cell"><x-ui.badge :variant="StatusMap::badge('inspections.status', $inspection->status)">{{ StatusMap::label('inspections.status', $inspection->status) }}</x-ui.badge></td>
                    </tr>
                @endforeach
            </x-ui.table>
        @endif
    </x-ui.card>

    <x-ui.card title="Selesai terbaru" :padding="false">
        @if ($done->isEmpty())
            <p class="px-6 pb-6 text-body-md text-on-surface-muted">Belum ada inspeksi yang selesai.</p>
        @else
            <x-ui.table label="Inspeksi selesai terbaru">
                <x-slot:head>
                    <th scope="col" class="px-6">Inspeksi</th>
                    <th scope="col" class="px-4">PO</th>
                    <th scope="col" class="hidden px-4 sm:table-cell">Mode</th>
                    <th scope="col" class="hidden px-6 sm:table-cell">Status</th>
                </x-slot:head>
                @foreach ($done as $inspection)
                    <tr class="border-t border-outline">
                        <td class="px-6 py-3 text-label-md">#{{ $inspection->id_inspection }}<span class="mt-1 block sm:hidden"><x-ui.badge :variant="StatusMap::badge('inspections.status', $inspection->status)">{{ StatusMap::label('inspections.status', $inspection->status) }}</x-ui.badge></span></td>
                        <td class="px-4 py-3">PO-{{ $inspection->purchase_orders_id_purchase_order }}</td>
                        <td class="hidden px-4 py-3 sm:table-cell">{{ StatusMap::label('inspections.mode_verifikasi', $inspection->mode_verifikasi) }}</td>
                        <td class="hidden px-6 py-3 sm:table-cell"><x-ui.badge :variant="StatusMap::badge('inspections.status', $inspection->status)">{{ StatusMap::label('inspections.status', $inspection->status) }}</x-ui.badge></td>
                    </tr>
                @endforeach
            </x-ui.table>
        @endif
    </x-ui.card>
</div>

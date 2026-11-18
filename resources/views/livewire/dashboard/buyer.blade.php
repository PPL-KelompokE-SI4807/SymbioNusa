{{-- FND-04 Dashboard Buyer. --}}
@use('App\Support\Format')
@use('App\Support\StatusMap')

<div class="space-y-6">
    <section aria-label="Ringkasan Buyer" class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($kpis as $kpi)
            <x-ui.kpi :label="$kpi['label']" :value="$kpi['value']" :exact="$kpi['exact'] ?? null" :caption="$kpi['caption'] ?? null" />
        @endforeach
    </section>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        <x-ui.card title="Order pembelian terbaru" :padding="false">
            @if ($orders->isEmpty())
                <p class="px-6 pb-6 text-body-md text-on-surface-muted">Belum ada order. Ajukan RFQ ke Seller, lalu buat PO setelah harga disepakati.</p>
            @else
                <x-ui.table label="Order pembelian terbaru">
                    <x-slot:head>
                        <th scope="col" class="px-6">PO</th>
                        <th scope="col" class="px-4">Seller</th>
                        <th scope="col" class="px-4 text-right">Total</th>
                        <th scope="col" class="hidden px-6 sm:table-cell">Status</th>
                    </x-slot:head>
                    @foreach ($orders as $order)
                        <tr class="border-t border-outline">
                            <td class="whitespace-nowrap px-6 py-3 text-label-md">PO-{{ $order->id_purchase_order }}<span class="mt-1 block sm:hidden"><x-ui.badge :variant="StatusMap::badge('purchase_orders.status', $order->status)">{{ StatusMap::label('purchase_orders.status', $order->status) }}</x-ui.badge></span></td>
                            <td class="px-4 py-3">{{ $order->seller->nama ?? '–' }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-right tabular-nums">{{ Format::rupiah($order->volume_kg * $order->harga_per_kg) }}</td>
                            <td class="hidden px-6 py-3 sm:table-cell"><x-ui.badge :variant="StatusMap::badge('purchase_orders.status', $order->status)">{{ StatusMap::label('purchase_orders.status', $order->status) }}</x-ui.badge></td>
                        </tr>
                    @endforeach
                </x-ui.table>
            @endif
        </x-ui.card>

        <x-ui.card title="Kebutuhan saya" :padding="false">
            @if ($demands->isEmpty())
                <p class="px-6 pb-6 text-body-md text-on-surface-muted">Belum ada kebutuhan. Publikasikan kebutuhan agar Seller bisa menawarkan materialnya.</p>
            @else
                <x-ui.table label="Kebutuhan saya">
                    <x-slot:head>
                        <th scope="col" class="px-6">Kategori</th>
                        <th scope="col" class="px-4 text-right">Volume</th>
                        <th scope="col" class="hidden px-4 sm:table-cell">Berlaku sampai</th>
                        <th scope="col" class="hidden px-6 sm:table-cell">Status</th>
                    </x-slot:head>
                    @foreach ($demands as $demand)
                        <tr class="border-t border-outline">
                            <td class="px-6 py-3 text-label-md">{{ $demand->category->nama ?? '–' }}<span class="mt-1 block sm:hidden"><x-ui.badge :variant="StatusMap::badge('demand_requests.status', $demand->status)">{{ StatusMap::label('demand_requests.status', $demand->status) }}</x-ui.badge></span></td>
                            <td class="whitespace-nowrap px-4 py-3 text-right tabular-nums">{{ Format::kg($demand->volume_kg) }}</td>
                            <td class="hidden whitespace-nowrap px-4 py-3 sm:table-cell">{{ Format::date($demand->berlaku_sampai) }}</td>
                            <td class="hidden px-6 py-3 sm:table-cell"><x-ui.badge :variant="StatusMap::badge('demand_requests.status', $demand->status)">{{ StatusMap::label('demand_requests.status', $demand->status) }}</x-ui.badge></td>
                        </tr>
                    @endforeach
                </x-ui.table>
            @endif
        </x-ui.card>
    </div>
</div>

{{-- FND-04 Dashboard Seller. --}}
@use('App\Support\Format')
@use('App\Support\StatusMap')

<div class="space-y-6">
    <section aria-label="Ringkasan Seller" class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($kpis as $kpi)
            <x-ui.kpi :label="$kpi['label']" :value="$kpi['value']" :exact="$kpi['exact'] ?? null" :caption="$kpi['caption'] ?? null" />
        @endforeach
    </section>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        <x-ui.card title="Order masuk terbaru" :padding="false">
            @if ($orders->isEmpty())
                <p class="px-6 pb-6 text-body-md text-on-surface-muted">Belum ada order masuk. Order muncul di sini setelah Buyer membuat PO dari RFQ yang disepakati.</p>
            @else
                <x-ui.table label="Order masuk terbaru">
                    <x-slot:head>
                        <th scope="col" class="px-6">PO</th>
                        <th scope="col" class="px-4">Buyer</th>
                        <th scope="col" class="px-4 text-right">Total</th>
                        <th scope="col" class="hidden px-6 sm:table-cell">Status</th>
                    </x-slot:head>
                    @foreach ($orders as $order)
                        <tr class="border-t border-outline">
                            <td class="whitespace-nowrap px-6 py-3 text-label-md">PO-{{ $order->id_purchase_order }}<span class="mt-1 block sm:hidden"><x-ui.badge :variant="StatusMap::badge('purchase_orders.status', $order->status)">{{ StatusMap::label('purchase_orders.status', $order->status) }}</x-ui.badge></span></td>
                            <td class="px-4 py-3">{{ $order->buyer->nama ?? '–' }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-right tabular-nums">{{ Format::rupiah($order->volume_kg * $order->harga_per_kg) }}</td>
                            <td class="hidden px-6 py-3 sm:table-cell"><x-ui.badge :variant="StatusMap::badge('purchase_orders.status', $order->status)">{{ StatusMap::label('purchase_orders.status', $order->status) }}</x-ui.badge></td>
                        </tr>
                    @endforeach
                </x-ui.table>
            @endif
        </x-ui.card>

        <x-ui.card title="Listing terbaru" :padding="false">
            @if ($listings->isEmpty())
                <p class="px-6 pb-6 text-body-md text-on-surface-muted">Belum ada listing. Buat listing agar material Anda tampil di katalog.</p>
            @else
                <x-ui.table label="Listing terbaru">
                    <x-slot:head>
                        <th scope="col" class="px-6">Listing</th>
                        <th scope="col" class="px-4 text-right">Stok</th>
                        <th scope="col" class="hidden px-4 text-right sm:table-cell">Harga/kg</th>
                        <th scope="col" class="hidden px-6 sm:table-cell">Status</th>
                    </x-slot:head>
                    @foreach ($listings as $listing)
                        <tr class="border-t border-outline">
                            <td class="px-6 py-3 text-label-md">{{ $listing->judul }}<span class="mt-1 block sm:hidden"><x-ui.badge :variant="StatusMap::badge('listings.status', $listing->status)">{{ StatusMap::label('listings.status', $listing->status) }}</x-ui.badge></span></td>
                            <td class="whitespace-nowrap px-4 py-3 text-right tabular-nums">{{ Format::kg($listing->stok_kg) }}</td>
                            <td class="hidden whitespace-nowrap px-4 py-3 text-right tabular-nums sm:table-cell">{{ Format::rupiah($listing->harga_per_kg) }}</td>
                            <td class="hidden px-6 py-3 sm:table-cell"><x-ui.badge :variant="StatusMap::badge('listings.status', $listing->status)">{{ StatusMap::label('listings.status', $listing->status) }}</x-ui.badge></td>
                        </tr>
                    @endforeach
                </x-ui.table>
            @endif
        </x-ui.card>
    </div>
</div>

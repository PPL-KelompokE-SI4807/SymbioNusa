{{--
    Tabel order ringkas di dasbor (DESIGN.md › Data table, amandemen FND v8). Volume di bawah nomor PO agar muat di kolom sempit.
    orders: Collection<PurchaseOrder> dengan relasi pihak (buyer/seller) sudah dimuat.
    party: 'buyer' | 'seller' — kolom pihak yang ditampilkan.
--}}
@props(['title', 'orders', 'party' => 'buyer', 'empty' => 'Belum ada order.'])

<x-ui.card :title="$title" :padding="false">
    @if ($orders->isEmpty())
        <p class="px-6 pb-6 text-body-md text-on-surface-muted">{{ $empty }}</p>
    @else
        {{-- Mobile: kartu baris (DESIGN.md › Layout › Responsive). --}}
        <ul class="px-3 pb-3 sm:hidden">
            @foreach ($orders as $order)
                @php($company = $party === 'buyer' ? $order->buyer : $order->seller)
                <li class="flex items-start gap-3 border-t border-outline px-3 py-3 first:border-t-0">
                    <x-ui.monogram :name="$company->nama ?? '–'" size="sm" />
                    <div class="min-w-0 flex-1">
                        <p class="flex justify-between gap-3">
                            <span class="font-semibold">PO-{{ $order->id_purchase_order }}</span>
                            <span class="font-semibold tabular-nums">{{ \App\Support\Format::rupiah((float) $order->volume_kg * (float) $order->harga_per_kg) }}</span>
                        </p>
                        <p class="text-body-sm text-on-surface-subtle">{{ $company->nama ?? '–' }} · {{ \App\Support\Format::kg($order->volume_kg) }}</p>
                        <x-ui.badge class="mt-1.5" :variant="\App\Support\StatusMap::badge('purchase_orders.status', $order->status)">
                            {{ \App\Support\StatusMap::label('purchase_orders.status', $order->status) }}
                        </x-ui.badge>
                    </div>
                </li>
            @endforeach
        </ul>

        <x-ui.table :label="$title" class="hidden px-3 pb-3 sm:block">
            <x-slot:head>
                <th class="px-3">Order</th>
                <th class="px-3">{{ $party === 'buyer' ? 'Buyer' : 'Seller' }}</th>
                <th class="px-3 text-right">Total</th>
                <th class="px-3">Status</th>
            </x-slot:head>
            @foreach ($orders as $order)
                @php($company = $party === 'buyer' ? $order->buyer : $order->seller)
                <tr class="border-t border-outline">
                    <td class="px-3 py-3">
                        <p class="font-semibold">PO-{{ $order->id_purchase_order }}</p>
                        <p class="text-body-sm text-on-surface-subtle">{{ \App\Support\Format::kg($order->volume_kg) }}</p>
                    </td>
                    <td class="px-3 py-3">
                        <span class="flex items-center gap-2.5">
                            <x-ui.monogram :name="$company->nama ?? '–'" size="sm" />
                            <span class="min-w-0">{{ $company->nama ?? '–' }}</span>
                        </span>
                    </td>
                    <td class="px-3 py-3 text-right font-semibold tabular-nums whitespace-nowrap">
                        {{ \App\Support\Format::rupiah((float) $order->volume_kg * (float) $order->harga_per_kg) }}
                    </td>
                    <td class="px-3 py-3">
                        <x-ui.badge :variant="\App\Support\StatusMap::badge('purchase_orders.status', $order->status)">
                            {{ \App\Support\StatusMap::label('purchase_orders.status', $order->status) }}
                        </x-ui.badge>
                    </td>
                </tr>
            @endforeach
        </x-ui.table>
    @endif
</x-ui.card>

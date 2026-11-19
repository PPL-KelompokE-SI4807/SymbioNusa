{{--
    Dasbor Buyer (FND-04, DESIGN.md amandemen FND v8): 4 tile bermakna → Perlu tindakan + Order pembelian (kiri)
    dan Kebutuhan saya + Pengiriman berjalan (kanan).
--}}
@use('App\Support\Format')
@use('App\Support\MaterialPhoto')

<div class="space-y-6">
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($tiles as $tile)
            <x-ui.kpi :label="$tile['label']" :value="$tile['value']" :unit="$tile['unit']" :caption="$tile['caption']"
                      :exact="$tile['exact'] ?? null" :tone="$tile['tone']" />
        @endforeach
    </div>

    <div class="grid items-start gap-6 xl:grid-cols-[minmax(0,1fr)_380px]">
        <div class="min-w-0 space-y-6">
            <x-dash.todo-list title="Perlu tindakan" :items="$todo" />
            <x-dash.order-table title="Order pembelian" :orders="$orders" party="seller" empty="Belum ada order pembelian." />
        </div>

        <div class="space-y-6">
            <x-ui.card title="Kebutuhan saya">
                @if ($demand)
                    @php($photo = MaterialPhoto::forCategory($demand->category->nama ?? null))
                    @if ($photo)
                        <img src="{{ $photo }}" alt="" class="h-36 w-full rounded-md object-cover">
                    @endif
                    <div class="mt-4 flex items-start justify-between gap-3">
                        <p class="min-w-0 text-body-lg font-semibold">{{ $demand->category->nama ?? 'Kebutuhan' }}</p>
                        <x-ui.badge :variant="\App\Support\StatusMap::badge('demand_requests.status', $demand->status)">
                            {{ \App\Support\StatusMap::label('demand_requests.status', $demand->status) }}
                        </x-ui.badge>
                    </div>
                    <dl class="mt-4 grid grid-cols-2 gap-2.5">
                        <div class="rounded-md bg-surface-muted px-3.5 py-3">
                            <dt class="text-body-sm text-on-surface-subtle">Volume</dt>
                            <dd class="text-label-md font-semibold tabular-nums">{{ Format::kg($demand->volume_kg) }}</dd>
                        </div>
                        <div class="rounded-md bg-surface-muted px-3.5 py-3">
                            <dt class="text-body-sm text-on-surface-subtle">Berlaku sampai</dt>
                            <dd class="text-label-md font-semibold">{{ Format::date($demand->berlaku_sampai) }}</dd>
                        </div>
                    </dl>
                    @if (Route::has('buyer.demands.index'))
                        <x-ui.button variant="secondary" :href="route('buyer.demands.index')" class="mt-4 w-full">Semua kebutuhan</x-ui.button>
                    @endif
                @else
                    <p class="text-body-md text-on-surface-muted">Belum ada kebutuhan terbuka. Ajukan kebutuhan agar Seller bisa menawarkan materialnya.</p>
                @endif
            </x-ui.card>

            <x-ui.card title="Pengiriman berjalan" :padding="false">
                @if ($shipments->isEmpty())
                    <p class="px-6 pb-6 text-body-md text-on-surface-muted">Tidak ada order yang sedang dikirim.</p>
                @else
                    <ul class="px-3 pb-3">
                        @foreach ($shipments as $order)
                            <li class="flex items-center gap-3 rounded-md p-2.5">
                                <x-ui.monogram :name="$order->seller->nama ?? 'PO'" />
                                <div class="min-w-0 flex-1">
                                    <p class="text-label-md font-semibold">PO-{{ $order->id_purchase_order }}</p>
                                    <p class="text-body-sm text-on-surface-subtle">{{ $order->seller->nama ?? '–' }} · {{ Format::kg($order->volume_kg) }}</p>
                                </div>
                                <x-ui.badge variant="info">Dalam Pengiriman</x-ui.badge>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-ui.card>
        </div>
    </div>
</div>

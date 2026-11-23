{{--
    Dasbor Seller (FND-04, DESIGN.md amandemen FND v8): 4 tile bermakna → Perlu tindakan + Order masuk (kiri)
    dan Listing saya + Kebutuhan Buyer terbaru (kanan).
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
            <x-dash.order-table title="Order masuk" :orders="$orders" party="buyer" empty="Belum ada order dari Buyer." />
        </div>

        <div class="space-y-6">
            <x-ui.card title="Listing saya">
                @if ($listing)
                    @php($photo = MaterialPhoto::forCategory($listing->category->nama ?? null))
                    @if ($photo)
                        <img src="{{ $photo }}" alt="" class="h-36 w-full rounded-md object-cover">
                    @endif
                    <div class="mt-4 flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-body-lg font-semibold">{{ $listing->judul }}</p>
                            <p class="text-body-sm text-on-surface-subtle">{{ $listing->category->nama ?? '–' }}</p>
                        </div>
                        <x-ui.badge :variant="\App\Support\StatusMap::badge('listings.status', $listing->status)">
                            {{ \App\Support\StatusMap::label('listings.status', $listing->status) }}
                        </x-ui.badge>
                    </div>
                    <dl class="mt-4 grid grid-cols-2 gap-2.5">
                        <div class="rounded-md bg-surface-muted px-3.5 py-3">
                            <dt class="text-body-sm text-on-surface-subtle">Stok</dt>
                            <dd class="text-label-md font-semibold tabular-nums">{{ Format::kg($listing->stok_kg) }}</dd>
                        </div>
                        <div class="rounded-md bg-surface-muted px-3.5 py-3">
                            <dt class="text-body-sm text-on-surface-subtle">Harga</dt>
                            <dd class="text-label-md font-semibold tabular-nums">{{ Format::rupiah($listing->harga_per_kg) }}/kg</dd>
                        </div>
                    </dl>
                @else
                    <p class="text-body-md text-on-surface-muted">Belum ada listing yang dipublikasikan.</p>
                @endif
            </x-ui.card>

            <x-ui.card title="Kebutuhan Buyer terbaru" :padding="false">
                @if (Route::has('seller.demands.index'))
                    <x-slot:actions>
                        <a href="{{ route('seller.demands.index') }}" class="inline-flex items-center gap-1 rounded-sm text-label-md font-semibold text-primary hover:underline underline-offset-4 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus-ring">
                            Semua <x-ui.icon name="arrow-right" class="size-4" />
                        </a>
                    </x-slot:actions>
                @endif
                @if ($demands->isEmpty())
                    <p class="px-6 pb-6 text-body-md text-on-surface-muted">Belum ada kebutuhan Buyer yang terbuka.</p>
                @else
                    <ul class="px-3 pb-3">
                        @foreach ($demands as $demand)
                            @php($photo = MaterialPhoto::forCategory($demand->category->nama ?? null))
                            <li class="flex items-center gap-3 rounded-md p-2.5">
                                @if ($photo)
                                    <img src="{{ $photo }}" alt="" class="size-12 shrink-0 rounded-sm object-cover">
                                @else
                                    <x-ui.monogram :name="$demand->category->nama ?? 'Kebutuhan'" />
                                @endif
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-label-md font-semibold">{{ $demand->category->nama ?? 'Kebutuhan' }}</p>
                                    <p class="text-body-sm text-on-surface-subtle">{{ $demand->company->nama ?? '–' }} · {{ Format::kg($demand->volume_kg) }}</p>
                                </div>
                                <p class="shrink-0 text-right text-body-sm text-on-surface-muted">
                                    @if ($demand->berlaku_sampai)
                                        s.d. {{ Format::dayMonth($demand->berlaku_sampai) }}
                                    @endif
                                </p>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-ui.card>
        </div>
    </div>
</div>

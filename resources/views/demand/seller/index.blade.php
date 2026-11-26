@extends('layouts.app')

@section('title', 'Papan Kebutuhan Buyer')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="hover:underline">Dashboard</a>
    <span class="mx-2 text-on-surface-muted" aria-hidden="true">/</span>
    <span class="text-on-surface" aria-current="page">Papan Kebutuhan</span>
@endsection

@section('content')
    <x-ui.page-header
        title="Papan Kebutuhan Buyer"
        subtitle="Jelajahi permintaan bahan baku sekunder dari industri Buyer dan tawarkan material sisa produksi Anda."
    />


    {{-- Filter Bar --}}
    <x-ui.card class="mb-6">
        <form method="GET" action="{{ route('seller.demands.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
            <div>
                <label for="filter-category" class="mb-2 block text-label-md font-semibold text-on-surface">Kategori Material</label>
                <x-ui.select id="filter-category" name="category">
                    <option value="">Semua Kategori</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id_categories }}" {{ request('category') == $cat->id_categories ? 'selected' : '' }}>
                            {{ $cat->nama }}
                        </option>
                    @endforeach
                </x-ui.select>
            </div>
            <div>
                <label for="filter-wilayah" class="mb-2 block text-label-md font-semibold text-on-surface">Wilayah / Nama Buyer</label>
                <x-ui.input
                    id="filter-wilayah"
                    name="wilayah"
                    placeholder="Contoh: Bandung / PT Jaya"
                    :value="request('wilayah')"
                />
            </div>
            <div>
                <label for="filter-vol-min" class="mb-2 block text-label-md font-semibold text-on-surface">Volume Min (kg)</label>
                <x-ui.input
                    id="filter-vol-min"
                    name="vol_min"
                    type="number"
                    placeholder="0"
                    :value="request('vol_min')"
                />
            </div>
            <div>
                <label for="filter-vol-max" class="mb-2 block text-label-md font-semibold text-on-surface">Volume Max (kg)</label>
                <x-ui.input
                    id="filter-vol-max"
                    name="vol_max"
                    type="number"
                    placeholder="Contoh: 10000"
                    :value="request('vol_max')"
                />
            </div>
            <div class="flex gap-2">
                <x-ui.button type="submit" variant="secondary" size="md" class="w-full">
                    Saring
                </x-ui.button>
                @if (request()->hasAny(['category', 'wilayah', 'vol_min', 'vol_max']))
                    <x-ui.button href="{{ route('seller.demands.index') }}" variant="text" size="md">
                        Reset
                    </x-ui.button>
                @endif
            </div>
        </form>
    </x-ui.card>

    {{-- Daftar Kebutuhan --}}
    @if ($demands->isEmpty())
        <x-ui.card class="text-center py-12">
            <p class="text-dash-sub text-on-surface">Tidak ada kebutuhan yang cocok</p>
            <p class="mt-1 text-body-sm text-on-surface-muted">Ubah saringan kategori, wilayah, atau volume untuk melihat kebutuhan lain.</p>
        </x-ui.card>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($demands as $demand)
                @php
                    $freqLabel = $demand->frekuensi instanceof \App\Enums\DemandRequestFrequency ? $demand->frekuensi->label() : \App\Support\StatusMap::label('demand_requests.frekuensi', $demand->frekuensi);
                @endphp
                <x-ui.card class="flex flex-col justify-between transition-shadow hover:shadow-raised">
                    <div>
                        {{-- Header Card: Kategori & Status --}}
                        <div class="flex items-start justify-between gap-2 mb-3">
                            <div>
                                <span class="text-label-sm font-bold text-on-surface-muted">
                                    {{ $demand->category?->nama ?? 'Bahan Baku' }}
                                </span>
                            </div>
                            <span class="text-xs text-on-surface-muted">
                                {{ $freqLabel }}
                            </span>
                        </div>

                        {{-- Nama Perusahaan Buyer --}}
                        <h2 class="mb-1 text-dash-row text-on-surface">
                            {{ $demand->company?->nama ?? 'Perusahaan Mitra' }}
                        </h2>
                        <p class="mb-4 flex items-center gap-1.5 text-body-sm text-on-surface-muted">
                            <x-ui.icon name="map-pin" class="size-4 shrink-0" /> {{ $demand->company?->alamat ?? 'Lokasi industri terdaftar' }}
                        </p>

                        {{-- Spesifikasi Ringkas --}}
                        @if ($demand->spesifikasi_min && is_array($demand->spesifikasi_min))
                            <div class="mb-4 space-y-1 border-t border-outline-hairline pt-3 text-body-sm">
                                <span class="mb-1 block text-label-sm font-bold text-on-surface-muted">
                                    Spesifikasi dicari
                                </span>
                                @foreach ($demand->spesifikasi_min as $k => $v)
                                    <div class="flex justify-between text-on-surface">
                                        <span class="text-on-surface-muted">{{ $k }}:</span>
                                        <span class="font-medium">{{ $v }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        {{-- Detail Angka --}}
                        <div class="mb-4 grid grid-cols-2 gap-2 border-t border-outline-hairline pt-3 text-body-sm">
                            <div>
                                <span class="text-on-surface-muted block">Volume:</span>
                                <span class="text-dash-row tabular-nums text-on-surface">
                                    {{ number_format((float) $demand->volume_kg, 2, ',', '.') }} kg
                                </span>
                            </div>
                            <div>
                                <span class="text-on-surface-muted block">Maks. Harga:</span>
                                <span class="text-dash-row tabular-nums text-on-surface">
                                    {{ $demand->harga_maks ? \App\Support\Format::rupiah($demand->harga_maks) : 'Terbuka' }}
                                </span>
                            </div>
                        </div>

                        @if ($demand->berlaku_sampai)
                            <p class="mb-4 text-body-sm text-on-surface-muted">
                                Berlaku s.d. <strong class="text-on-surface">{{ \App\Support\Format::date($demand->berlaku_sampai) }}</strong>
                            </p>
                        @endif
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center justify-between border-t border-outline-hairline pt-3 gap-2">
                        <x-ui.button href="{{ route('seller.demands.show', $demand) }}" variant="text" class="min-h-10">
                            Lihat Detail
                        </x-ui.button>
                        <x-ui.button href="{{ route('rfq.index') }}" variant="secondary">
                            Tawarkan Listing
                        </x-ui.button>
                    </div>
                </x-ui.card>
            @endforeach
        </div>

        @if ($demands->hasPages())
            <div class="mt-6">
                {{ $demands->links() }}
            </div>
        @endif
    @endif
@endsection

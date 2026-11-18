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

    @if (session('success'))
        <x-ui.alert variant="success" class="mb-6">
            {{ session('success') }}
        </x-ui.alert>
    @endif

    {{-- Filter Bar --}}
    <x-ui.card class="mb-6">
        <form method="GET" action="{{ route('seller.demands.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
            <div>
                <label for="filter-category" class="block text-label-sm text-on-surface mb-1">Kategori Material</label>
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
                <label for="filter-wilayah" class="block text-label-sm text-on-surface mb-1">Wilayah / Nama Buyer</label>
                <x-ui.input
                    id="filter-wilayah"
                    name="wilayah"
                    placeholder="Contoh: Bandung / PT Jaya"
                    :value="request('wilayah')"
                />
            </div>
            <div>
                <label for="filter-vol-min" class="block text-label-sm text-on-surface mb-1">Volume Min (kg)</label>
                <x-ui.input
                    id="filter-vol-min"
                    name="vol_min"
                    type="number"
                    placeholder="0"
                    :value="request('vol_min')"
                />
            </div>
            <div>
                <label for="filter-vol-max" class="block text-label-sm text-on-surface mb-1">Volume Max (kg)</label>
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
                    <x-ui.button href="{{ route('seller.demands.index') }}" variant="ghost" size="md">
                        Reset
                    </x-ui.button>
                @endif
            </div>
        </form>
    </x-ui.card>

    {{-- Daftar Kebutuhan --}}
    @if ($demands->isEmpty())
        <x-ui.card class="text-center py-12">
            <p class="text-on-surface-muted text-body-md">
                Tidak ada permintaan kebutuhan yang cocok dengan penyaringan saat ini.
            </p>
        </x-ui.card>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($demands as $demand)
                @php
                    $freqLabel = $demand->frekuensi instanceof \App\Enums\DemandRequestFrequency ? $demand->frekuensi->label() : \App\Support\StatusMap::label('demand_requests.frekuensi', $demand->frekuensi);
                @endphp
                <x-ui.card class="flex flex-col justify-between hover:border-primary/50 transition">
                    <div>
                        {{-- Header Card: Kategori & Status --}}
                        <div class="flex items-start justify-between gap-2 mb-3">
                            <div>
                                <span class="inline-block px-2 py-0.5 rounded text-xs font-medium bg-primary/10 text-primary">
                                    {{ $demand->category?->nama ?? 'Bahan Baku' }}
                                </span>
                            </div>
                            <span class="text-xs text-on-surface-muted">
                                {{ $freqLabel }}
                            </span>
                        </div>

                        {{-- Nama Perusahaan Buyer --}}
                        <h4 class="text-title-sm font-semibold text-on-surface mb-1">
                            {{ $demand->company?->nama ?? 'Perusahaan Mitra' }}
                        </h4>
                        <p class="text-xs text-on-surface-muted mb-4 flex items-center gap-1">
                            <span>📍</span> {{ $demand->company?->alamat ?? 'Lokasi industri terdaftar' }}
                        </p>

                        {{-- Spesifikasi Ringkas --}}
                        @if ($demand->spesifikasi_min && is_array($demand->spesifikasi_min))
                            <div class="bg-surface-alt/60 rounded-sm p-3 mb-4 space-y-1 text-xs">
                                <span class="font-semibold text-on-surface block text-[11px] uppercase tracking-wider text-on-surface-muted mb-1">
                                    Spesifikasi Dicari:
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
                        <div class="grid grid-cols-2 gap-2 text-xs border-t border-outline pt-3 mb-4">
                            <div>
                                <span class="text-on-surface-muted block">Volume:</span>
                                <span class="font-semibold text-on-surface text-sm tabular-nums">
                                    {{ number_format((float) $demand->volume_kg, 2, ',', '.') }} kg
                                </span>
                            </div>
                            <div>
                                <span class="text-on-surface-muted block">Maks. Harga:</span>
                                <span class="font-semibold text-on-surface text-sm tabular-nums">
                                    {{ $demand->harga_maks ? \App\Support\Format::rupiah($demand->harga_maks) : 'Terbuka' }}
                                </span>
                            </div>
                        </div>

                        @if ($demand->berlaku_sampai)
                            <p class="text-xs text-on-surface-muted mb-4">
                                Berlaku s.d. <strong class="text-on-surface">{{ \App\Support\Format::date($demand->berlaku_sampai) }}</strong>
                            </p>
                        @endif
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center justify-between border-t border-outline pt-3 gap-2">
                        <x-ui.button href="{{ route('seller.demands.show', $demand) }}" variant="secondary" size="sm" class="w-1/2">
                            Lihat Detail
                        </x-ui.button>
                        <x-ui.button href="{{ route('rfq.index') }}" variant="primary" size="sm" class="w-1/2">
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

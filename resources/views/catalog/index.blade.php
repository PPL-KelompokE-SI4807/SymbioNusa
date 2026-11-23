@extends('layouts.app')

@section('title', 'Katalog Material Sisa Produksi')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="hover:underline">Dashboard</a>
    <span class="mx-2 text-on-surface-muted" aria-hidden="true">/</span>
    <span class="text-on-surface" aria-current="page">Katalog Material</span>
@endsection

@section('content')
    <div class="mb-6">
        <x-ui.page-header
            title="Katalog Material Sisa Produksi"
            subtitle="Temukan pasokan bahan baku sekunder industri terverifikasi non-B3 untuk kebutuhan efisiensi dan sirkularitas produksi Anda."
        />
    </div>

    {{-- Filter Bar --}}
    <x-ui.card class="mb-6">
        <form method="GET" action="{{ route('catalog.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
            <div class="lg:col-span-2">
                <label for="filter-q" class="block text-label-sm text-on-surface mb-1">Cari Nama Material atau Lokasi</label>
                <x-ui.input
                    id="filter-q"
                    name="q"
                    placeholder="Contoh: Logam Aluminium, Bandung, Kain Perca..."
                    :value="request('q')"
                />
            </div>
            <div>
                <label for="filter-kategori" class="block text-label-sm text-on-surface mb-1">Kategori Material</label>
                <x-ui.select id="filter-kategori" name="kategori">
                    <option value="">Semua Kategori</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id_categories }}" {{ request('kategori') == $cat->id_categories ? 'selected' : '' }}>
                            {{ $cat->nama }}
                        </option>
                    @endforeach
                </x-ui.select>
            </div>
            <div class="flex gap-2">
                <x-ui.button type="submit" variant="secondary" class="flex-1">
                    Cari Material
                </x-ui.button>
                @if (request()->hasAny(['q', 'kategori']))
                    <x-ui.button href="{{ route('catalog.index') }}" variant="ghost">
                        Reset
                    </x-ui.button>
                @endif
            </div>
        </form>
    </x-ui.card>

    {{-- Grid Listing Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse ($listings as $listing)
            <x-ui.card class="flex flex-col justify-between hover:border-primary transition-colors">
                <div>
                    <div class="flex items-start justify-between gap-2 mb-2">
                        <span class="inline-block px-2 py-0.5 rounded text-xs bg-primary/10 text-primary font-medium">
                            {{ $listing->category?->nama ?? 'Material' }}
                        </span>
                        <span class="text-body-xs text-on-surface-muted">
                            {{ $listing->lokasi ?? 'Indonesia' }}
                        </span>
                    </div>

                    <h3 class="text-title-sm font-semibold text-on-surface mb-2">
                        <a href="{{ route('catalog.show', $listing) }}" class="hover:text-primary">
                            {{ $listing->judul }}
                        </a>
                    </h3>

                    <div class="text-body-xs text-on-surface-muted mb-4">
                        Penyedia: <span class="font-medium text-on-surface">{{ $listing->company?->nama ?? 'Mitra Terverifikasi' }}</span>
                    </div>

                    <div class="bg-surface-muted p-3 rounded-md mb-4 space-y-1">
                        <div class="flex justify-between text-body-sm">
                            <span class="text-on-surface-muted">Stok Tersedia:</span>
                            <span class="font-semibold text-on-surface">{{ \App\Support\Format::number($listing->stok_kg) }} kg</span>
                        </div>
                        <div class="flex justify-between text-body-sm">
                            <span class="text-on-surface-muted">Harga Penawaran:</span>
                            <span class="font-semibold text-primary">{{ \App\Support\Format::rupiah($listing->harga_per_kg) }} / kg</span>
                        </div>
                    </div>
                </div>

                <div class="border-t border-border pt-4 flex items-center justify-between">
                    <div class="flex items-center gap-1.5 text-xs text-on-surface-muted">
                        @if ($listing->attachments->contains('jenis', 'SURAT_NON_B3'))
                            <span class="text-success font-medium">✓ Bukti Non-B3</span>
                        @endif
                        @if ($listing->attachments->contains('jenis', 'SERTIFIKAT_LAB'))
                            <span>• Uji Lab</span>
                        @endif
                    </div>
                    <x-ui.button href="{{ route('catalog.show', $listing) }}" variant="secondary" size="sm">
                        Rincian Material →
                    </x-ui.button>
                </div>
            </x-ui.card>
        @empty
            <div class="col-span-full">
                <x-ui.card class="text-center py-12">
                    <p class="text-on-surface-muted text-body-md">
                        Belum ada material sisa produksi yang sesuai dengan pencarian Anda.
                    </p>
                </x-ui.card>
            </div>
        @endforelse
    </div>

    @if ($listings->hasPages())
        <div class="mt-6">
            {{ $listings->links() }}
        </div>
    @endif
@endsection

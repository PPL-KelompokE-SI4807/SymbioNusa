@extends('layouts.app')

@section('title', 'Listing Saya')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="hover:underline">Dashboard</a>
    <span class="mx-2 text-on-surface-muted" aria-hidden="true">/</span>
    <span class="text-on-surface" aria-current="page">Listing Saya</span>
@endsection

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <x-ui.page-header
            title="Listing Saya"
            subtitle="Kelola penawaran material sisa produksi industri Anda, pantau ketersediaan stok, dan lampiran dokumen kepatuhan."
            class="mb-0"
        />
        <div>
            <x-ui.button href="{{ route('seller.listings.create') }}" variant="primary">
                + Buat Listing
            </x-ui.button>
        </div>
    </div>

    @if (session('status'))
        <x-ui.alert variant="success" class="mb-6">
            {{ session('status') }}
        </x-ui.alert>
    @endif

    {{-- Filter Bar --}}
    <x-ui.card class="mb-6">
        <form method="GET" action="{{ route('seller.listings.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
            <div>
                <label for="filter-q" class="block text-label-sm text-on-surface mb-1">Cari Material / Lokasi</label>
                <x-ui.input
                    id="filter-q"
                    name="q"
                    placeholder="Contoh: Cacahan PP, Bandung..."
                    :value="request('q')"
                />
            </div>
            <div>
                <label for="filter-kategori" class="block text-label-sm text-on-surface mb-1">Kategori Material</label>
                <x-ui.select id="filter-kategori" name="kategori">
                    <option value="">Semua Kategori</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id_categories }}" {{ request('kategori') == $cat->id_categories ? 'selected' : '' }}>
                            {{ $cat->nama }} {{ $cat->is_b3_prohibited ? '(B3 Dilarang)' : '' }}
                        </option>
                    @endforeach
                </x-ui.select>
            </div>
            <div>
                <label for="filter-status" class="block text-label-sm text-on-surface mb-1">Status Listing</label>
                <x-ui.select id="filter-status" name="status">
                    <option value="">Semua Status</option>
                    <option value="DRAF" {{ request('status') === 'DRAF' ? 'selected' : '' }}>Draf</option>
                    <option value="PUBLISHED" {{ request('status') === 'PUBLISHED' ? 'selected' : '' }}>Dipublikasikan</option>
                    <option value="DIARSIPKAN" {{ request('status') === 'DIARSIPKAN' ? 'selected' : '' }}>Diarsipkan</option>
                </x-ui.select>
            </div>
            <div class="flex gap-2">
                <x-ui.button type="submit" variant="secondary" class="flex-1">
                    Terapkan Filter
                </x-ui.button>
                @if (request()->hasAny(['q', 'kategori', 'status']))
                    <x-ui.button href="{{ route('seller.listings.index') }}" variant="ghost">
                        Reset
                    </x-ui.button>
                @endif
            </div>
        </form>
    </x-ui.card>

    {{-- Tabel Listing --}}
    <x-ui.card class="overflow-hidden p-0">
        <div class="overflow-x-auto">
            <x-ui.table label="Daftar listing sisa produksi">
                <x-slot:head>
                    <th>Material & Kategori</th>
                    <th class="text-right">Stok / Volume (kg)</th>
                    <th class="text-right">Harga (Rp/kg)</th>
                    <th>Lokasi</th>
                    <th>Bukti Material</th>
                    <th>Status</th>
                    <th class="text-right">Aksi</th>
                </x-slot:head>
                    @forelse ($listings as $listing)
                        <tr>
                            <td>
                                <div class="font-semibold text-on-surface">
                                    <a href="{{ route('seller.listings.show', $listing) }}" class="hover:underline text-primary">
                                        {{ $listing->judul }}
                                    </a>
                                </div>
                                <div class="text-body-xs text-on-surface-muted">
                                    {{ $listing->category?->nama ?? '–' }}
                                </div>
                            </td>
                            <td class="text-right font-medium">
                                <div>{{ \App\Support\Format::number($listing->stok_kg) }} kg</div>
                                <div class="text-body-xs text-on-surface-muted">
                                    dari {{ \App\Support\Format::number($listing->volume_kg) }} kg
                                </div>
                            </td>
                            <td class="text-right font-medium">
                                {{ \App\Support\Format::rupiah($listing->harga_per_kg) }}
                            </td>
                            <td class="text-body-sm text-on-surface">
                                {{ $listing->lokasi ?? '–' }}
                            </td>
                            <td>
                                <div class="flex items-center gap-1.5 text-body-xs">
                                    @php
                                        $hasNonB3 = $listing->attachments->contains('jenis', 'SURAT_NON_B3');
                                        $hasFoto = $listing->attachments->contains('jenis', 'FOTO_SAMPEL');
                                        $hasLab = $listing->attachments->contains('jenis', 'SERTIFIKAT_LAB');
                                    @endphp
                                    <span title="Surat Pernyataan Non-B3" class="px-1.5 py-0.5 rounded text-xs {{ $hasNonB3 ? 'bg-primary/10 text-primary font-medium' : 'bg-surface-muted text-on-surface-muted' }}">
                                        Non-B3
                                    </span>
                                    <span title="Foto Sampel" class="px-1.5 py-0.5 rounded text-xs {{ $hasFoto ? 'bg-primary/10 text-primary font-medium' : 'bg-surface-muted text-on-surface-muted' }}">
                                        Foto
                                    </span>
                                    @if ($listing->category?->wajib_sertifikat_lab)
                                        <span title="Sertifikat Lab (Wajib Kategori)" class="px-1.5 py-0.5 rounded text-xs {{ $hasLab ? 'bg-primary/10 text-primary font-medium' : 'bg-danger/10 text-danger font-medium' }}">
                                            Lab
                                        </span>
                                    @elseif ($hasLab)
                                        <span title="Sertifikat Lab" class="px-1.5 py-0.5 rounded text-xs bg-primary/10 text-primary font-medium">
                                            Lab
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <x-ui.badge :variant="\App\Support\StatusMap::badge('listings.status', $listing->status)">
                                    {{ \App\Support\StatusMap::label('listings.status', $listing->status) }}
                                </x-ui.badge>
                            </td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <x-ui.button href="{{ route('seller.listings.show', $listing) }}" variant="ghost" size="sm">
                                        Lihat
                                    </x-ui.button>
                                    @if ($listing->status !== 'DIARSIPKAN')
                                        <x-ui.button href="{{ route('seller.listings.edit', $listing) }}" variant="ghost" size="sm">
                                            Ubah
                                        </x-ui.button>
                                        <form method="POST" action="{{ route('seller.listings.archive', $listing) }}" onsubmit="return confirm('Apakah Anda yakin ingin mengarsipkan listing ini?')">
                                            @csrf
                                            <x-ui.button type="submit" variant="ghost" size="sm" class="text-danger hover:text-danger">
                                                Arsipkan
                                            </x-ui.button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-10 text-on-surface-muted">
                                Tidak ada data listing material yang ditemukan.
                            </td>
                        </tr>
                    @endforelse
            </x-ui.table>
        </div>

        @if ($listings->hasPages())
            <div class="p-4 border-t border-border">
                {{ $listings->links() }}
            </div>
        @endif
    </x-ui.card>
@endsection

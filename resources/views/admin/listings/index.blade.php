@extends('layouts.app')

@section('title', 'Moderasi Listing')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="hover:underline">Dashboard</a>
    <span class="mx-2 text-on-surface-muted" aria-hidden="true">/</span>
    <span class="text-on-surface" aria-current="page">Moderasi Listing</span>
@endsection

@section('content')
    <x-ui.page-header
        title="Moderasi Listing"
        subtitle="Pantau semua listing sisa produksi dan turunkan listing yang melanggar ketentuan dari katalog."
    />

    @if (session('status'))
        <x-ui.alert variant="success" class="mb-6">{{ session('status') }}</x-ui.alert>
    @endif
    @if (session('error'))
        <x-ui.alert variant="danger" class="mb-6">{{ session('error') }}</x-ui.alert>
    @endif

    <x-ui.card class="mb-6">
        <form method="GET" action="{{ route('admin.listings.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
            <div>
                <label for="filter-q" class="block text-label-sm text-on-surface mb-1">Cari Material / Lokasi</label>
                <x-ui.input id="filter-q" name="q" placeholder="Contoh: Cacahan PP, Bandung..." :value="request('q')" />
            </div>
            <div>
                <label for="filter-kategori" class="block text-label-sm text-on-surface mb-1">Kategori Material</label>
                <x-ui.select id="filter-kategori" name="kategori">
                    <option value="">Semua Kategori</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id_categories }}" @selected(request('kategori') == $cat->id_categories)>{{ $cat->nama }}</option>
                    @endforeach
                </x-ui.select>
            </div>
            <div>
                <label for="filter-status" class="block text-label-sm text-on-surface mb-1">Status Listing</label>
                <x-ui.select id="filter-status" name="status">
                    <option value="">Semua Status</option>
                    @foreach (['PUBLISHED' => 'Dipublikasikan', 'DRAF' => 'Draf', 'DIARSIPKAN' => 'Diarsipkan'] as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                    @endforeach
                </x-ui.select>
            </div>
            <div class="flex gap-2">
                <x-ui.button type="submit" variant="secondary" class="flex-1">Terapkan Filter</x-ui.button>
                @if (request()->hasAny(['q', 'kategori', 'status']))
                    <x-ui.button href="{{ route('admin.listings.index') }}" variant="secondary">Reset</x-ui.button>
                @endif
            </div>
        </form>
    </x-ui.card>

    <x-ui.card class="overflow-hidden p-0">
        <div class="overflow-x-auto">
            <x-ui.table label="Daftar listing untuk moderasi">
                <x-slot:head>
                    <th>Material & Kategori</th>
                    <th>Perusahaan</th>
                    <th class="text-right">Stok (kg)</th>
                    <th class="text-right pr-8">Harga (Rp/kg)</th>
                    <th>Status</th>
                    <th class="text-right">Aksi</th>
                </x-slot:head>
                @forelse ($listings as $listing)
                    <tr>
                        <td>
                            <div class="font-semibold text-on-surface">{{ $listing->judul }}</div>
                            <div class="text-body-xs text-on-surface-muted">{{ $listing->category?->nama ?? '–' }} · {{ $listing->lokasi ?? '–' }}</div>
                        </td>
                        <td class="text-body-sm">{{ $listing->company?->nama ?? '–' }}</td>
                        <td class="text-right font-medium">{{ \App\Support\Format::number($listing->stok_kg) }}</td>
                        <td class="text-right font-medium pr-8">{{ \App\Support\Format::rupiah($listing->harga_per_kg) }}</td>
                        <td>
                            <x-ui.badge :variant="\App\Support\StatusMap::badge('listings.status', $listing->status)">
                                {{ \App\Support\StatusMap::label('listings.status', $listing->status) }}
                            </x-ui.badge>
                        </td>
                        <td class="text-right">
                            <div class="flex items-center justify-end gap-2">
                                @if ($listing->status === 'PUBLISHED')
                                    <x-ui.button href="{{ route('catalog.show', $listing) }}" variant="text">Lihat</x-ui.button>
                                @endif
                                @if ($listing->status !== 'DIARSIPKAN')
                                    <form method="POST" action="{{ route('admin.listings.archive', $listing) }}" onsubmit="return confirm('Turunkan listing ini dari katalog? Status listing menjadi Diarsipkan.')">
                                        @csrf
                                        <x-ui.button type="submit" variant="text-danger">Turunkan</x-ui.button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-10 text-on-surface-muted">Tidak ada listing yang cocok dengan filter.</td>
                    </tr>
                @endforelse
            </x-ui.table>
        </div>

        @if ($listings->hasPages())
            <div class="p-4 border-t border-border">{{ $listings->links() }}</div>
        @endif
    </x-ui.card>
@endsection

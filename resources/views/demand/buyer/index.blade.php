@extends('layouts.app')

@section('title', 'Kebutuhan Saya')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="hover:underline">Dashboard</a>
    <span class="mx-2 text-on-surface-muted" aria-hidden="true">/</span>
    <span class="text-on-surface" aria-current="page">Kebutuhan Saya</span>
@endsection

@section('content')
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between mb-6">
        <x-ui.page-header
            title="Kebutuhan Saya"
            subtitle="Kelola publikasi kebutuhan bahan baku sekunder untuk industri Anda."
            class="mb-0"
        />
        <div>
            <x-ui.button href="{{ route('buyer.demands.create') }}" variant="primary" size="md">
                + Publikasikan Kebutuhan
            </x-ui.button>
        </div>
    </div>

    @if (session('success'))
        <x-ui.alert variant="success" class="mb-6">
            {{ session('success') }}
        </x-ui.alert>
    @endif

    {{-- KPI Strip Ringkasan --}}
    <div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
        <x-ui.kpi label="Total Kebutuhan" :value="$kpi['total']" caption="Permintaan terdaftar" />
        <x-ui.kpi label="Kebutuhan Terbuka" :value="$kpi['terbuka']" caption="Tayang di pasar" />
        <x-ui.kpi label="Terpenuhi" :value="$kpi['terpenuhi']" caption="Telah mendapatkan suplai" />
        <x-ui.kpi label="Draf" :value="$kpi['draf']" caption="Belum dipublikasikan" />
    </div>

    {{-- Filter Bar --}}
    <x-ui.card class="mb-6">
        <form method="GET" action="{{ route('buyer.demands.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
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
                <label for="filter-status" class="block text-label-sm text-on-surface mb-1">Status Kebutuhan</label>
                <x-ui.select id="filter-status" name="status">
                    <option value="">Semua Status</option>
                    <option value="DRAF" {{ request('status') === 'DRAF' ? 'selected' : '' }}>Draf</option>
                    <option value="TERBUKA" {{ request('status') === 'TERBUKA' ? 'selected' : '' }}>Terbuka</option>
                    <option value="TERPENUHI" {{ request('status') === 'TERPENUHI' ? 'selected' : '' }}>Terpenuhi</option>
                    <option value="KEDALUWARSA" {{ request('status') === 'KEDALUWARSA' ? 'selected' : '' }}>Kedaluwarsa</option>
                </x-ui.select>
            </div>
            <div class="flex gap-2">
                <x-ui.button type="submit" variant="secondary" size="md" class="w-full">
                    Saring
                </x-ui.button>
                @if (request()->hasAny(['category', 'status']))
                    <x-ui.button href="{{ route('buyer.demands.index') }}" variant="ghost" size="md">
                        Reset
                    </x-ui.button>
                @endif
            </div>
        </form>
    </x-ui.card>

    <x-ui.card padding="false">
        <x-ui.table label="Daftar Kebutuhan Bahan Baku Buyer">
            <x-slot:head>
                <th scope="col" class="px-4 py-3 text-left">Komoditas / Kategori</th>
                <th scope="col" class="px-4 py-3 text-left">Volume</th>
                <th scope="col" class="px-4 py-3 text-left">Frekuensi</th>
                <th scope="col" class="px-4 py-3 text-left">Harga Maksimal</th>
                <th scope="col" class="px-4 py-3 text-left">Berlaku Sampai</th>
                <th scope="col" class="px-4 py-3 text-left">Status</th>
                <th scope="col" class="px-4 py-3 text-right">Aksi</th>
            </x-slot:head>

            @forelse ($demands as $demand)
                @php
                    $statusVal = $demand->status instanceof \App\Enums\DemandRequestStatus ? $demand->status->value : $demand->status;
                    $statusBadge = $demand->status instanceof \App\Enums\DemandRequestStatus ? $demand->status->badge() : \App\Support\StatusMap::badge('demand_requests.status', $demand->status);
                    $statusLabel = $demand->status instanceof \App\Enums\DemandRequestStatus ? $demand->status->label() : \App\Support\StatusMap::label('demand_requests.status', $demand->status);
                    $frekuensiLabel = $demand->frekuensi instanceof \App\Enums\DemandRequestFrequency ? $demand->frekuensi->label() : \App\Support\StatusMap::label('demand_requests.frekuensi', $demand->frekuensi);
                @endphp
                <tr class="border-t border-outline hover:bg-surface-alt/50 transition">
                    <td class="px-4 py-3">
                        <div class="font-semibold text-on-surface">{{ $demand->category?->nama ?? '–' }}</div>
                        @if ($demand->spesifikasi_min && is_array($demand->spesifikasi_min))
                            <div class="text-xs text-on-surface-muted truncate max-w-xs mt-0.5">
                                @foreach ($demand->spesifikasi_min as $k => $v)
                                    <span class="inline-block mr-2">{{ $k }}: <strong class="text-on-surface">{{ $v }}</strong></span>
                                @endforeach
                            </div>
                        @endif
                    </td>
                    <td class="px-4 py-3 font-medium text-on-surface tabular-nums">
                        {{ number_format((float) $demand->volume_kg, 2, ',', '.') }} kg
                    </td>
                    <td class="px-4 py-3 text-on-surface">
                        {{ $frekuensiLabel }}
                    </td>
                    <td class="px-4 py-3 font-medium text-on-surface tabular-nums">
                        {{ $demand->harga_maks ? \App\Support\Format::rupiah($demand->harga_maks) . '/kg' : 'Terbuka' }}
                    </td>
                    <td class="px-4 py-3 text-on-surface-muted">
                        {{ $demand->berlaku_sampai ? \App\Support\Format::date($demand->berlaku_sampai) : 'Tidak dibatasi' }}
                    </td>
                    <td class="px-4 py-3">
                        <x-ui.badge :variant="$statusBadge">
                            {{ $statusLabel }}
                        </x-ui.badge>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-2">
                            @if ($statusVal === 'DRAF')
                                <x-ui.button href="{{ route('buyer.demands.edit', $demand) }}" variant="secondary" size="sm">
                                    Ubah
                                </x-ui.button>
                                <form method="POST" action="{{ route('buyer.demands.destroy', $demand) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus draf kebutuhan ini?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <x-ui.button type="submit" variant="ghost" size="sm" class="text-error hover:bg-error/10">
                                        Hapus
                                    </x-ui.button>
                                </form>
                            @elseif ($statusVal === 'TERBUKA')
                                <x-ui.button href="{{ route('buyer.demands.edit', $demand) }}" variant="secondary" size="sm">
                                    Ubah
                                </x-ui.button>
                                <form method="POST" action="{{ route('buyer.demands.close', $demand) }}" onsubmit="return confirm('Tutup permintaan kebutuhan ini dari pasar?');" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <x-ui.button type="submit" variant="ghost" size="sm" class="text-on-surface-muted hover:text-on-surface">
                                        Tutup
                                    </x-ui.button>
                                </form>
                            @else
                                <span class="text-xs text-on-surface-muted">–</span>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-4 py-8 text-center text-on-surface-muted">
                        Belum ada kebutuhan bahan baku yang dipublikasikan. Klik tombol "+ Publikasikan Kebutuhan" di atas untuk memulai.
                    </td>
                </tr>
            @endforelse
        </x-ui.table>

        @if ($demands->hasPages())
            <div class="p-4 border-t border-outline">
                {{ $demands->links() }}
            </div>
        @endif
    </x-ui.card>
@endsection

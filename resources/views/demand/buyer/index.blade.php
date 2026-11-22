@extends('layouts.app')

@section('title', 'Kebutuhan Saya')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="hover:underline">Dashboard</a>
    <span class="mx-2 text-on-surface-muted" aria-hidden="true">/</span>
    <span class="text-on-surface" aria-current="page">Kebutuhan Saya</span>
@endsection

@section('content')
    <x-ui.page-header
        title="Kebutuhan Saya"
        subtitle="Kelola publikasi kebutuhan bahan baku sekunder untuk industri Anda.">
        <x-slot:actions>
            <x-ui.button href="{{ route('buyer.demands.create') }}">
                <x-ui.icon name="plus" />
                Publikasikan Kebutuhan
            </x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    @if (session('success'))
        <x-ui.alert variant="success" class="mb-6">
            {{ session('success') }}
        </x-ui.alert>
    @endif

    {{-- Strip ringkasan: satu permukaan --}}
    <x-ui.summary-strip label="Ringkasan kebutuhan" class="mb-6">
        <x-ui.kpi label="Total Kebutuhan" :value="$kpi['total']" caption="Permintaan terdaftar" />
        <x-ui.kpi label="Kebutuhan Terbuka" :value="$kpi['terbuka']" caption="Tayang di pasar" />
        <x-ui.kpi label="Terpenuhi" :value="$kpi['terpenuhi']" caption="Telah mendapatkan suplai" />
        <x-ui.kpi label="Draf" :value="$kpi['draf']" caption="Belum dipublikasikan" />
    </x-ui.summary-strip>

    {{-- Filter Bar --}}
    <x-ui.card class="mb-6">
        <form method="GET" action="{{ route('buyer.demands.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
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
                <label for="filter-status" class="mb-2 block text-label-md font-semibold text-on-surface">Status Kebutuhan</label>
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
                    <x-ui.button href="{{ route('buyer.demands.index') }}" variant="text" size="md">
                        Reset
                    </x-ui.button>
                @endif
            </div>
        </form>
    </x-ui.card>

    <x-ui.card :padding="false">
        {{-- Di bawah 1024 px tabel menjadi baris ledger (DESIGN.md › Responsive); tabel tetap untuk layar lebar. --}}
        @if ($demands->count() > 0)
            <ul class="divide-y divide-outline-hairline lg:hidden" aria-label="Daftar kebutuhan bahan baku">
                @foreach ($demands as $demand)
                    @php
                        $statusVal = $demand->status instanceof \App\Enums\DemandRequestStatus ? $demand->status->value : $demand->status;
                        $statusBadge = $demand->status instanceof \App\Enums\DemandRequestStatus ? $demand->status->badge() : \App\Support\StatusMap::badge('demand_requests.status', $demand->status);
                        $statusLabel = $demand->status instanceof \App\Enums\DemandRequestStatus ? $demand->status->label() : \App\Support\StatusMap::label('demand_requests.status', $demand->status);
                        $frekuensiLabel = $demand->frekuensi instanceof \App\Enums\DemandRequestFrequency ? $demand->frekuensi->label() : \App\Support\StatusMap::label('demand_requests.frekuensi', $demand->frekuensi);
                    @endphp
                    <li class="px-[18px] py-4 sm:px-6">
                        <p class="text-dash-row text-on-surface">{{ $demand->category?->nama ?? '–' }}</p>
                        <p class="mt-0.5 text-body-sm tabular-nums text-on-surface">
                            {{ number_format((float) $demand->volume_kg, 2, ',', '.') }} kg · {{ $frekuensiLabel }} ·
                            {{ $demand->harga_maks ? 'maks. '.\App\Support\Format::rupiah($demand->harga_maks).'/kg' : 'harga terbuka' }}
                        </p>
                        <p class="text-body-sm text-on-surface-muted">
                            Berlaku {{ $demand->berlaku_sampai ? 's.d. '.\App\Support\Format::date($demand->berlaku_sampai) : 'tanpa batas' }}
                        </p>
                        <div class="mt-2 flex flex-wrap items-center justify-between gap-3">
                            <x-ui.badge :variant="$statusBadge">{{ $statusLabel }}</x-ui.badge>
                            @if (in_array($statusVal, ['DRAF', 'TERBUKA'], true))
                                <div class="flex items-center gap-3">
                                    <x-ui.button href="{{ route('buyer.demands.edit', $demand) }}" variant="secondary" size="lg">Ubah</x-ui.button>
                                    @if ($statusVal === 'DRAF')
                                        <form method="POST" action="{{ route('buyer.demands.destroy', $demand) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus draf kebutuhan ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <x-ui.button type="submit" variant="text-danger" class="min-h-11 px-1">Hapus</x-ui.button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('buyer.demands.close', $demand) }}" onsubmit="return confirm('Tutup permintaan kebutuhan ini dari pasar?');">
                                            @csrf
                                            @method('PATCH')
                                            <x-ui.button type="submit" variant="text" class="min-h-11 px-1">Tutup</x-ui.button>
                                        </form>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif

        <x-ui.table label="Daftar Kebutuhan Bahan Baku Buyer" class="{{ $demands->count() > 0 ? 'hidden lg:block' : '' }}">
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
                <tr class="h-14 border-t border-outline-hairline hover:bg-surface-muted/60">
                    <td class="px-4 py-3">
                        <div class="text-dash-row text-on-surface">{{ $demand->category?->nama ?? '–' }}</div>
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
                                <x-ui.button href="{{ route('buyer.demands.edit', $demand) }}" variant="secondary">
                                    Ubah
                                </x-ui.button>
                                <form method="POST" action="{{ route('buyer.demands.destroy', $demand) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus draf kebutuhan ini?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <x-ui.button type="submit" variant="text-danger" class="min-h-10 px-1">
                                        Hapus
                                    </x-ui.button>
                                </form>
                            @elseif ($statusVal === 'TERBUKA')
                                <x-ui.button href="{{ route('buyer.demands.edit', $demand) }}" variant="secondary">
                                    Ubah
                                </x-ui.button>
                                <form method="POST" action="{{ route('buyer.demands.close', $demand) }}" onsubmit="return confirm('Tutup permintaan kebutuhan ini dari pasar?');" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <x-ui.button type="submit" variant="text" class="min-h-10 px-1">
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
                    <td colspan="7" class="px-4 py-10 text-center">
                        <p class="text-dash-sub text-on-surface">Belum ada kebutuhan bahan baku</p>
                        <p class="mt-1 text-body-sm text-on-surface-muted">Publikasikan kebutuhan agar Seller bisa menawarkan materialnya.</p>
                    </td>
                </tr>
            @endforelse
        </x-ui.table>

        @if ($demands->hasPages())
            <div class="p-4 border-t border-outline-hairline">
                {{ $demands->links() }}
            </div>
        @endif
    </x-ui.card>
@endsection

@extends('layouts.app')

@section('title', 'Purchase Order')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="hover:text-on-surface">Dashboard</a>
    <span class="text-on-surface-muted">/</span>
    <span class="text-on-surface" aria-current="page">Purchase Order</span>
@endsection

@section('content')
    <x-ui.page-header title="Purchase Order" subtitle="Kelola pesanan pembelian resmi, konfirmasi seller, dan reservasi stok material.">
        @if (auth()->user()?->role === 'BUYER')
            <x-slot:actions>
                <x-ui.button href="{{ route('po.create') }}" variant="primary">
                    + Buat PO
                </x-ui.button>
            </x-slot:actions>
        @endif
    </x-ui.page-header>

    @if (session('status'))
        <div class="mb-6">
            <x-ui.alert variant="success">
                {{ session('status') }}
            </x-ui.alert>
        </div>
    @endif

    {{-- Kartu KPI (Screen 10) --}}
    <div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
        <x-ui.kpi label="Draf" :value="$totalDraf" caption="Belum dikirim ke Seller" />
        <x-ui.kpi label="Menunggu Konfirmasi" :value="$totalMenunggu" caption="Perlu konfirmasi Seller" />
        <x-ui.kpi label="Dikonfirmasi" :value="$totalDikonfirmasi" caption="Stok telah direservasi" />
        <x-ui.kpi label="Ditolak / Dibatalkan" :value="$totalDitolakBatal" caption="Pesanan tidak berlanjut" />
    </div>

    {{-- Filter Bar --}}
    <x-ui.card class="mb-6">
        <form method="GET" action="{{ route('po.index') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-4 items-end">
            <div class="sm:col-span-2">
                <x-ui.field label="Pencarian" for="q">
                    <x-ui.input id="q" name="q" :value="request('q')" placeholder="Cari No. PO (mis. PO-0001) atau material..." />
                </x-ui.field>
            </div>
            <div>
                <x-ui.field label="Status PO" for="status">
                    <x-ui.select id="status" name="status">
                        <option value="">Semua Status</option>
                        @foreach (\App\Enums\OrderStatus::cases() as $case)
                            <option value="{{ $case->value }}" @selected(request('status') === $case->value)>
                                {{ $case->label() }}
                            </option>
                        @endforeach
                    </x-ui.select>
                </x-ui.field>
            </div>
            <div class="flex gap-2">
                <x-ui.button type="submit" variant="primary" class="w-full justify-center">
                    Filter
                </x-ui.button>
                @if (request()->hasAny(['q', 'status']))
                    <x-ui.button href="{{ route('po.index') }}" variant="secondary">Reset</x-ui.button>
                @endif
            </div>
        </form>
    </x-ui.card>

    {{-- Tabel Purchase Order --}}
    <x-ui.card :padding="false">
        <x-ui.table label="Tabel Daftar Purchase Order">
            <x-slot:head>
                <th class="px-4 py-3">No. PO</th>
                <th class="px-4 py-3">Material</th>
                <th class="px-4 py-3">{{ auth()->user()?->role === 'SELLER' ? 'Buyer' : 'Seller' }}</th>
                <th class="px-4 py-3 text-right">Volume</th>
                <th class="px-4 py-3 text-right">Harga Satuan</th>
                <th class="px-4 py-3 text-right">Total Nominal</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3 text-right">Aksi</th>
            </x-slot:head>

            @forelse ($orders as $order)
                @php
                    $listing = $order->rfqContract?->listing;
                    $statusValue = $order->status instanceof \App\Enums\OrderStatus ? $order->status->value : (string) $order->status;
                @endphp
                <tr class="border-t border-outline hover:bg-surface-container-lowest">
                    <td class="px-4 py-3 font-mono font-medium text-primary whitespace-nowrap">
                        {{ $order->formattedNumber() }}
                    </td>
                    <td class="px-4 py-3">
                        <span class="font-medium text-on-surface">{{ $listing?->judul ?? '–' }}</span>
                        @if ($listing?->category)
                            <span class="block text-body-xs text-on-surface-muted">{{ $listing->category->nama }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-body-sm text-on-surface">
                        @if (auth()->user()?->role === 'SELLER')
                            {{ $order->buyer?->nama ?? '–' }}
                        @else
                            {{ $order->seller?->nama ?? '–' }}
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right font-medium text-on-surface tabular-nums whitespace-nowrap">
                        {{ \App\Support\Format::kg($order->volume_kg) }}
                    </td>
                    <td class="px-4 py-3 text-right text-body-sm text-on-surface tabular-nums whitespace-nowrap">
                        {{ \App\Support\Format::rupiah($order->harga_per_kg) }}/kg
                    </td>
                    <td class="px-4 py-3 text-right font-semibold text-primary tabular-nums whitespace-nowrap">
                        {{ \App\Support\Format::rupiah($order->totalNominal()) }}
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        <x-ui.badge :variant="\App\Support\StatusMap::badge('purchase_orders.status', $statusValue)">
                            {{ \App\Support\StatusMap::label('purchase_orders.status', $statusValue) }}
                        </x-ui.badge>
                    </td>
                    <td class="px-4 py-3 text-right whitespace-nowrap">
                        <x-ui.button href="{{ route('po.show', $order) }}" variant="secondary" size="sm">
                            Lihat
                        </x-ui.button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="px-4 py-12 text-center text-on-surface-muted">
                        Tidak ada data Purchase Order yang cocok dengan kriteria filter.
                    </td>
                </tr>
            @endforelse
        </x-ui.table>

        @if ($orders->hasPages())
            <div class="border-t border-outline p-4">
                {{ $orders->links() }}
            </div>
        @endif
    </x-ui.card>
@endsection

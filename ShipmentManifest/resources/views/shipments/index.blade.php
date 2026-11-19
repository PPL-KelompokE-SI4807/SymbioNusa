@extends('layouts.app')

@section('title', 'Pengiriman & Manifes')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Pengiriman &amp; Manifes Angkut</h1>
            <p class="mt-1 text-sm text-zinc-500">Catat pengiriman material, terbitkan manifes angkut, dan pantau statusnya sampai tiba.</p>
        </div>
        @if ($canCreate)
            <a href="{{ route('shipments.create') }}" class="btn-primary">+ Buat Pengiriman</a>
        @endif
    </div>

    {{-- Ringkasan --}}
    <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
        @php
            $cards = [
                ['Draf', \App\Enums\ShipmentStatus::Draf],
                ['Dalam Pengiriman', \App\Enums\ShipmentStatus::DalamPengiriman],
                ['Tiba', \App\Enums\ShipmentStatus::Tiba],
                ['Dibatalkan', \App\Enums\ShipmentStatus::Dibatalkan],
            ];
        @endphp
        @foreach ($cards as [$label, $st])
            <a href="{{ route('shipments.index', ['status' => $st->value]) }}" class="card block p-4 transition hover:border-emerald-300">
                <p class="text-xs font-medium uppercase tracking-wide text-zinc-500">{{ $label }}</p>
                <p class="mt-2 text-2xl font-semibold">{{ $counts[$st->value] ?? 0 }}</p>
            </a>
        @endforeach
    </div>

    {{-- Filter --}}
    <form method="GET" action="{{ route('shipments.index') }}" class="card mb-4 flex flex-col gap-3 p-4 sm:flex-row sm:items-end">
        <div class="flex-1">
            <label for="q" class="label">Cari</label>
            <input id="q" type="search" name="q" value="{{ $search }}" placeholder="No. manifes, nomor PO, plat, atau sopir" class="input">
        </div>
        <div class="sm:w-56">
            <label for="status" class="label">Status</label>
            <select id="status" name="status" class="input">
                <option value="">Semua status</option>
                @foreach (\App\Enums\ShipmentStatus::cases() as $s)
                    <option value="{{ $s->value }}" @selected($status === $s)>{{ $s->label() }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex gap-2">
            <button class="btn-primary" type="submit">Terapkan</button>
            @if ($search !== '' || $status)
                <a href="{{ route('shipments.index') }}" class="btn-secondary">Reset</a>
            @endif
        </div>
    </form>

    {{-- Tabel --}}
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-200">
                <thead class="bg-zinc-50">
                    <tr>
                        <th class="th">No. Manifes</th>
                        <th class="th">Order / Material</th>
                        <th class="th">Seller &rarr; Buyer</th>
                        <th class="th">Armada</th>
                        <th class="th text-right">Berat (kg)</th>
                        <th class="th">Berangkat</th>
                        <th class="th">Status</th>
                        <th class="th text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    @forelse ($shipments as $s)
                        <tr class="hover:bg-zinc-50">
                            <td class="td font-medium text-zinc-900">{{ $s->no_manifes ?? '-' }}</td>
                            <td class="td">
                                <p class="font-medium text-zinc-900">{{ $s->order->nomor_po }}</p>
                                <p class="text-xs text-zinc-500">{{ $s->order->material_name }}</p>
                            </td>
                            <td class="td">
                                <p>{{ $s->order->seller?->nama }}</p>
                                <p class="text-xs text-zinc-500">&rarr; {{ $s->order->buyer?->nama }}</p>
                            </td>
                            <td class="td">
                                <p>{{ $s->armada }}</p>
                                <p class="text-xs text-zinc-500">{{ $s->plat }} &middot; {{ $s->sopir }}</p>
                            </td>
                            <td class="td text-right tabular-nums">{{ number_format((float) $s->berat_dikirim_kg, 2, ',', '.') }}</td>
                            <td class="td whitespace-nowrap">{{ $s->jadwal_berangkat->format('d M Y H:i') }}</td>
                            <td class="td"><x-status-badge :status="$s->status" /></td>
                            <td class="td whitespace-nowrap text-right">
                                <a href="{{ route('shipments.show', $s) }}" class="text-sm font-medium text-emerald-700 hover:underline">Lihat</a>
                                @can('update', $s)
                                    <a href="{{ route('shipments.edit', $s) }}" class="ml-3 text-sm font-medium text-zinc-700 hover:underline">Ubah</a>
                                @endcan
                                @can('downloadManifest', $s)
                                    <a href="{{ route('shipments.manifest', $s) }}" target="_blank" class="ml-3 text-sm font-medium text-zinc-700 hover:underline">Manifes</a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center text-sm text-zinc-500">
                                Belum ada data pengiriman{{ ($search !== '' || $status) ? ' yang cocok dengan filter' : '' }}.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($shipments->hasPages())
            <div class="border-t border-zinc-200 px-4 py-3">{{ $shipments->links() }}</div>
        @endif
    </div>
@endsection

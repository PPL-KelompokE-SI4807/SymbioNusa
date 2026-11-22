@extends('layouts.app')

@section('title', 'Tugas Inspeksi Saya')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="hover:underline">Dashboard</a>
    <span class="mx-2 text-on-surface-muted" aria-hidden="true">/</span>
    <span class="text-on-surface" aria-current="page">Tugas Inspeksi</span>
@endsection

@section('content')
    <div class="space-y-6">
        <x-ui.page-header
            title="Tugas Inspeksi Saya"
            subtitle="Daftar jadwal penugasan verifikasi dan inspeksi mutu yang ditugaskan kepada Anda."
        />

        {{-- Filter Status --}}
        <div class="flex flex-wrap items-center gap-2">
            <x-ui.button
                :variant="$currentStatus === null ? 'primary' : 'secondary'"
                href="{{ route('inspector.tasks.index') }}"
            >
                Semua
            </x-ui.button>
            <x-ui.button
                :variant="$currentStatus === 'DITUGASKAN' ? 'primary' : 'secondary'"
                href="{{ route('inspector.tasks.index', ['status' => 'DITUGASKAN']) }}"
            >
                Ditugaskan
            </x-ui.button>
            <x-ui.button
                :variant="$currentStatus === 'DIPROSES' ? 'primary' : 'secondary'"
                href="{{ route('inspector.tasks.index', ['status' => 'DIPROSES']) }}"
            >
                Diproses
            </x-ui.button>
            <x-ui.button
                :variant="$currentStatus === 'SELESAI' ? 'primary' : 'secondary'"
                href="{{ route('inspector.tasks.index', ['status' => 'SELESAI']) }}"
            >
                Selesai
            </x-ui.button>
        </div>

        <x-ui.card :padding="false">
            <x-ui.table label="Daftar Tugas Inspeksi">
                <x-slot:head>
                    <th scope="col" class="px-6 py-3">No. PO</th>
                    <th scope="col" class="px-6 py-3">Perusahaan Seller</th>
                    <th scope="col" class="px-6 py-3">Kategori Material</th>
                    <th scope="col" class="px-6 py-3">Mode</th>
                    <th scope="col" class="px-6 py-3">Jadwal</th>
                    <th scope="col" class="px-6 py-3">Status</th>
                    <th scope="col" class="px-6 py-3 text-right">Aksi</th>
                </x-slot:head>

                @forelse ($inspections as $ins)
                    @php
                        $po = $ins->purchaseOrder;
                        $cat = $po?->rfqContract?->listing?->category;
                    @endphp
                    <tr class="border-t border-outline transition-colors hover:bg-background/50">
                        <td class="px-6 py-4 font-medium text-on-surface">
                            PO #{{ $ins->purchase_orders_id_purchase_order }}
                        </td>
                        <td class="px-6 py-4 text-on-surface">
                            {{ $po?->seller?->nama ?? '-' }}
                        </td>
                        <td class="px-6 py-4 text-on-surface-muted">
                            {{ $cat?->nama ?? 'Umum' }}
                        </td>
                        <td class="px-6 py-4">
                            <x-ui.badge :variant="\App\Support\StatusMap::badge('inspections.mode_verifikasi', $ins->mode_verifikasi)">
                                {{ \App\Support\StatusMap::label('inspections.mode_verifikasi', $ins->mode_verifikasi) }}
                            </x-ui.badge>
                        </td>
                        <td class="px-6 py-4 text-body-sm text-on-surface">
                            {{ $ins->jadwal ? \App\Support\Format::date($ins->jadwal) : '-' }}
                        </td>
                        <td class="px-6 py-4">
                            <x-ui.badge :variant="\App\Support\StatusMap::badge('inspections.status', $ins->status)">
                                {{ \App\Support\StatusMap::label('inspections.status', $ins->status) }}
                            </x-ui.badge>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <x-ui.button
                                variant="secondary"
                                href="{{ route('inspector.verifications.index') }}"
                            >
                                Buka Verifikasi
                            </x-ui.button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-on-surface-muted">
                            <x-ui.icon name="calendar" class="mx-auto size-8 opacity-40" />
                            <p class="mt-2 text-body-md font-medium">Belum ada tugas inspeksi yang ditugaskan kepada Anda.</p>
                        </td>
                    </tr>
                @endforelse
            </x-ui.table>

            @if ($inspections->hasPages())
                <div class="border-t border-outline px-6 py-4">
                    {{ $inspections->links() }}
                </div>
            @endif
        </x-ui.card>
    </div>
@endsection

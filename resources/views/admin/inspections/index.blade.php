@extends('layouts.app')

@section('title', 'Penugasan Inspeksi')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="hover:underline">Dashboard</a>
    <span class="mx-2 text-on-surface-muted" aria-hidden="true">/</span>
    <span class="text-on-surface" aria-current="page">Penugasan Inspeksi</span>
@endsection

@section('content')
    <div class="space-y-8">
        <x-ui.page-header
            title="Penugasan Inspeksi"
            subtitle="Kelola penugasan verifikator dan inspeksi mutu untuk Purchase Order yang telah dikonfirmasi."
        />

        @if (session('success'))
            <x-ui.alert variant="success">
                {{ session('success') }}
            </x-ui.alert>
        @endif

        @if (session('error'))
            <x-ui.alert variant="danger">
                {{ session('error') }}
            </x-ui.alert>
        @endif

        {{-- Bagian 1: Antrean Order Siap Ditugaskan --}}
        <section class="space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-headline-md font-semibold text-on-surface">Order Menunggu Penugasan</h2>
                    <p class="text-body-sm text-on-surface-muted">Purchase Order berstatus Dikonfirmasi yang siap diperiksa verifikator.</p>
                </div>
                <x-ui.badge variant="warning">{{ $pendingOrders->total() }} Menunggu</x-ui.badge>
            </div>

            <x-ui.card :padding="false">
                <x-ui.table label="Antrean Order Menunggu Penugasan">
                    <x-slot:head>
                        <th scope="col" class="px-6 py-3">No. PO</th>
                        <th scope="col" class="px-6 py-3">Seller</th>
                        <th scope="col" class="px-6 py-3">Buyer</th>
                        <th scope="col" class="px-6 py-3">Kategori Material</th>
                        <th scope="col" class="px-6 py-3 text-right">Volume</th>
                        <th scope="col" class="px-6 py-3 text-right">Aksi</th>
                    </x-slot:head>

                    @forelse ($pendingOrders as $po)
                        @php
                            $category = $po->rfqContract?->listing?->category;
                        @endphp
                        <tr class="border-t border-outline transition-colors hover:bg-background/50">
                            <td class="px-6 py-4 font-medium text-on-surface">
                                PO #{{ $po->id_purchase_order }}
                            </td>
                            <td class="px-6 py-4 text-on-surface">
                                {{ $po->seller?->nama ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-on-surface-muted">
                                {{ $po->buyer?->nama ?? '-' }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-on-surface font-medium">{{ $category?->nama ?? 'Umum' }}</span>
                                @if ($category?->tingkat_risiko)
                                    <span class="ml-1.5 inline-block">
                                        <x-ui.badge :variant="\App\Support\StatusMap::badge('categories.tingkat_risiko', $category->tingkat_risiko)">
                                            {{ \App\Support\StatusMap::label('categories.tingkat_risiko', $category->tingkat_risiko) }}
                                        </x-ui.badge>
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right tabular-nums text-on-surface">
                                {{ \App\Support\Format::kg($po->volume_kg) }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <x-ui.button
                                    variant="primary"
                                    href="{{ route('admin.inspections.create', $po) }}"
                                >
                                    Tugaskan Inspector
                                </x-ui.button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-on-surface-muted">
                                <x-ui.icon name="check-circle" class="mx-auto size-8 opacity-40" />
                                <p class="mt-2 text-body-md font-medium">Tidak ada order yang menunggu penugasan inspeksi.</p>
                                <p class="text-body-sm">Semua Purchase Order terkonfirmasi telah ditugaskan.</p>
                            </td>
                        </tr>
                    @endforelse
                </x-ui.table>

                @if ($pendingOrders->hasPages())
                    <div class="border-t border-outline px-6 py-4">
                        {{ $pendingOrders->links() }}
                    </div>
                @endif
            </x-ui.card>
        </section>

        {{-- Bagian 2: Riwayat Penugasan Platform --}}
        <section class="space-y-4 pt-4">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h2 class="text-headline-md font-semibold text-on-surface">Riwayat Penugasan Inspeksi</h2>
                    <p class="text-body-sm text-on-surface-muted">Daftar inspeksi yang sedang berlangsung maupun telah selesai di platform.</p>
                </div>

                {{-- Filter Status --}}
                <div class="flex flex-wrap items-center gap-2">
                    <x-ui.button
                        :variant="$currentStatus === null ? 'primary' : 'secondary'"
                        href="{{ route('admin.inspections.index') }}"
                    >
                        Semua
                    </x-ui.button>
                    <x-ui.button
                        :variant="$currentStatus === 'DITUGASKAN' ? 'primary' : 'secondary'"
                        href="{{ route('admin.inspections.index', ['status' => 'DITUGASKAN']) }}"
                    >
                        Ditugaskan
                    </x-ui.button>
                    <x-ui.button
                        :variant="$currentStatus === 'DIPROSES' ? 'primary' : 'secondary'"
                        href="{{ route('admin.inspections.index', ['status' => 'DIPROSES']) }}"
                    >
                        Diproses
                    </x-ui.button>
                    <x-ui.button
                        :variant="$currentStatus === 'SELESAI' ? 'primary' : 'secondary'"
                        href="{{ route('admin.inspections.index', ['status' => 'SELESAI']) }}"
                    >
                        Selesai
                    </x-ui.button>
                    <x-ui.button
                        :variant="$currentStatus === 'DIBATALKAN' ? 'primary' : 'secondary'"
                        href="{{ route('admin.inspections.index', ['status' => 'DIBATALKAN']) }}"
                    >
                        Dibatalkan
                    </x-ui.button>
                </div>
            </div>

            <x-ui.card :padding="false">
                <x-ui.table label="Riwayat Penugasan Inspeksi">
                    <x-slot:head>
                        <th scope="col" class="px-6 py-3">ID</th>
                        <th scope="col" class="px-6 py-3">No. PO</th>
                        <th scope="col" class="px-6 py-3">Inspector Ditugaskan</th>
                        <th scope="col" class="px-6 py-3">Mode</th>
                        <th scope="col" class="px-6 py-3">Jadwal</th>
                        <th scope="col" class="px-6 py-3">Status</th>
                        <th scope="col" class="px-6 py-3 text-right">Aksi</th>
                    </x-slot:head>

                    @forelse ($recentInspections as $ins)
                        <tr class="border-t border-outline transition-colors hover:bg-background/50">
                            <td class="px-6 py-4 font-mono text-body-sm text-on-surface">
                                #{{ $ins->id_inspection }}
                            </td>
                            <td class="px-6 py-4 font-medium text-on-surface">
                                PO #{{ $ins->purchase_orders_id_purchase_order }}
                            </td>
                            <td class="px-6 py-4 text-on-surface">
                                <span class="font-medium">{{ $ins->inspector?->nama ?? '-' }}</span>
                                <span class="block text-body-xs text-on-surface-muted">{{ $ins->inspector?->inspectorProfile?->wilayah_tugas ?? '-' }}</span>
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
                                <div class="flex items-center justify-end gap-2">
                                    <x-ui.button
                                        variant="secondary"
                                        href="{{ route('admin.inspections.show', $ins) }}"
                                    >
                                        Detail
                                    </x-ui.button>

                                    @if (! in_array($ins->status, [\App\Models\Inspection::STATUS_SELESAI, \App\Models\Inspection::STATUS_DIBATALKAN], true))
                                        <form
                                            method="POST"
                                            action="{{ route('admin.inspections.cancel', $ins) }}"
                                            onsubmit="return confirm('Apakah Anda yakin ingin membatalkan penugasan inspeksi ini?')"
                                        >
                                            @csrf
                                            <x-ui.button variant="danger" type="submit">
                                                Batalkan
                                            </x-ui.button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-on-surface-muted">
                                <x-ui.icon name="calendar" class="mx-auto size-8 opacity-40" />
                                <p class="mt-2 text-body-md font-medium">Belum ada riwayat penugasan inspeksi.</p>
                            </td>
                        </tr>
                    @endforelse
                </x-ui.table>

                @if ($recentInspections->hasPages())
                    <div class="border-t border-outline px-6 py-4">
                        {{ $recentInspections->links() }}
                    </div>
                @endif
            </x-ui.card>
        </section>
    </div>
@endsection

@extends('layouts.app')

@section('title', 'Detail Inspeksi #' . $inspection->id_inspection)

@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="hover:underline">Dashboard</a>
    <span class="mx-2 text-on-surface-muted" aria-hidden="true">/</span>
    <a href="{{ route('admin.inspections.index') }}" class="hover:underline">Penugasan Inspeksi</a>
    <span class="mx-2 text-on-surface-muted" aria-hidden="true">/</span>
    <span class="text-on-surface" aria-current="page">Inspeksi #{{ $inspection->id_inspection }}</span>
@endsection

@section('content')
    <div class="space-y-6">
        <x-ui.page-header
            title="Detail Penugasan Inspeksi #{{ $inspection->id_inspection }}"
            subtitle="Informasi status penugasan verifikator untuk Purchase Order #{{ $inspection->purchase_orders_id_purchase_order }}."
        />

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            {{-- Kolom Kiri / 2 Kolom: Detail Transaksi & Penugasan --}}
            <div class="space-y-6 lg:col-span-2">
                <x-ui.card title="Informasi Penugasan">
                    <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="border-b border-outline pb-3">
                            <dt class="text-body-sm text-on-surface-muted">Status Inspeksi</dt>
                            <dd class="mt-1">
                                <x-ui.badge :variant="\App\Support\StatusMap::badge('inspections.status', $inspection->status)">
                                    {{ \App\Support\StatusMap::label('inspections.status', $inspection->status) }}
                                </x-ui.badge>
                            </dd>
                        </div>

                        <div class="border-b border-outline pb-3">
                            <dt class="text-body-sm text-on-surface-muted">Mode Verifikasi</dt>
                            <dd class="mt-1">
                                <x-ui.badge :variant="\App\Support\StatusMap::badge('inspections.mode_verifikasi', $inspection->mode_verifikasi)">
                                    {{ \App\Support\StatusMap::label('inspections.mode_verifikasi', $inspection->mode_verifikasi) }}
                                </x-ui.badge>
                            </dd>
                        </div>

                        <div class="border-b border-outline pb-3">
                            <dt class="text-body-sm text-on-surface-muted">Jadwal Pelaksanaan</dt>
                            <dd class="mt-1 font-medium text-on-surface">
                                {{ $inspection->jadwal ? \App\Support\Format::date($inspection->jadwal) : '-' }}
                            </dd>
                        </div>

                        <div class="border-b border-outline pb-3">
                            <dt class="text-body-sm text-on-surface-muted">Inspector Ditugaskan</dt>
                            <dd class="mt-1 font-medium text-on-surface">
                                {{ $inspection->inspector?->nama ?? '-' }}
                            </dd>
                        </div>

                        @if ($inspection->alasan_mode)
                            <div class="col-span-2 rounded-md bg-background/60 p-4">
                                <dt class="text-body-sm font-semibold text-on-surface">Catatan / Alasan Mode</dt>
                                <dd class="mt-1 text-body-md text-on-surface-muted">
                                    {{ $inspection->alasan_mode }}
                                </dd>
                            </div>
                        @endif
                    </dl>
                </x-ui.card>

                <x-ui.card title="Data Purchase Order Terkait">
                    @php
                        $po = $inspection->purchaseOrder;
                        $category = $po?->rfqContract?->listing?->category;
                    @endphp
                    <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="border-b border-outline pb-3">
                            <dt class="text-body-sm text-on-surface-muted">Nomor PO</dt>
                            <dd class="mt-1 font-medium text-on-surface">PO #{{ $po?->id_purchase_order }}</dd>
                        </div>

                        <div class="border-b border-outline pb-3">
                            <dt class="text-body-sm text-on-surface-muted">Status PO</dt>
                            <dd class="mt-1">
                                <x-ui.badge :variant="\App\Support\StatusMap::badge('purchase_orders.status', $po?->status)">
                                    {{ \App\Support\StatusMap::label('purchase_orders.status', $po?->status) }}
                                </x-ui.badge>
                            </dd>
                        </div>

                        <div class="border-b border-outline pb-3">
                            <dt class="text-body-sm text-on-surface-muted">Perusahaan Seller</dt>
                            <dd class="mt-1 font-medium text-on-surface">{{ $po?->seller?->nama ?? '-' }}</dd>
                        </div>

                        <div class="border-b border-outline pb-3">
                            <dt class="text-body-sm text-on-surface-muted">Perusahaan Buyer</dt>
                            <dd class="mt-1 font-medium text-on-surface">{{ $po?->buyer?->nama ?? '-' }}</dd>
                        </div>

                        <div class="border-b border-outline pb-3">
                            <dt class="text-body-sm text-on-surface-muted">Kategori Material</dt>
                            <dd class="mt-1 font-medium text-on-surface">{{ $category?->nama ?? 'Umum' }}</dd>
                        </div>

                        <div class="border-b border-outline pb-3">
                            <dt class="text-body-sm text-on-surface-muted">Volume Transaksi</dt>
                            <dd class="mt-1 font-medium text-on-surface">{{ \App\Support\Format::kg($po?->volume_kg) }}</dd>
                        </div>
                    </dl>
                </x-ui.card>
            </div>

            {{-- Kolom Kanan: Aksi & Tindakan Admin --}}
            <div class="space-y-6">
                <x-ui.card title="Tindakan">
                    <div class="space-y-4">
                        <x-ui.button
                            variant="secondary"
                            class="w-full justify-center"
                            href="{{ route('admin.inspections.index') }}"
                        >
                            Kembali ke Daftar
                        </x-ui.button>

                        @if (! in_array($inspection->status, [\App\Models\Inspection::STATUS_SELESAI, \App\Models\Inspection::STATUS_DIBATALKAN], true))
                            <div class="border-t border-outline pt-4">
                                <form
                                    method="POST"
                                    action="{{ route('admin.inspections.cancel', $inspection) }}"
                                    onsubmit="return confirm('Apakah Anda yakin ingin membatalkan penugasan inspeksi ini?')"
                                    class="space-y-3"
                                >
                                    @csrf
                                    <x-ui.field label="Alasan Pembatalan (Opsional)" for="alasan_batal" name="alasan_batal">
                                        <x-ui.input id="alasan_batal" name="alasan_batal" placeholder="Tulis alasan jika dibatalkan..." />
                                    </x-ui.field>

                                    <x-ui.button
                                        variant="danger"
                                        type="submit"
                                        class="w-full justify-center"
                                    >
                                        Batalkan Penugasan
                                    </x-ui.button>
                                </form>
                            </div>
                        @endif
                    </div>
                </x-ui.card>
            </div>
        </div>
    </div>
@endsection

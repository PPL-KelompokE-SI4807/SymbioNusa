@extends('layouts.app')

@section('title', 'Verifikasi Dokumen')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="hover:underline">Dashboard</a>
    <span class="mx-2 text-on-surface-muted" aria-hidden="true">/</span>
    <span class="text-on-surface" aria-current="page">Verifikasi Dokumen</span>
@endsection

@section('content')
    <div class="space-y-6">
        <x-ui.page-header
            title="Verifikasi Dokumen"
            subtitle="Periksa dokumen legalitas perusahaan dan bukti listing sisa produksi non-B3."
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

        {{-- Filter Status --}}
        <div class="flex flex-wrap items-center gap-2">
            <x-ui.button
                :variant="$currentStatus === null ? 'primary' : 'secondary'"
                href="{{ route('inspector.verifications.index') }}"
            >
                Semua
            </x-ui.button>
            <x-ui.button
                :variant="$currentStatus === 'MENUNGGU' ? 'primary' : 'secondary'"
                href="{{ route('inspector.verifications.index', ['status' => 'MENUNGGU']) }}"
            >
                Menunggu Verifikasi
            </x-ui.button>
            <x-ui.button
                :variant="$currentStatus === 'SETUJU' ? 'primary' : 'secondary'"
                href="{{ route('inspector.verifications.index', ['status' => 'SETUJU']) }}"
            >
                Disetujui
            </x-ui.button>
            <x-ui.button
                :variant="$currentStatus === 'DITOLAK' ? 'primary' : 'secondary'"
                href="{{ route('inspector.verifications.index', ['status' => 'DITOLAK']) }}"
            >
                Ditolak
            </x-ui.button>
        </div>

        {{-- Tabel Data --}}
        <x-ui.card :padding="false">
            <x-ui.table label="Daftar Verifikasi Dokumen">
                <x-slot:head>
                    <th scope="col" class="px-6 py-3">No. PO</th>
                    <th scope="col" class="px-6 py-3">Perusahaan Seller</th>
                    <th scope="col" class="px-6 py-3">Perusahaan Buyer</th>
                    <th scope="col" class="px-6 py-3">Status</th>
                    <th scope="col" class="px-6 py-3 text-right">Aksi</th>
                </x-slot:head>

                @forelse ($verifications as $item)
                    <tr class="border-t border-outline transition-colors hover:bg-background/50">
                        <td class="px-6 py-4 font-medium text-on-surface">
                            PO #{{ $item->purchase_orders_id_purchase_order }}
                        </td>
                        <td class="px-6 py-4 text-on-surface">
                            {{ $item->purchaseOrder?->seller?->nama ?? '-' }}
                        </td>
                        <td class="px-6 py-4 text-on-surface-muted">
                            {{ $item->purchaseOrder?->buyer?->nama ?? '-' }}
                        </td>
                        <td class="px-6 py-4">
                            <x-ui.badge :variant="\App\Support\StatusMap::badge('document_verifications.status', $item->status)">
                                {{ \App\Support\StatusMap::label('document_verifications.status', $item->status) }}
                            </x-ui.badge>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <x-ui.button
                                variant="secondary"
                                href="{{ route('inspector.verifications.show', $item) }}"
                            >
                                Periksa Dokumen
                            </x-ui.button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-on-surface-muted">
                            <div class="flex flex-col items-center justify-center space-y-2">
                                <x-ui.icon name="file-text" class="size-8 opacity-40" />
                                <p class="text-body-md font-medium">Belum ada penugasan verifikasi dokumen.</p>
                                <p class="text-body-sm">Semua penugasan verifikasi dokumen non-B3 akan tampil di sini.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </x-ui.table>

            @if ($verifications->hasPages())
                <div class="border-t border-outline px-6 py-4">
                    {{ $verifications->links() }}
                </div>
            @endif
        </x-ui.card>
    </div>
@endsection

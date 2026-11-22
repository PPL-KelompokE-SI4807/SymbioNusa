@extends('layouts.app')

@php
    $listing = $po->rfqContract?->listing;
    $statusValue = $po->status instanceof \App\Enums\OrderStatus ? $po->status->value : (string) $po->status;
    $user = auth()->user();
    $isSeller = $user && ((int) $user->id_company === (int) $po->companies_id_seller);
    $isBuyer = $user && ((int) $user->id_company === (int) $po->companies_id_buyer);
    $stokListing = (float) ($listing?->stok_kg ?? 0);
    $volumePo = (float) $po->volume_kg;
    $stokCukup = $stokListing >= $volumePo;
@endphp

@section('title', $po->formattedNumber() . ' · Purchase Order')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="hover:text-on-surface">Dashboard</a>
    <span class="text-on-surface-muted">/</span>
    <a href="{{ route('po.index') }}" class="hover:text-on-surface">Purchase Order</a>
    <span class="text-on-surface-muted">/</span>
    <span class="text-on-surface" aria-current="page">{{ $po->formattedNumber() }}</span>
@endsection

@section('content')
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-display-sm font-bold text-on-surface">
                    {{ $po->formattedNumber() }}
                </h1>
                <x-ui.badge :variant="\App\Support\StatusMap::badge('purchase_orders.status', $statusValue)">
                    {{ \App\Support\StatusMap::label('purchase_orders.status', $statusValue) }}
                </x-ui.badge>
            </div>
            <p class="mt-1 text-body-sm text-on-surface-muted">
                Dibuat dari kontrak 
                <a href="{{ route('rfq.show', $po->rfq_contracts_id_rfq_contract) }}" class="text-primary hover:underline font-mono">
                    RFQ-{{ str_pad((string) $po->rfq_contracts_id_rfq_contract, 4, '0', STR_PAD_LEFT) }}
                </a>
                oleh <strong>{{ $po->buyer?->nama ?? 'Buyer' }}</strong>.
            </p>
        </div>
        <div>
            <x-ui.button href="{{ route('po.index') }}" variant="secondary">
                &larr; Kembali
            </x-ui.button>
        </div>
    </div>

    @if (session('status'))
        <div class="mb-6">
            <x-ui.alert variant="success">
                {{ session('status') }}
            </x-ui.alert>
        </div>
    @endif

    @if (session('error'))
        <div class="mb-6">
            <x-ui.alert variant="error">
                {{ session('error') }}
            </x-ui.alert>
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6">
            <x-ui.alert variant="error">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-ui.alert>
        </div>
    @endif

    {{-- Stepper Alur Status Order (Screen 12) --}}
    <x-ui.card class="mb-6">
        <h2 class="text-label-md font-semibold text-on-surface-muted mb-4 uppercase tracking-wider">
            Alur Status Pesanan
        </h2>

        @if (in_array($statusValue, ['DITOLAK', 'DIBATALKAN']))
            <div class="rounded-md border p-4 {{ $statusValue === 'DITOLAK' ? 'border-error bg-error-container text-on-error-container' : 'border-outline bg-surface-container text-on-surface' }}">
                <div class="flex items-center gap-2 font-semibold">
                    <span>Pesanan Berakhir ({{ \App\Support\StatusMap::label('purchase_orders.status', $statusValue) }})</span>
                </div>
                <p class="mt-1 text-body-sm">
                    Transaksi ini telah dihentikan dan tidak dapat dilanjutkan ke tahap berikutnya.
                </p>
            </div>
        @else
            @php
                $stages = [
                    'DRAFT' => 'Draf',
                    'MENUNGGU_KONFIRMASI' => 'Menunggu Konfirmasi',
                    'DIKONFIRMASI' => 'Dikonfirmasi',
                    'INSPEKSI' => 'Inspeksi Mutu',
                    'INVOICED' => 'Pembayaran',
                    'SELESAI' => 'Selesai',
                ];

                $orderWeights = [
                    'DRAFT' => 1,
                    'MENUNGGU_KONFIRMASI' => 2,
                    'DIKONFIRMASI' => 3,
                    'INSPEKSI' => 4,
                    'LOLOS_INSPEKSI' => 4,
                    'INVOICED' => 5,
                    'DIBAYAR' => 5,
                    'DALAM_PENGIRIMAN' => 5,
                    'DITERIMA' => 5,
                    'SELESAI' => 6,
                ];

                $currentWeight = $orderWeights[$statusValue] ?? 1;
            @endphp

            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6 text-center text-body-xs font-medium">
                @foreach ($stages as $key => $label)
                    @php
                        $stageWeight = $orderWeights[$key] ?? 1;
                        $isPassed = $currentWeight > $stageWeight;
                        $isCurrent = ($statusValue === $key) || ($key === 'INSPEKSI' && in_array($statusValue, ['INSPEKSI', 'LOLOS_INSPEKSI'])) || ($key === 'INVOICED' && in_array($statusValue, ['INVOICED', 'DIBAYAR', 'DALAM_PENGIRIMAN', 'DITERIMA']));
                    @endphp
                    <div class="rounded-md p-2.5 border {{ $isCurrent ? 'border-primary bg-primary-container text-on-primary-container font-semibold' : ($isPassed ? 'border-success/40 bg-success-container/30 text-on-surface' : 'border-outline bg-surface-container-lowest text-on-surface-muted') }}">
                        <div class="flex items-center justify-center gap-1.5 mb-1">
                            @if ($isPassed)
                                <span class="text-success font-bold">&check;</span>
                            @elseif ($isCurrent)
                                <span class="h-2 w-2 rounded-full bg-primary animate-pulse"></span>
                            @else
                                <span class="h-2 w-2 rounded-full bg-outline"></span>
                            @endif
                            <span>{{ $label }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </x-ui.card>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Kolom Kiri: Rincian PO (2 Kolom) --}}
        <div class="space-y-6 lg:col-span-2">
            <x-ui.card title="Rincian Purchase Order">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 text-body-sm mb-6">
                    <div>
                        <span class="block text-body-xs text-on-surface-muted">Pembeli (Buyer)</span>
                        <span class="font-medium text-on-surface">{{ $po->buyer?->nama ?? '–' }}</span>
                    </div>
                    <div>
                        <span class="block text-body-xs text-on-surface-muted">Penyedia (Seller)</span>
                        <span class="font-medium text-on-surface">{{ $po->seller?->nama ?? '–' }}</span>
                    </div>
                    <div>
                        <span class="block text-body-xs text-on-surface-muted">Material</span>
                        <span class="font-medium text-on-surface">{{ $listing?->judul ?? '–' }}</span>
                        @if ($listing?->category)
                            <span class="block text-body-xs text-on-surface-muted">{{ $listing->category->nama }}</span>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 border-t border-outline pt-4 text-body-sm mb-6">
                    <div>
                        <span class="block text-body-xs text-on-surface-muted">Volume Nominal</span>
                        <span class="text-headline-sm font-semibold tabular-nums text-on-surface">
                            {{ \App\Support\Format::kg($po->volume_kg) }}
                        </span>
                    </div>
                    <div>
                        <span class="block text-body-xs text-on-surface-muted">Harga Satuan</span>
                        <span class="text-headline-sm font-semibold tabular-nums text-on-surface">
                            {{ \App\Support\Format::rupiah($po->harga_per_kg) }}/kg
                        </span>
                    </div>
                    <div>
                        <span class="block text-body-xs text-on-surface-muted">Total Nilai Pesanan</span>
                        <span class="text-headline-sm font-bold tabular-nums text-primary">
                            {{ \App\Support\Format::rupiah($po->totalNominal()) }}
                        </span>
                    </div>
                </div>

                {{-- Batas Toleransi Mutu & Tonase Snapshot --}}
                <div class="border-t border-outline pt-4">
                    <h3 class="text-label-md font-semibold text-on-surface mb-3">
                        Snapshot Toleransi Mutu & Tonase Kategori
                    </h3>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 bg-surface-container-lowest p-3 rounded-md text-body-sm">
                        <div>
                            <span class="block text-body-xs text-on-surface-muted">Toleransi Susut Tonase</span>
                            <span class="font-medium text-on-surface">{{ \App\Support\Format::number($po->toleransi_persen) }}%</span>
                            <span class="block text-body-xs text-on-surface-muted mt-0.5">
                                Batas: {{ \App\Support\Format::kg($po->minVolumeKg()) }} – {{ \App\Support\Format::kg($po->maxVolumeKg()) }}
                            </span>
                        </div>
                        <div>
                            <span class="block text-body-xs text-on-surface-muted">Batas Kadar Air Maksimum</span>
                            <span class="font-medium text-on-surface">
                                {{ $po->kadar_air_maks_persen !== null ? \App\Support\Format::number($po->kadar_air_maks_persen) . '%' : '–' }}
                            </span>
                            @if ($po->toleransi_kadar_air_poin !== null)
                                <span class="block text-body-xs text-on-surface-muted mt-0.5">
                                    Toleransi: {{ \App\Support\Format::number($po->toleransi_kadar_air_poin) }} poin
                                </span>
                            @endif
                        </div>
                        <div>
                            <span class="block text-body-xs text-on-surface-muted">Jarak Pengiriman</span>
                            <span class="font-medium text-on-surface">
                                {{ $po->jarak_km !== null ? \App\Support\Format::number($po->jarak_km) . ' km' : '–' }}
                            </span>
                        </div>
                    </div>
                </div>
            </x-ui.card>

            {{-- Riwayat Status Order (Screen 12) --}}
            <x-ui.card title="Riwayat Status Pesanan">
                <div class="flow-root">
                    <ul class="-mb-8">
                        @forelse ($po->statusHistory as $index => $history)
                            @php
                                $isLast = $loop->last;
                            @endphp
                            <li>
                                <div class="relative pb-8">
                                    @if (! $isLast)
                                        <span class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-outline" aria-hidden="true"></span>
                                    @endif
                                    <div class="relative flex space-x-3 items-start">
                                        <div>
                                            <span class="h-8 w-8 rounded-full bg-surface-container flex items-center justify-center ring-4 ring-surface text-body-xs font-semibold text-primary">
                                                {{ $index + 1 }}
                                            </span>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center justify-between text-body-sm">
                                                <div class="font-medium text-on-surface">
                                                    @if ($history->dari)
                                                        <span>{{ \App\Support\StatusMap::label('purchase_orders.status', $history->dari) }}</span>
                                                        <span class="text-on-surface-muted">&rarr;</span>
                                                    @endif
                                                    <span class="font-semibold text-primary">
                                                        {{ \App\Support\StatusMap::label('purchase_orders.status', $history->ke) }}
                                                    </span>
                                                </div>
                                                <time class="text-body-xs text-on-surface-muted whitespace-nowrap">
                                                    {{ \App\Support\Format::datetime($history->waktu) }}
                                                </time>
                                            </div>
                                            @if ($history->alasan)
                                                <p class="mt-1 text-body-sm text-on-surface-muted bg-surface-container-lowest p-2 rounded border border-outline">
                                                    {{ $history->alasan }}
                                                </p>
                                            @endif
                                            @if ($history->user)
                                                <p class="mt-1 text-body-xs text-on-surface-muted">
                                                    Oleh: {{ $history->user->nama }} ({{ $history->user->role }})
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </li>
                        @empty
                            <li class="text-body-sm text-on-surface-muted py-4 text-center">
                                Belum ada riwayat perubahan status.
                            </li>
                        @endforelse
                    </ul>
                </div>
            </x-ui.card>
        </div>

        {{-- Kolom Kanan: Tindakan Seller / Buyer (1 Kolom) --}}
        <div class="space-y-6">
            {{-- Tindakan untuk Seller (Screen 12) --}}
            @if ($isSeller && $statusValue === 'MENUNGGU_KONFIRMASI')
                <x-ui.card title="Tindakan Seller">
                    <div class="mb-4 rounded-md bg-info-container p-3.5 text-body-sm text-on-info-container">
                        <p class="font-semibold">&bullet; Reservasi Stok Listing:</p>
                        <p class="mt-1">
                            Konfirmasi PO akan mereservasi <strong>{{ \App\Support\Format::kg($volumePo) }}</strong> dari stok listing Anda (Stok saat ini: <strong>{{ \App\Support\Format::kg($stokListing) }}</strong>).
                        </p>
                    </div>

                    @if (! $stokCukup)
                        <div class="mb-4 rounded-md border border-error bg-error-container p-3 text-body-sm text-on-error-container">
                            <strong>Perhatian:</strong> Stok listing Anda saat ini ({{ \App\Support\Format::kg($stokListing) }}) tidak mencukupi untuk memenuhi volume PO ini ({{ \App\Support\Format::kg($volumePo) }}). Anda disarankan menolak PO ini.
                        </div>
                    @endif

                    <div class="space-y-4" x-data="{ confirming: false }">
                        {{-- Tombol Konfirmasi --}}
                        <form method="POST" action="{{ route('po.confirm', $po) }}" @submit="confirming = true">
                            @csrf
                            <x-ui.button type="submit" variant="primary" class="w-full justify-center" :disabled="!$stokCukup" x-bind:disabled="confirming || {{ !$stokCukup ? 'true' : 'false' }}">
                                <span x-show="!confirming">&check; Konfirmasi PO & Reservasi Stok</span>
                                <span x-show="confirming" style="display: none;">Memproses Konfirmasi...</span>
                            </x-ui.button>
                        </form>

                        {{-- Form Tolak PO --}}
                        <div class="border-t border-outline pt-4" x-data="{ openReject: false }">
                            <button type="button" @click="openReject = !openReject" class="text-body-sm text-error hover:underline font-medium w-full text-center">
                                Tolak Purchase Order Ini
                            </button>

                            <div x-show="openReject" class="mt-4 space-y-3" style="display: none;">
                                <form method="POST" action="{{ route('po.reject', $po) }}">
                                    @csrf
                                    <x-ui.field label="Alasan Penolakan" for="alasan_penolakan" name="alasan_penolakan" required>
                                        <x-ui.textarea id="alasan_penolakan" name="alasan_penolakan" rows="3" placeholder="Sebutkan alasan penolakan (wajib)..." required />
                                    </x-ui.field>
                                    <div class="mt-3">
                                        <button type="submit" class="w-full rounded-md border border-error bg-error/10 px-4 py-2 text-label-md font-semibold text-error hover:bg-error hover:text-white transition-colors">
                                            Konfirmasi Tolak PO
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </x-ui.card>
            @endif

            {{-- Tindakan untuk Buyer --}}
            @if ($isBuyer && in_array($statusValue, ['DRAFT', 'MENUNGGU_KONFIRMASI']))
                <x-ui.card title="Tindakan Buyer">
                    <div class="space-y-4">
                        @if ($statusValue === 'DRAFT')
                            <p class="text-body-sm text-on-surface-muted">
                                Draf Purchase Order belum dikirimkan kepada Seller. Periksa rincian sebelum mengirim.
                            </p>
                            <form method="POST" action="{{ route('po.send', $po) }}">
                                @csrf
                                <x-ui.button type="submit" variant="primary" class="w-full justify-center">
                                    Kirim ke Seller
                                </x-ui.button>
                            </form>
                        @endif

                        {{-- Batalkan PO --}}
                        <div class="border-t border-outline pt-4" x-data="{ openCancel: false }">
                            <button type="button" @click="openCancel = !openCancel" class="text-body-sm text-error hover:underline font-medium w-full text-center">
                                Batalkan Purchase Order
                            </button>

                            <div x-show="openCancel" class="mt-4 space-y-3" style="display: none;">
                                <form method="POST" action="{{ route('po.cancel', $po) }}">
                                    @csrf
                                    <x-ui.field label="Alasan Pembatalan (Opsional)" for="alasan" name="alasan">
                                        <x-ui.textarea id="alasan" name="alasan" rows="2" placeholder="Alasan pembatalan..." />
                                    </x-ui.field>
                                    <div class="mt-3">
                                        <button type="submit" class="w-full rounded-md border border-outline bg-surface-container px-4 py-2 text-label-md font-semibold text-on-surface hover:bg-error hover:text-white transition-colors" onclick="return confirm('Apakah Anda yakin ingin membatalkan PO ini?')">
                                            Konfirmasi Pembatalan
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </x-ui.card>
            @endif

            {{-- Informasi Listing Material --}}
            <x-ui.card title="Material Terkait">
                <div class="space-y-3 text-body-sm">
                    <div class="flex justify-between border-b border-outline pb-2">
                        <span class="text-on-surface-muted">Judul</span>
                        <span class="font-medium text-on-surface text-right">{{ $listing?->judul ?? '–' }}</span>
                    </div>
                    <div class="flex justify-between border-b border-outline pb-2">
                        <span class="text-on-surface-muted">Stok Tersedia Listing</span>
                        <span class="font-medium text-on-surface tabular-nums">{{ \App\Support\Format::kg($stokListing) }}</span>
                    </div>
                    <div class="flex justify-between border-b border-outline pb-2">
                        <span class="text-on-surface-muted">Lokasi Gudang</span>
                        <span class="font-medium text-on-surface">{{ $listing?->lokasi ?? '–' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-on-surface-muted">Kategori Risiko</span>
                        <x-ui.badge :variant="\App\Support\StatusMap::badge('categories.tingkat_risiko', $listing?->category?->tingkat_risiko ?? 'RENDAH')">
                            {{ \App\Support\StatusMap::label('categories.tingkat_risiko', $listing?->category?->tingkat_risiko ?? 'RENDAH') }}
                        </x-ui.badge>
                    </div>
                </div>
            </x-ui.card>
        </div>
    </div>
@endsection

@extends('layouts.app')

@section('title', 'Detail Kebutuhan Bahan Baku')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="hover:underline">Dashboard</a>
    <span class="mx-2 text-on-surface-muted" aria-hidden="true">/</span>
    <a href="{{ route('seller.demands.index') }}" class="hover:underline">Papan Kebutuhan</a>
    <span class="mx-2 text-on-surface-muted" aria-hidden="true">/</span>
    <span class="text-on-surface" aria-current="page">Detail Kebutuhan</span>
@endsection

@section('content')
    <div class="max-w-3xl">
        <div class="flex items-center justify-between mb-6">
            <x-ui.page-header
                title="Detail Permintaan Kebutuhan"
                subtitle="Informasi spesifikasi lengkap kebutuhan bahan baku yang dipublikasikan oleh Buyer."
                class="mb-0"
            />
            <x-ui.button href="{{ route('seller.demands.index') }}" variant="ghost" size="sm">
                ← Kembali ke Papan Kebutuhan
            </x-ui.button>
        </div>

        <x-ui.card class="space-y-6 mb-6">
            {{-- Info Perusahaan Buyer --}}
            <div class="flex items-start justify-between border-b border-outline pb-4">
                <div>
                    <span class="inline-block px-2 py-0.5 rounded text-xs font-medium bg-primary/10 text-primary mb-2">
                        {{ $demand->category?->nama ?? 'Bahan Baku' }}
                    </span>
                    <h3 class="text-title-lg font-bold text-on-surface">
                        {{ $demand->company?->nama ?? 'Perusahaan Buyer' }}
                    </h3>
                    <p class="text-xs text-on-surface-muted mt-1">
                        📍 {{ $demand->company?->alamat ?? 'Lokasi industri terdaftar' }}
                    </p>
                </div>
                <div class="text-right">
                    <span class="text-xs text-on-surface-muted block">Status Permintaan:</span>
                    <x-ui.badge :variant="$demand->status instanceof \App\Enums\DemandRequestStatus ? $demand->status->badge() : \App\Support\StatusMap::badge('demand_requests.status', $demand->status)">
                        {{ $demand->status instanceof \App\Enums\DemandRequestStatus ? $demand->status->label() : \App\Support\StatusMap::label('demand_requests.status', $demand->status) }}
                    </x-ui.badge>
                </div>
            </div>

            {{-- Ringkasan Kebutuhan --}}
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4 bg-surface-alt/40 rounded-sm p-4 text-center">
                <div>
                    <span class="text-xs text-on-surface-muted block mb-1">Volume Dibutuhkan</span>
                    <span class="text-base font-bold text-on-surface tabular-nums">
                        {{ number_format((float) $demand->volume_kg, 2, ',', '.') }} kg
                    </span>
                </div>
                <div>
                    <span class="text-xs text-on-surface-muted block mb-1">Frekuensi Pasokan</span>
                    <span class="text-base font-bold text-on-surface">
                        {{ $demand->frekuensi instanceof \App\Enums\DemandRequestFrequency ? $demand->frekuensi->label() : \App\Support\StatusMap::label('demand_requests.frekuensi', $demand->frekuensi) }}
                    </span>
                </div>
                <div>
                    <span class="text-xs text-on-surface-muted block mb-1">Harga Maksimal</span>
                    <span class="text-base font-bold text-on-surface tabular-nums">
                        {{ $demand->harga_maks ? \App\Support\Format::rupiah($demand->harga_maks) : 'Terbuka' }}
                    </span>
                </div>
                <div>
                    <span class="text-xs text-on-surface-muted block mb-1">Batas Waktu Berlaku</span>
                    <span class="text-base font-bold text-on-surface">
                        {{ $demand->berlaku_sampai ? \App\Support\Format::date($demand->berlaku_sampai) : 'Tidak dibatasi' }}
                    </span>
                </div>
            </div>

            {{-- Tabel Spesifikasi Minimal --}}
            <div>
                <h4 class="text-label-md font-semibold text-on-surface mb-3">
                    Spesifikasi Teknis Minimal yang Diisyaratkan
                </h4>
                @if ($demand->spesifikasi_min && is_array($demand->spesifikasi_min))
                    <div class="border border-outline rounded-sm overflow-hidden">
                        <table class="w-full text-body-sm text-left">
                            <thead class="bg-surface-alt text-on-surface-muted text-xs uppercase border-b border-outline">
                                <tr>
                                    <th scope="col" class="px-4 py-2 font-semibold">Parameter Spesifikasi</th>
                                    <th scope="col" class="px-4 py-2 font-semibold">Standar Nilai Minimal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-outline">
                                @foreach ($demand->spesifikasi_min as $param => $val)
                                    <tr>
                                        <td class="px-4 py-2.5 font-medium text-on-surface">{{ $param }}</td>
                                        <td class="px-4 py-2.5 text-on-surface font-semibold">{{ $val }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-xs text-on-surface-muted italic">Tidak ada rincian spesifikasi tambahan.</p>
                @endif
            </div>

            {{-- Informasi Kategori --}}
            @if ($demand->category)
                <div class="border-t border-outline pt-4 text-xs text-on-surface-muted flex flex-wrap gap-4">
                    <span>Tingkat Risiko: <strong class="text-on-surface">{{ $demand->category->tingkat_risiko }}</strong></span>
                    <span>Toleransi Susut: <strong class="text-on-surface">{{ $demand->category->toleransi_susut_persen }}%</strong></span>
                    @if ($demand->category->wajib_sertifikat_lab)
                        <span class="text-warning font-medium">⚠️ Wajib menyertakan sertifikat uji laboratorium</span>
                    @endif
                </div>
            @endif
        </x-ui.card>

        <div class="flex items-center justify-between">
            <x-ui.button href="{{ route('seller.demands.index') }}" variant="secondary" size="md">
                ← Kembali
            </x-ui.button>
            <x-ui.button href="{{ route('rfq.index') }}" variant="primary" size="md">
                Tawarkan Listing Saya untuk Kebutuhan Ini
            </x-ui.button>
        </div>
    </div>
@endsection

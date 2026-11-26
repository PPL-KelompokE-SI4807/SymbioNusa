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
        <x-ui.page-header
            title="Detail Permintaan Kebutuhan"
            subtitle="Informasi spesifikasi lengkap kebutuhan bahan baku yang dipublikasikan oleh Buyer.">
            <x-slot:actions>
                <x-ui.button href="{{ route('seller.demands.index') }}" variant="secondary">
                    <x-ui.icon name="arrow-left" class="size-4" />
                    Papan Kebutuhan
                </x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        <x-ui.card class="space-y-6 mb-6">
            {{-- Info Perusahaan Buyer --}}
            <div class="flex items-start justify-between border-b border-outline-hairline pb-4">
                <div>
                    <span class="mb-1 block text-label-sm font-bold text-on-surface-muted">
                        {{ $demand->category?->nama ?? 'Bahan Baku' }}
                    </span>
                    <h2 class="text-dash-title text-on-surface">
                        {{ $demand->company?->nama ?? 'Perusahaan Buyer' }}
                    </h2>
                    <p class="mt-1 flex items-center gap-1.5 text-body-sm text-on-surface-muted">
                        <x-ui.icon name="map-pin" class="size-4 shrink-0" /> {{ $demand->company?->alamat ?? 'Lokasi industri terdaftar' }}
                    </p>
                </div>
                <div class="text-right">
                    <span class="mb-1 block text-label-sm font-bold text-on-surface-muted">Status</span>
                    <x-ui.badge :variant="$demand->status instanceof \App\Enums\DemandRequestStatus ? $demand->status->badge() : \App\Support\StatusMap::badge('demand_requests.status', $demand->status)">
                        {{ $demand->status instanceof \App\Enums\DemandRequestStatus ? $demand->status->label() : \App\Support\StatusMap::label('demand_requests.status', $demand->status) }}
                    </x-ui.badge>
                </div>
            </div>

            {{-- Ringkasan Kebutuhan --}}
            <dl class="grid grid-cols-2 gap-px overflow-hidden rounded-md bg-outline-hairline shadow-[0_0_0_1px_var(--color-outline-hairline)] sm:grid-cols-4">
                <div class="bg-surface p-4">
                    <dt class="mb-1 text-label-sm font-bold text-on-surface-muted">Volume Dibutuhkan</dt>
                    <dd class="text-dash-row text-on-surface tabular-nums">
                        {{ number_format((float) $demand->volume_kg, 2, ',', '.') }} kg
                    </dd>
                </div>
                <div class="bg-surface p-4">
                    <dt class="mb-1 text-label-sm font-bold text-on-surface-muted">Frekuensi Pasokan</dt>
                    <dd class="text-dash-row text-on-surface">
                        {{ $demand->frekuensi instanceof \App\Enums\DemandRequestFrequency ? $demand->frekuensi->label() : \App\Support\StatusMap::label('demand_requests.frekuensi', $demand->frekuensi) }}
                    </dd>
                </div>
                <div class="bg-surface p-4">
                    <dt class="mb-1 text-label-sm font-bold text-on-surface-muted">Harga Maksimal</dt>
                    <dd class="text-dash-row text-on-surface tabular-nums">
                        {{ $demand->harga_maks ? \App\Support\Format::rupiah($demand->harga_maks) : 'Terbuka' }}
                    </dd>
                </div>
                <div class="bg-surface p-4">
                    <dt class="mb-1 text-label-sm font-bold text-on-surface-muted">Batas Waktu Berlaku</dt>
                    <dd class="text-dash-row text-on-surface">
                        {{ $demand->berlaku_sampai ? \App\Support\Format::date($demand->berlaku_sampai) : 'Tidak dibatasi' }}
                    </dd>
                </div>
            </dl>

            {{-- Tabel Spesifikasi Minimal --}}
            <div>
                <h3 class="mb-3 text-dash-sub text-on-surface">
                    Spesifikasi teknis minimal
                </h3>
                @if ($demand->spesifikasi_min && is_array($demand->spesifikasi_min))
                    <div>
                        <table class="w-full text-left text-data-tabular">
                            <thead class="text-body-sm text-on-surface-muted">
                                <tr>
                                    <th scope="col" class="h-10 pr-4 font-medium">Parameter</th>
                                    <th scope="col" class="h-10 font-medium">Nilai minimal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-outline-hairline border-t border-outline-hairline">
                                @foreach ($demand->spesifikasi_min as $param => $val)
                                    <tr>
                                        <td class="h-12 pr-4 text-on-surface">{{ $param }}</td>
                                        <td class="h-12 font-semibold tabular-nums text-on-surface">{{ $val }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-body-sm text-on-surface-muted">Tidak ada rincian spesifikasi tambahan.</p>
                @endif
            </div>

            {{-- Informasi Kategori --}}
            @if ($demand->category)
                <div class="flex flex-wrap gap-x-4 gap-y-1 border-t border-outline-hairline pt-4 text-body-sm text-on-surface-muted">
                    <span>Tingkat Risiko: <strong class="text-on-surface">{{ $demand->category->tingkat_risiko }}</strong></span>
                    <span>Toleransi Susut: <strong class="text-on-surface">{{ $demand->category->toleransi_susut_persen }}%</strong></span>
                    @if ($demand->category->wajib_sertifikat_lab)
                        <span class="inline-flex items-center gap-1.5 font-semibold text-warning"><x-ui.icon name="alert" class="size-4 shrink-0" /> Wajib menyertakan sertifikat uji laboratorium</span>
                    @endif
                </div>
            @endif
        </x-ui.card>

        <div class="flex flex-wrap items-center justify-between gap-3">
            <x-ui.button href="{{ route('seller.demands.index') }}" variant="secondary">
                <x-ui.icon name="arrow-left" class="size-4" />
                Kembali
            </x-ui.button>
            <x-ui.button href="{{ route('rfq.index') }}">
                Tawarkan Listing Saya
            </x-ui.button>
        </div>
    </div>
@endsection

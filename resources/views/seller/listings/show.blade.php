@extends('layouts.app')

@section('title', $listing->judul)

@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="hover:underline">Dashboard</a>
    <span class="mx-2 text-on-surface-muted" aria-hidden="true">/</span>
    <a href="{{ route('seller.listings.index') }}" class="hover:underline">Listing Saya</a>
    <span class="mx-2 text-on-surface-muted" aria-hidden="true">/</span>
    <span class="text-on-surface" aria-current="page">{{ $listing->judul }}</span>
@endsection

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-headline-sm font-bold text-on-surface">{{ $listing->judul }}</h1>
                <x-ui.badge :variant="\App\Support\StatusMap::badge('listings.status', $listing->status)">
                    {{ \App\Support\StatusMap::label('listings.status', $listing->status) }}
                </x-ui.badge>
            </div>
            <p class="text-body-sm text-on-surface-muted mt-1">
                Kategori: {{ $listing->category?->nama ?? '–' }} • Dibuat oleh {{ $listing->company?->nama ?? 'Perusahaan' }}
            </p>
        </div>
        <div class="flex items-center gap-2">
            <x-ui.button href="{{ route('seller.listings.index') }}" variant="secondary">
                ← Kembali
            </x-ui.button>
            @if ($listing->status !== 'DIARSIPKAN')
                <x-ui.button href="{{ route('seller.listings.edit', $listing) }}" variant="secondary">
                    Ubah Listing
                </x-ui.button>
            @endif
        </div>
    </div>

    @if (session('status'))
        <x-ui.alert variant="success" class="mb-6">
            {{ session('status') }}
        </x-ui.alert>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Kolom Kiri: Rincian Utama & Spesifikasi --}}
        <div class="lg:col-span-2 space-y-6">
            <x-ui.card>
                <h3 class="text-title-sm text-on-surface font-semibold mb-4 border-b border-border pb-2">
                    Rincian Kuantitas & Nilai Penawaran
                </h3>

                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-body-sm">
                    <div>
                        <dt class="text-label-sm text-on-surface-muted">Sisa Stok Tersedia</dt>
                        <dd class="text-title-sm font-semibold text-primary mt-0.5">
                            {{ \App\Support\Format::number($listing->stok_kg) }} kg
                        </dd>
                    </div>
                    <div>
                        <dt class="text-label-sm text-on-surface-muted">Volume Awal Terdaftar</dt>
                        <dd class="text-title-sm font-semibold text-on-surface mt-0.5">
                            {{ \App\Support\Format::number($listing->volume_kg) }} kg
                        </dd>
                    </div>
                    <div>
                        <dt class="text-label-sm text-on-surface-muted">Harga Penawaran</dt>
                        <dd class="text-title-sm font-semibold text-on-surface mt-0.5">
                            {{ \App\Support\Format::rupiah($listing->harga_per_kg) }} <span class="text-body-xs font-normal text-on-surface-muted">/ kg</span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-label-sm text-on-surface-muted">Estimasi Nilai Total</dt>
                        <dd class="text-title-sm font-semibold text-on-surface mt-0.5">
                            {{ \App\Support\Format::rupiah($listing->stok_kg * $listing->harga_per_kg) }}
                        </dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-label-sm text-on-surface-muted">Lokasi Asal / Gudang</dt>
                        <dd class="text-body-sm font-medium text-on-surface mt-0.5">
                            {{ $listing->lokasi ?? '–' }}
                        </dd>
                    </div>
                </dl>
            </x-ui.card>

            <x-ui.card>
                <h3 class="text-title-sm text-on-surface font-semibold mb-4 border-b border-border pb-2">
                    Spesifikasi Teknis Material
                </h3>

                @if (!empty($listing->spesifikasi) && is_array($listing->spesifikasi))
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-body-sm">
                        @foreach ($listing->spesifikasi as $key => $val)
                            <div class="p-3 bg-surface-muted rounded-md">
                                <dt class="text-label-sm text-on-surface-muted">{{ $key }}</dt>
                                <dd class="text-body-sm font-semibold text-on-surface mt-0.5">{{ $val }}</dd>
                            </div>
                        @endforeach
                    </dl>
                @else
                    <p class="text-body-sm text-on-surface-muted italic">Tidak ada spesifikasi khusus tercatat.</p>
                @endif
            </x-ui.card>

            <x-ui.card>
                <h3 class="text-title-sm text-on-surface font-semibold mb-4 border-b border-border pb-2">
                    Informasi Dampak Lingkungan
                </h3>

                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-body-sm">
                    <div>
                        <dt class="text-label-sm text-on-surface-muted">Metode Pembuangan Sebelumnya</dt>
                        <dd class="text-body-sm font-medium text-on-surface mt-0.5">
                            {{ $listing->metode_buang_sebelumnya ?? '–' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-label-sm text-on-surface-muted">Biaya Pembuangan Sebelumnya</dt>
                        <dd class="text-body-sm font-medium text-on-surface mt-0.5">
                            {{ $listing->biaya_buang_per_kg ? \App\Support\Format::rupiah($listing->biaya_buang_per_kg) . ' / kg' : '–' }}
                        </dd>
                    </div>
                </dl>
            </x-ui.card>
        </div>

        {{-- Kolom Kanan: Lampiran Bukti Material --}}
        <div class="space-y-6">
            <x-ui.card>
                <h3 class="text-title-sm text-on-surface font-semibold mb-4 border-b border-border pb-2">
                    Bukti & Dokumen Kepatuhan
                </h3>

                <div class="space-y-3">
                    @forelse ($listing->activeAttachments as $attachment)
                        <div class="p-3 border border-border rounded-md">
                            <div class="flex items-center justify-between">
                                <span class="font-semibold text-body-sm text-on-surface">
                                    {{ match ($attachment->jenis) {
                                        'SURAT_NON_B3' => 'Surat Pernyataan Non-B3',
                                        'FOTO_SAMPEL' => 'Foto Sampel Material',
                                        'SERTIFIKAT_LAB' => 'Sertifikat Uji Lab',
                                        default => $attachment->jenis
                                    } }}
                                </span>
                                <x-ui.badge variant="success" size="sm">Aktif</x-ui.badge>
                            </div>
                            @if ($attachment->tanggal_pengambilan)
                                <div class="text-body-xs text-on-surface-muted mt-1">
                                    Pengambilan: {{ \App\Support\Format::date($attachment->tanggal_pengambilan) }}
                                </div>
                            @endif
                            <div class="mt-2 text-right">
                                <a
                                    href="{{ asset('storage/' . $attachment->file_url) }}"
                                    target="_blank"
                                    class="text-body-xs text-primary hover:underline font-medium"
                                >
                                    Buka Dokumen ↗
                                </a>
                            </div>
                        </div>
                    @empty
                        <p class="text-body-sm text-on-surface-muted text-center py-4">
                            Belum ada dokumen bukti yang terlampir.
                        </p>
                    @endforelse
                </div>
            </x-ui.card>
        </div>
    </div>
@endsection

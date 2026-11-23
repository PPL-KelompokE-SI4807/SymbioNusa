@extends('layouts.app')

@section('title', $listing->judul . ' - Katalog Material')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="hover:underline">Dashboard</a>
    <span class="mx-2 text-on-surface-muted" aria-hidden="true">/</span>
    <a href="{{ route('catalog.index') }}" class="hover:underline">Katalog Material</a>
    <span class="mx-2 text-on-surface-muted" aria-hidden="true">/</span>
    <span class="text-on-surface" aria-current="page">{{ $listing->judul }}</span>
@endsection

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-headline-sm font-bold text-on-surface">{{ $listing->judul }}</h1>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-success/10 text-success">
                    Tersedia
                </span>
            </div>
            <p class="text-body-sm text-on-surface-muted mt-1">
                Kategori: {{ $listing->category?->nama ?? '–' }} • Disediakan oleh <strong>{{ $listing->company?->nama ?? 'Perusahaan Mitra' }}</strong>
            </p>
        </div>
        <div class="flex items-center gap-2">
            <x-ui.button href="{{ route('catalog.index') }}" variant="ghost">
                ← Kembali ke Katalog
            </x-ui.button>
            @if (auth()->user()?->role === 'BUYER')
                <x-ui.button href="{{ route('rfq.create', ['listing' => $listing->id_listings]) }}" variant="primary">
                    Ajukan RFQ / Penawaran
                </x-ui.button>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <x-ui.card>
                <h3 class="text-title-sm text-on-surface font-semibold mb-4 border-b border-border pb-2">
                    Informasi Ketersediaan & Penawaran
                </h3>

                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-body-sm">
                    <div>
                        <dt class="text-label-sm text-on-surface-muted">Stok Siap Angkut</dt>
                        <dd class="text-title-sm font-semibold text-primary mt-0.5">
                            {{ \App\Support\Format::number($listing->stok_kg) }} kg
                        </dd>
                    </div>
                    <div>
                        <dt class="text-label-sm text-on-surface-muted">Harga Penawaran</dt>
                        <dd class="text-title-sm font-semibold text-on-surface mt-0.5">
                            {{ \App\Support\Format::rupiah($listing->harga_per_kg) }} <span class="text-body-xs font-normal text-on-surface-muted">/ kg</span>
                        </dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-label-sm text-on-surface-muted">Lokasi Gudang Penyedia</dt>
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
                    <p class="text-body-sm text-on-surface-muted italic">Spesifikasi umum standar kategori.</p>
                @endif
            </x-ui.card>
        </div>

        <div class="space-y-6">
            <x-ui.card>
                <h3 class="text-title-sm text-on-surface font-semibold mb-4 border-b border-border pb-2">
                    Kepatuhan & Dokumen Non-B3
                </h3>

                <div class="space-y-3">
                    @forelse ($listing->attachments as $attachment)
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
                                <x-ui.badge variant="success" size="sm">Terverifikasi</x-ui.badge>
                            </div>
                            @if ($attachment->tanggal_pengambilan)
                                <div class="text-body-xs text-on-surface-muted mt-1">
                                    Sampel: {{ \App\Support\Format::date($attachment->tanggal_pengambilan) }}
                                </div>
                            @endif
                            <div class="mt-2 text-right">
                                <a
                                    href="{{ asset('storage/' . $attachment->file_url) }}"
                                    target="_blank"
                                    class="text-body-xs text-primary hover:underline font-medium"
                                >
                                    Lihat Lampiran ↗
                                </a>
                            </div>
                        </div>
                    @empty
                        <p class="text-body-sm text-on-surface-muted text-center py-4">
                            Tidak ada dokumen khusus.
                        </p>
                    @endforelse
                </div>
            </x-ui.card>
        </div>
    </div>
@endsection

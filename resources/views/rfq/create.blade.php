@extends('layouts.app')

@section('title', 'Ajukan RFQ')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="hover:underline">Dashboard</a>
    <span class="mx-2 text-on-surface-muted" aria-hidden="true">/</span>
    <a href="{{ route('rfq.index') }}" class="hover:underline">RFQ & Negosiasi</a>
    <span class="mx-2 text-on-surface-muted" aria-hidden="true">/</span>
    <span class="text-on-surface" aria-current="page">Ajukan RFQ</span>
@endsection

@section('content')
    <x-ui.page-header title="Ajukan RFQ Baru" subtitle="Kirim penawaran volume dan harga material sisa produksi kepada Seller." />

    @if ($errors->any())
        <x-ui.alert variant="danger" class="mb-6">
            Terdapat kesalahan pada isian formulir. Mohon periksa kembali input Anda di bawah ini.
        </x-ui.alert>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Formulir Utama --}}
        <div class="lg:col-span-2">
            <x-ui.card title="Formulir Penawaran Material">
                <form method="POST" action="{{ route('rfq.store') }}" class="space-y-6">
                    @csrf

                    <div>
                        <x-ui.field label="Pilih Listing Material" for="listings_id_listings" name="listings_id_listings" helper="Pilih material sisa produksi non-B3 dari katalog." required>
                            <x-ui.select id="listings_id_listings" name="listings_id_listings" onchange="window.location.href = '{{ route('rfq.create') }}?listing_id=' + this.value">
                                <option value="">-- Pilih Material dari Katalog --</option>
                                @foreach ($listings as $lst)
                                    <option value="{{ $lst->id_listings }}" @selected(old('listings_id_listings', request('listing_id')) == $lst->id_listings)>
                                        {{ $lst->judul }} — {{ $lst->company?->nama ?? 'Seller' }} (Stok: {{ \App\Support\Format::number($lst->stok_kg) }} kg, Harga: {{ \App\Support\Format::rupiah($lst->harga_per_kg) }}/kg)
                                    </option>
                                @endforeach
                            </x-ui.select>
                        </x-ui.field>
                    </div>

                    <div class="border-t border-outline-hairline pt-6">
                        <h3 class="mb-4 text-dash-sub text-on-surface">Rincian Penawaran</h3>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <x-ui.field label="Volume yang Diajukan (kg)" for="volume_kg" name="volume_kg" helper="Tidak boleh melebihi stok tersedia." required>
                                    <x-ui.input id="volume_kg" name="volume_kg" type="number" step="0.01" min="1" :value="old('volume_kg')" placeholder="Contoh: 1500" required />
                                </x-ui.field>
                            </div>

                            <div>
                                <x-ui.field label="Harga Diajukan (Rp/kg)" for="harga_per_kg" name="harga_per_kg" helper="Tawaran harga satuan dalam Rupiah." required>
                                    <x-ui.input id="harga_per_kg" name="harga_per_kg" type="number" step="0.01" min="1" :value="old('harga_per_kg')" placeholder="Contoh: 3500" required />
                                </x-ui.field>
                            </div>
                        </div>

                        <div class="mt-4">
                            <x-ui.field label="Tenggat Negosiasi (Hari)" for="tenggat_hari" name="tenggat_hari" helper="Batas waktu Seller merespons (default 3 hari, maksimal 7 hari).">
                                <x-ui.select id="tenggat_hari" name="tenggat_hari">
                                    <option value="1" @selected(old('tenggat_hari') == 1)>1 Hari</option>
                                    <option value="2" @selected(old('tenggat_hari') == 2)>2 Hari</option>
                                    <option value="3" @selected(old('tenggat_hari', 3) == 3)>3 Hari (Direkomendasikan)</option>
                                    <option value="5" @selected(old('tenggat_hari') == 5)>5 Hari</option>
                                    <option value="7" @selected(old('tenggat_hari') == 7)>7 Hari (Maksimal)</option>
                                </x-ui.select>
                            </x-ui.field>
                        </div>

                        <div class="mt-4">
                            <x-ui.field label="Catatan Penawaran (Opsional)" for="catatan" name="catatan" helper="Kebutuhan spesifikasi khusus atau rencana jadwal pengambilan.">
                                <x-ui.textarea id="catatan" name="catatan" rows="3" placeholder="Tambahkan catatan untuk penjual...">{{ old('catatan') }}</x-ui.textarea>
                            </x-ui.field>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 border-t border-outline-hairline pt-6">
                        <x-ui.button href="{{ route('rfq.index') }}" variant="secondary">
                            Batal
                        </x-ui.button>
                        <x-ui.button type="submit" variant="primary">
                            Kirim RFQ
                        </x-ui.button>
                    </div>
                </form>
            </x-ui.card>
        </div>

        {{-- Ringkasan Material (Preview Card) --}}
        <div>
            <x-ui.card title="Ringkasan Listing Terpilih">
                @if ($selectedListing)
                    <div class="space-y-4 text-body-sm">
                        <div>
                            <span class="block text-label-sm text-on-surface-muted">Judul Material</span>
                            <span class="font-medium text-on-surface">{{ $selectedListing->judul }}</span>
                        </div>
                        <div>
                            <span class="block text-label-sm text-on-surface-muted">Perusahaan Penjual</span>
                            <span class="font-medium text-on-surface">{{ $selectedListing->company?->nama ?? '–' }}</span>
                        </div>
                        <div>
                            <span class="block text-label-sm text-on-surface-muted">Kategori Material</span>
                            <span class="font-medium text-on-surface">{{ $selectedListing->category?->nama ?? '–' }}</span>
                        </div>
                        <div class="grid grid-cols-2 gap-2 border-t border-outline-hairline pt-3">
                            <div>
                                <span class="block text-label-sm text-on-surface-muted">Stok Tersedia</span>
                                <span class="font-semibold text-primary tabular-nums">{{ \App\Support\Format::number($selectedListing->stok_kg) }} kg</span>
                            </div>
                            <div>
                                <span class="block text-label-sm text-on-surface-muted">Harga Listing</span>
                                <span class="font-semibold text-on-surface tabular-nums">{{ \App\Support\Format::rupiah($selectedListing->harga_per_kg) }}/kg</span>
                            </div>
                        </div>
                        <div class="border-t border-outline-hairline pt-3">
                            <span class="block text-label-sm text-on-surface-muted">Lokasi</span>
                            <span class="text-on-surface">{{ $selectedListing->lokasi ?? '–' }}</span>
                        </div>
                        @if ($selectedListing->spesifikasi && is_array($selectedListing->spesifikasi))
                            <div class="border-t border-outline-hairline pt-3">
                                <span class="block text-label-sm text-on-surface-muted mb-1">Spesifikasi</span>
                                <ul class="list-disc pl-4 space-y-0.5 text-body-sm text-on-surface-muted">
                                    @foreach ($selectedListing->spesifikasi as $key => $val)
                                        <li><strong class="text-on-surface">{{ $key }}:</strong> {{ is_array($val) ? json_encode($val) : $val }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="py-8 text-center text-on-surface-muted">
                        <p class="text-body-sm">Pilih listing material pada dropdown untuk melihat rincian stok, harga patokan, dan spesifikasinya.</p>
                    </div>
                @endif
            </x-ui.card>
        </div>
    </div>
@endsection

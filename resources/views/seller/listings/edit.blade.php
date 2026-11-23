@extends('layouts.app')

@section('title', 'Ubah Listing: ' . $listing->judul)

@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="hover:underline">Dashboard</a>
    <span class="mx-2 text-on-surface-muted" aria-hidden="true">/</span>
    <a href="{{ route('seller.listings.index') }}" class="hover:underline">Listing Saya</a>
    <span class="mx-2 text-on-surface-muted" aria-hidden="true">/</span>
    <span class="text-on-surface" aria-current="page">Ubah Listing</span>
@endsection

@section('content')
    <x-ui.page-header
        title="Ubah Listing Material"
        subtitle="Perbarui data stok, harga, dan dokumen kelengkapan sisa produksi industri Anda."
    />

    @if ($errors->any())
        <x-ui.alert variant="danger" class="mb-6">
            <div class="font-semibold mb-1">Periksa kembali isian formulir:</div>
            <ul class="list-disc list-inside text-body-sm space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-ui.alert>
    @endif

    <form
        method="POST"
        action="{{ route('seller.listings.update', $listing) }}"
        enctype="multipart/form-data"
        x-data="{
            categories: {{ \Illuminate\Support\Js::from($categories) }},
            selectedId: '{{ old('categories_id_categories', $listing->categories_id_categories) }}',
            selectedCategory: null,
            init() {
                if (this.selectedId) {
                    this.selectedCategory = this.categories.find(c => String(c.id_categories) === String(this.selectedId)) || null;
                }
            },
            updateCategory(id) {
                this.selectedId = id;
                this.selectedCategory = this.categories.find(c => String(c.id_categories) === String(id)) || null;
            }
        }"
        class="space-y-6"
    >
        @csrf
        @method('PUT')

        {{-- Section 1: Identitas Material --}}
        <x-ui.card>
            <h3 class="text-title-sm text-on-surface font-semibold mb-4 border-b border-border pb-2">
                1. Identitas & Kategori Material
            </h3>

            <div class="space-y-4">
                <div>
                    <label for="judul" class="block text-label-sm text-on-surface mb-1">
                        Judul Penawaran Material <span class="text-danger">*</span>
                    </label>
                    <x-ui.input
                        id="judul"
                        name="judul"
                        :value="old('judul', $listing->judul)"
                        required
                    />
                </div>

                <div>
                    <label for="categories_id_categories" class="block text-label-sm text-on-surface mb-1">
                        Kategori Material <span class="text-danger">*</span>
                    </label>
                    <x-ui.select
                        id="categories_id_categories"
                        name="categories_id_categories"
                        x-model="selectedId"
                        @change="updateCategory($event.target.value)"
                        required
                    >
                        @foreach ($categories as $cat)
                            <option
                                value="{{ $cat->id_categories }}"
                                {{ old('categories_id_categories', $listing->categories_id_categories) == $cat->id_categories ? 'selected' : '' }}
                                {{ $cat->is_b3_prohibited ? 'class=text-danger' : '' }}
                            >
                                {{ $cat->nama }} {{ $cat->is_b3_prohibited ? '— [DILARANG / LIMBAH B3]' : '' }}
                            </option>
                        @endforeach
                    </x-ui.select>
                </div>
            </div>
        </x-ui.card>

        {{-- Section 2: Stok, Harga, Lokasi --}}
        <x-ui.card>
            <h3 class="text-title-sm text-on-surface font-semibold mb-4 border-b border-border pb-2">
                2. Volume Stok, Harga & Lokasi
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div>
                    <label for="volume_kg" class="block text-label-sm text-on-surface mb-1">
                        Volume Total (kg) <span class="text-danger">*</span>
                    </label>
                    <x-ui.input
                        id="volume_kg"
                        name="volume_kg"
                        type="number"
                        step="0.01"
                        :value="old('volume_kg', $listing->volume_kg)"
                        required
                    />
                </div>

                <div>
                    <label for="stok_kg" class="block text-label-sm text-on-surface mb-1">
                        Sisa Stok Tersedia (kg) <span class="text-danger">*</span>
                    </label>
                    <x-ui.input
                        id="stok_kg"
                        name="stok_kg"
                        type="number"
                        step="0.01"
                        :value="old('stok_kg', $listing->stok_kg)"
                        required
                    />
                </div>

                <div>
                    <label for="harga_per_kg" class="block text-label-sm text-on-surface mb-1">
                        Harga (Rp/kg) <span class="text-danger">*</span>
                    </label>
                    <x-ui.input
                        id="harga_per_kg"
                        name="harga_per_kg"
                        type="number"
                        step="0.01"
                        :value="old('harga_per_kg', $listing->harga_per_kg)"
                        required
                    />
                </div>

                <div>
                    <label for="lokasi" class="block text-label-sm text-on-surface mb-1">
                        Lokasi Gudang / Asal Material <span class="text-danger">*</span>
                    </label>
                    <x-ui.input
                        id="lokasi"
                        name="lokasi"
                        :value="old('lokasi', $listing->lokasi)"
                        required
                    />
                </div>

                <div>
                    <label for="metode_buang_sebelumnya" class="block text-label-sm text-on-surface mb-1">
                        Metode Pembuangan Sebelumnya
                    </label>
                    <x-ui.input
                        id="metode_buang_sebelumnya"
                        name="metode_buang_sebelumnya"
                        :value="old('metode_buang_sebelumnya', $listing->metode_buang_sebelumnya)"
                    />
                </div>

                <div>
                    <label for="biaya_buang_per_kg" class="block text-label-sm text-on-surface mb-1">
                        Biaya Pembuangan Sebelumnya (Rp/kg)
                    </label>
                    <x-ui.input
                        id="biaya_buang_per_kg"
                        name="biaya_buang_per_kg"
                        type="number"
                        step="0.01"
                        :value="old('biaya_buang_per_kg', $listing->biaya_buang_per_kg)"
                    />
                </div>
            </div>
        </x-ui.card>

        {{-- Section 3: Bukti & Dokumen Lampiran --}}
        <x-ui.card>
            <h3 class="text-title-sm text-on-surface font-semibold mb-4 border-b border-border pb-2">
                3. Bukti Material & Lampiran Non-B3
            </h3>

            @if ($underInspection)
                <x-ui.alert variant="warning" class="mb-4">
                    Listing ini sedang dalam proses inspeksi. Lampiran bukti tidak dapat diganti sampai inspeksi selesai.
                </x-ui.alert>
            @endif

            @if ($listing->activeAttachments->isNotEmpty())
                <div class="mb-4 p-3 bg-surface-muted rounded-md space-y-2">
                    <div class="text-label-sm font-semibold text-on-surface">Lampiran yang sudah tersimpan:</div>
                    <ul class="text-body-sm space-y-1">
                        @foreach ($listing->activeAttachments as $att)
                            <li class="flex items-center justify-between text-body-xs">
                                <span>
                                    <strong>{{ $att->jenis }}</strong> — {{ basename($att->file_url) }}
                                    @if ($att->tanggal_pengambilan)
                                        ({{ \App\Support\Format::date($att->tanggal_pengambilan) }})
                                    @endif
                                </span>
                                <span class="text-primary font-medium">Tersimpan</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="space-y-4">
                <div>
                    <label for="surat_non_b3" class="block text-label-sm text-on-surface mb-1">
                        Ganti / Unggah Surat Pernyataan Non-B3 (PDF / JPG / PNG, maks 5 MB)
                    </label>
                    <input
                        type="file"
                        id="surat_non_b3"
                        name="surat_non_b3"
                        accept=".pdf,.jpg,.jpeg,.png"
                        class="block w-full text-body-sm file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-label-sm file:font-semibold file:bg-surface-muted file:text-on-surface hover:file:bg-border"
                    />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="foto_sampel" class="block text-label-sm text-on-surface mb-1">
                            Ganti / Unggah Foto Sampel (JPG / PNG, maks 5 MB)
                        </label>
                        <input
                            type="file"
                            id="foto_sampel"
                            name="foto_sampel"
                            accept=".jpg,.jpeg,.png"
                            class="block w-full text-body-sm file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-label-sm file:font-semibold file:bg-surface-muted file:text-on-surface hover:file:bg-border"
                        />
                    </div>
                    <div>
                        <label for="tanggal_pengambilan_sampel" class="block text-label-sm text-on-surface mb-1">
                            Tanggal Pengambilan Sampel
                        </label>
                        <x-ui.input
                            id="tanggal_pengambilan_sampel"
                            name="tanggal_pengambilan_sampel"
                            type="date"
                            :value="old('tanggal_pengambilan_sampel', now()->toDateString())"
                        />
                    </div>
                </div>

                <div>
                    <label for="sertifikat_lab" class="block text-label-sm text-on-surface mb-1">
                        Ganti / Unggah Sertifikat Uji Lab (PDF / JPG / PNG, maks 5 MB)
                    </label>
                    <input
                        type="file"
                        id="sertifikat_lab"
                        name="sertifikat_lab"
                        accept=".pdf,.jpg,.jpeg,.png"
                        class="block w-full text-body-sm file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-label-sm file:font-semibold file:bg-surface-muted file:text-on-surface hover:file:bg-border"
                    />
                </div>
            </div>
        </x-ui.card>

        {{-- Aksi Formulir --}}
        <div class="flex flex-col-reverse items-start gap-4 pt-2 sm:flex-row sm:items-center sm:justify-between">
            <x-ui.button href="{{ route('seller.listings.index') }}" variant="secondary">
                Batal
            </x-ui.button>
            <div class="flex flex-wrap items-center gap-3">
                <x-ui.button
                    type="submit"
                    name="status"
                    value="DRAF"
                    variant="secondary"
                >
                    Simpan Perubahan
                </x-ui.button>
                <x-ui.button
                    type="submit"
                    name="status"
                    value="PUBLISHED"
                    variant="primary"
                >
                    Simpan & Publikasikan
                </x-ui.button>
            </div>
        </div>
    </form>
@endsection

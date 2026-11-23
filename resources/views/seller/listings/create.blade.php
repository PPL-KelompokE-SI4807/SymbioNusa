@extends('layouts.app')

@section('title', 'Buat Listing Sisa Produksi')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="hover:underline">Dashboard</a>
    <span class="mx-2 text-on-surface-muted" aria-hidden="true">/</span>
    <a href="{{ route('seller.listings.index') }}" class="hover:underline">Listing Saya</a>
    <span class="mx-2 text-on-surface-muted" aria-hidden="true">/</span>
    <span class="text-on-surface" aria-current="page">Buat Listing</span>
@endsection

@section('content')
    <x-ui.page-header
        title="Buat Listing Sisa Produksi"
        subtitle="Tawarkan sisa material industri non-B3 Anda untuk diserap oleh industri mitra yang membutuhkan."
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
        action="{{ route('seller.listings.store') }}"
        enctype="multipart/form-data"
        x-data="{
            categories: {{ \Illuminate\Support\Js::from($categories) }},
            selectedId: '{{ old('categories_id_categories', '') }}',
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

        {{-- Section 1: Informasi Pokok Material --}}
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
                        placeholder="Contoh: Sisa Potongan Kain Perca Katun 100% Non-B3"
                        :value="old('judul')"
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
                        <option value="">Pilih Kategori Material</option>
                        @foreach ($categories as $cat)
                            <option
                                value="{{ $cat->id_categories }}"
                                {{ old('categories_id_categories') == $cat->id_categories ? 'selected' : '' }}
                                {{ $cat->is_b3_prohibited ? 'class=text-danger' : '' }}
                            >
                                {{ $cat->nama }} {{ $cat->is_b3_prohibited ? '— [DILARANG / LIMBAH B3]' : '' }}
                            </option>
                        @endforeach
                    </x-ui.select>
                </div>

                {{-- Alert Kategori B3 --}}
                <template x-if="selectedCategory && selectedCategory.is_b3_prohibited == 1">
                    <x-ui.alert variant="danger" class="mt-3">
                        <div class="font-bold">Kategori ini dilarang pada platform SymbioNusa!</div>
                        <div>Material berkarakteristik B3 tidak dapat diperjualbelikan atau dibuatkan listing sesuai peraturan perundangan lingkungan hidup.</div>
                    </x-ui.alert>
                </template>

                {{-- Alert Wajib Sertifikat Lab --}}
                <template x-if="selectedCategory && selectedCategory.wajib_sertifikat_lab == 1 && selectedCategory.is_b3_prohibited == 0">
                    <x-ui.alert variant="info" class="mt-3">
                        <div class="font-semibold">Kategori Wajib Sertifikat Uji Lab:</div>
                        <div>Kategori ini mewajibkan pengunggahan berkas hasil uji laboratorium terakreditasi saat publikasi.</div>
                    </x-ui.alert>
                </template>
            </div>
        </x-ui.card>

        {{-- Section 2: Spesifikasi Material (Dinamis dari Templat Kategori) --}}
        <x-ui.card>
            <h3 class="text-title-sm text-on-surface font-semibold mb-4 border-b border-border pb-2">
                2. Spesifikasi Teknis Material
            </h3>

            <div class="space-y-4">
                {{-- Dynamic Schema Fields --}}
                <template x-if="selectedCategory && selectedCategory.spec_schema && Array.isArray(selectedCategory.spec_schema) && selectedCategory.spec_schema.length > 0">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <template x-for="(spec, index) in selectedCategory.spec_schema" :key="index">
                            <div>
                                <label class="block text-label-sm text-on-surface mb-1">
                                    <span x-text="spec.nama"></span>
                                    <span x-show="spec.satuan" class="text-on-surface-muted" x-text="' (' + spec.satuan + ')'"></span>
                                    <span x-show="spec.wajib" class="text-danger">*</span>
                                </label>
                                <input
                                    type="text"
                                    :name="'spesifikasi[' + spec.nama + ']'"
                                    class="w-full rounded-md border border-border bg-surface px-3 py-2 text-body-sm focus:border-primary focus:outline-none"
                                    :placeholder="'Masukkan ' + spec.nama"
                                    :required="spec.wajib"
                                />
                            </div>
                        </template>
                    </div>
                </template>

                <template x-if="!selectedCategory || !selectedCategory.spec_schema || selectedCategory.spec_schema.length === 0">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="spec-kadar-air" class="block text-label-sm text-on-surface mb-1">Kadar Air (%)</label>
                            <x-ui.input
                                id="spec-kadar-air"
                                name="spesifikasi[Kadar Air]"
                                placeholder="Contoh: 12"
                                :value="old('spesifikasi.Kadar Air')"
                            />
                        </div>
                        <div>
                            <label for="spec-kebersihan" class="block text-label-sm text-on-surface mb-1">Tingkat Kebersihan</label>
                            <x-ui.input
                                id="spec-kebersihan"
                                name="spesifikasi[Tingkat Kebersihan]"
                                placeholder="Contoh: Bersih / Kering"
                                :value="old('spesifikasi.Tingkat Kebersihan')"
                            />
                        </div>
                    </div>
                </template>
            </div>
        </x-ui.card>

        {{-- Section 3: Kuantitas, Harga, dan Lokasi --}}
        <x-ui.card>
            <h3 class="text-title-sm text-on-surface font-semibold mb-4 border-b border-border pb-2">
                3. Volume Stok, Harga & Lokasi
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div>
                    <label for="volume_kg" class="block text-label-sm text-on-surface mb-1">
                        Volume Total Tersedia (kg) <span class="text-danger">*</span>
                    </label>
                    <x-ui.input
                        id="volume_kg"
                        name="volume_kg"
                        type="number"
                        step="0.01"
                        placeholder="Contoh: 5000"
                        :value="old('volume_kg')"
                        required
                    />
                </div>

                <div>
                    <label for="harga_per_kg" class="block text-label-sm text-on-surface mb-1">
                        Harga Penawaran (Rp/kg) <span class="text-danger">*</span>
                    </label>
                    <x-ui.input
                        id="harga_per_kg"
                        name="harga_per_kg"
                        type="number"
                        step="0.01"
                        placeholder="Contoh: 3500"
                        :value="old('harga_per_kg')"
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
                        placeholder="Contoh: Rancaekek, Kab. Bandung"
                        :value="old('lokasi')"
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
                        placeholder="Contoh: Landfill / TPA Sarimukti"
                        :value="old('metode_buang_sebelumnya')"
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
                        placeholder="Contoh: 400"
                        :value="old('biaya_buang_per_kg')"
                    />
                </div>
            </div>
        </x-ui.card>

        {{-- Section 4: Bukti Kepatuhan & Non-B3 --}}
        <x-ui.card>
            <h3 class="text-title-sm text-on-surface font-semibold mb-4 border-b border-border pb-2">
                4. Bukti Material & Lampiran Non-B3
            </h3>

            <div class="space-y-4">
                <div>
                    <label for="surat_non_b3" class="block text-label-sm text-on-surface mb-1">
                        Surat Pernyataan Non-B3 (PDF / JPG / PNG, maks 5 MB) <span class="text-danger">*</span>
                    </label>
                    <input
                        type="file"
                        id="surat_non_b3"
                        name="surat_non_b3"
                        accept=".pdf,.jpg,.jpeg,.png"
                        class="block w-full text-body-sm file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-label-sm file:font-semibold file:bg-primary file:text-white hover:file:bg-primary/90"
                    />
                    <p class="text-body-xs text-on-surface-muted mt-1">
                        Wajib untuk mempublikasikan listing. Surat bermaterai resmi dari perusahaan penghasil sisa produksi.
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="foto_sampel" class="block text-label-sm text-on-surface mb-1">
                            Foto Sampel Material (JPG / PNG, maks 5 MB)
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
                        Sertifikat Uji Laboratorium (PDF / JPG / PNG, maks 5 MB)
                    </label>
                    <input
                        type="file"
                        id="sertifikat_lab"
                        name="sertifikat_lab"
                        accept=".pdf,.jpg,.jpeg,.png"
                        class="block w-full text-body-sm file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-label-sm file:font-semibold file:bg-surface-muted file:text-on-surface hover:file:bg-border"
                    />
                    <p class="text-body-xs text-on-surface-muted mt-1">
                        Wajib jika kategori material yang dipilih mensyaratkan sertifikat uji lab terakreditasi.
                    </p>
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
                    ::disabled="selectedCategory && selectedCategory.is_b3_prohibited == 1"
                >
                    Simpan Draf
                </x-ui.button>
                <x-ui.button
                    type="submit"
                    name="status"
                    value="PUBLISHED"
                    variant="primary"
                    ::disabled="selectedCategory && selectedCategory.is_b3_prohibited == 1"
                >
                    Publikasikan Listing
                </x-ui.button>
            </div>
        </div>
    </form>
@endsection

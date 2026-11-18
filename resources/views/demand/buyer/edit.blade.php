@extends('layouts.app')

@section('title', 'Ubah Kebutuhan Bahan Baku')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="hover:underline">Dashboard</a>
    <span class="mx-2 text-on-surface-muted" aria-hidden="true">/</span>
    <a href="{{ route('buyer.demands.index') }}" class="hover:underline">Kebutuhan Saya</a>
    <span class="mx-2 text-on-surface-muted" aria-hidden="true">/</span>
    <span class="text-on-surface" aria-current="page">Ubah Kebutuhan</span>
@endsection

@section('content')
    <div class="max-w-3xl">
        <x-ui.page-header
            title="Ubah Kebutuhan Bahan Baku"
            subtitle="Perbarui data spesifikasi, volume, atau tanggal berlaku kebutuhan perusahaan Anda."
        />

        @if ($errors->any())
            <x-ui.alert variant="danger" class="mb-6">
                <p class="font-medium">Terdapat beberapa kesalahan pengisian formulir:</p>
                <ul class="list-disc list-inside mt-1 text-sm space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-ui.alert>
        @endif

        <form method="POST" action="{{ route('buyer.demands.update', $demand) }}" id="demand-form">
            @csrf
            @method('PUT')

            <x-ui.card class="space-y-6 mb-6">
                <h3 class="text-title-md font-semibold text-on-surface border-b border-outline pb-3">
                    Informasi Material & Kebutuhan
                </h3>

                {{-- Pilih Kategori --}}
                <x-ui.field label="Kategori Material" for="categories_id_categories" name="categories_id_categories" helper="Pilih kategori bahan baku non-B3 yang dibutuhkan." required>
                    <x-ui.select id="categories_id_categories" name="categories_id_categories" required onchange="renderSpecs(this.value)">
                        <option value="">-- Pilih Kategori Material --</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id_categories }}" {{ (old('categories_id_categories', $demand->categories_id_categories) == $cat->id_categories) ? 'selected' : '' }}>
                                {{ $cat->nama }}
                            </option>
                        @endforeach
                    </x-ui.select>
                </x-ui.field>

                {{-- Container Spesifikasi Minimal Dinamis --}}
                <div id="specs-wrapper" class="space-y-4 rounded-sm border border-outline bg-surface-alt/40 p-4">
                    <div class="flex items-center justify-between border-b border-outline pb-2">
                        <span class="text-label-md font-semibold text-on-surface">Spesifikasi Minimal yang Diharapkan</span>
                        <span class="text-xs text-on-surface-muted">Sesuai templat spesifikasi kategori</span>
                    </div>

                    <div id="specs-fields" class="space-y-3">
                        <p id="specs-placeholder" class="text-body-sm text-on-surface-muted italic py-2">
                            Pilih kategori material di atas untuk menampilkan kolom spesifikasi minimal.
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    {{-- Volume (kg) --}}
                    <x-ui.field label="Volume Dibutuhkan (kg)" for="volume_kg" name="volume_kg" helper="Jumlah total bobot dalam kilogram." required>
                        <x-ui.input
                            id="volume_kg"
                            name="volume_kg"
                            type="number"
                            step="0.01"
                            min="0.01"
                            :value="old('volume_kg', $demand->volume_kg)"
                            required
                        />
                    </x-ui.field>

                    {{-- Frekuensi --}}
                    @php
                        $freqVal = $demand->frekuensi instanceof \App\Enums\DemandRequestFrequency ? $demand->frekuensi->value : $demand->frekuensi;
                    @endphp
                    <x-ui.field label="Frekuensi Pasokan" for="frekuensi" name="frekuensi" helper="Seberapa sering pengiriman diharapkan." required>
                        <x-ui.select id="frekuensi" name="frekuensi" required>
                            <option value="SEKALI" {{ old('frekuensi', $freqVal) === 'SEKALI' ? 'selected' : '' }}>Sekali (One-off)</option>
                            <option value="MINGGUAN" {{ old('frekuensi', $freqVal) === 'MINGGUAN' ? 'selected' : '' }}>Mingguan</option>
                            <option value="BULANAN" {{ old('frekuensi', $freqVal) === 'BULANAN' ? 'selected' : '' }}>Bulanan</option>
                        </x-ui.select>
                    </x-ui.field>
                </div>

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    {{-- Harga Maksimal (Opsional) --}}
                    <x-ui.field label="Harga Maksimal per kg (Rp)" for="harga_maks" name="harga_maks" helper="Batas atas harga yang bersedia dibayarkan (opsional).">
                        <x-ui.input
                            id="harga_maks"
                            name="harga_maks"
                            type="number"
                            step="1"
                            min="0"
                            :value="old('harga_maks', $demand->harga_maks ? (int) $demand->harga_maks : '')"
                        />
                    </x-ui.field>

                    {{-- Berlaku Sampai --}}
                    <x-ui.field label="Batas Waktu Berlaku" for="berlaku_sampai" name="berlaku_sampai" helper="Permintaan otomatis ditutup setelah tanggal ini.">
                        <x-ui.input
                            id="berlaku_sampai"
                            name="berlaku_sampai"
                            type="date"
                            min="{{ date('Y-m-d') }}"
                            :value="old('berlaku_sampai', $demand->berlaku_sampai?->format('Y-m-d'))"
                        />
                    </x-ui.field>
                </div>
            </x-ui.card>

            {{-- Tombol Aksi --}}
            <div class="flex items-center justify-end gap-3">
                <x-ui.button href="{{ route('buyer.demands.index') }}" variant="ghost" size="md">
                    Batal
                </x-ui.button>
                @php
                    $isDraft = ($demand->status instanceof \App\Enums\DemandRequestStatus ? $demand->status->value : $demand->status) === 'DRAF';
                @endphp
                @if ($isDraft)
                    <x-ui.button type="submit" name="action" value="draft" variant="secondary" size="md">
                        Simpan Draf
                    </x-ui.button>
                    <x-ui.button type="submit" name="action" value="publish" variant="primary" size="md">
                        Publikasikan ke Pasar
                    </x-ui.button>
                @else
                    <x-ui.button type="submit" name="action" value="publish" variant="primary" size="md">
                        Simpan Perubahan
                    </x-ui.button>
                @endif
            </div>
        </form>
    </div>

    {{-- Script render spesifikasi dinamis --}}
    <script>
        const categoriesSpecData = {
            @foreach ($categories as $cat)
                "{{ $cat->id_categories }}": @json($cat->spec_schema ?? []),
            @endforeach
        };

        const existingSpecs = @json(old('spesifikasi_min', $demand->spesifikasi_min ?? []));

        function renderSpecs(categoryId) {
            const container = document.getElementById('specs-fields');
            container.innerHTML = '';

            const schema = categoriesSpecData[categoryId];
            if (!schema || schema.length === 0) {
                const emptyMsg = document.createElement('p');
                emptyMsg.className = 'text-body-sm text-on-surface-muted italic py-1';
                emptyMsg.textContent = categoryId ? 'Kategori ini tidak memiliki templat spesifikasi khusus.' : 'Pilih kategori material di atas untuk menampilkan kolom spesifikasi minimal.';
                container.appendChild(emptyMsg);
                return;
            }

            schema.forEach(field => {
                const wrapper = document.createElement('div');
                wrapper.className = 'space-y-1';

                const label = document.createElement('label');
                label.className = 'block text-label-sm text-on-surface';
                label.innerHTML = `${field.nama} ${field.satuan ? '<span class="text-xs text-on-surface-muted">(' + field.satuan + ')</span>' : ''} ${field.wajib ? '<span class="text-error">*</span>' : ''}`;
                wrapper.appendChild(label);

                const currentVal = existingSpecs[field.nama] !== undefined ? existingSpecs[field.nama] : '';

                if (field.tipe === 'select' && Array.isArray(field.pilihan)) {
                    const select = document.createElement('select');
                    select.name = `spesifikasi_min[${field.nama}]`;
                    select.className = 'block h-10 w-full rounded-sm border border-outline-input bg-surface px-3 text-body-md text-on-surface focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus-ring';
                    if (field.wajib) select.required = true;

                    const defaultOpt = document.createElement('option');
                    defaultOpt.value = '';
                    defaultOpt.textContent = `-- Pilih ${field.nama} --`;
                    select.appendChild(defaultOpt);

                    field.pilihan.forEach(p => {
                        const opt = document.createElement('option');
                        opt.value = p;
                        opt.textContent = p;
                        if (currentVal === p) opt.selected = true;
                        select.appendChild(opt);
                    });
                    wrapper.appendChild(select);
                } else {
                    const input = document.createElement('input');
                    input.type = field.tipe === 'number' ? 'number' : 'text';
                    input.name = `spesifikasi_min[${field.nama}]`;
                    input.value = currentVal;
                    input.className = 'block h-10 w-full rounded-sm border border-outline-input bg-surface px-3 text-body-md text-on-surface focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus-ring';
                    if (field.wajib) input.required = true;
                    wrapper.appendChild(input);
                }

                container.appendChild(wrapper);
            });
        }

        document.addEventListener('DOMContentLoaded', function () {
            const initialCategory = document.getElementById('categories_id_categories').value;
            if (initialCategory) {
                renderSpecs(initialCategory);
            }
        });
    </script>
@endsection

@extends('layouts.app')

@section('title', 'Edit Kategori ' . $category->nama . ' - SymbioNusa')

@section('content')
<div class="py-6 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Breadcrumb -->
    <nav class="flex items-center text-xs font-medium text-gray-500 mb-2" aria-label="Breadcrumb">
        <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 transition">Dashboard</a>
        <span class="mx-2 text-gray-400">/</span>
        <a href="{{ route('admin.categories.index') }}" class="hover:text-emerald-600 transition">Master Kategori</a>
        <span class="mx-2 text-gray-400">/</span>
        <a href="{{ route('admin.categories.show', $category) }}" class="hover:text-emerald-600 transition">{{ $category->nama }}</a>
        <span class="mx-2 text-gray-400">/</span>
        <span class="text-gray-800 font-semibold" aria-current="page">Edit</span>
    </nav>

    <!-- Header Section -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">Edit Kategori Material</h1>
            <p class="mt-1 text-sm text-gray-600">
                Perbarui konfigurasi batas toleransi, templat spesifikasi mutu, atau status operasional kategori <strong>{{ $category->nama }}</strong>.
            </p>
        </div>
        <div class="flex items-center space-x-2">
            <a href="{{ route('admin.categories.show', $category) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-xs font-semibold rounded-xl text-gray-700 bg-white hover:bg-gray-50 transition">
                Lihat Detail
            </a>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.categories.update', $category) }}" id="category-form" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Informasi Utama & Hierarki -->
        <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-xs space-y-5">
            <h2 class="text-base font-bold text-gray-900 border-b border-gray-100 pb-3">1. Informasi Pokok & Hierarki</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <!-- Nama Kategori -->
                <div>
                    <label for="nama" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                        Nama Kategori <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="nama" name="nama" value="{{ old('nama', $category->nama) }}" required maxlength="100" class="w-full text-sm rounded-xl border border-gray-300 px-3.5 py-2.5 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                    <span class="text-xs text-gray-500 mt-1 block">Maksimal 100 karakter.</span>
                    @error('nama')
                        <span class="text-xs text-rose-600 mt-1 block font-medium">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Kategori Induk (Hierarki) -->
                <div>
                    <label for="categories_id_categories" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                        Kategori Induk (Parent)
                    </label>
                    <select id="categories_id_categories" name="categories_id_categories" class="w-full text-sm rounded-xl border border-gray-300 px-3.5 py-2.5 bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                        <option value="">-- Tanpa Induk (Tingkat Teratas) --</option>
                        @foreach($parentCategories as $parent)
                            <option value="{{ $parent->id_categories }}" {{ old('categories_id_categories', $category->categories_id_categories) == $parent->id_categories ? 'selected' : '' }}>
                                {{ $parent->nama }}
                            </option>
                        @endforeach
                    </select>
                    <span class="text-xs text-gray-500 mt-1 block">Ubah induk hierarki kategori.</span>
                    @error('categories_id_categories')
                        <span class="text-xs text-rose-600 mt-1 block font-medium">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Status Operasional -->
            <div class="pt-2">
                <label for="status" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                    Status Operasional
                </label>
                <select id="status" name="status" class="w-full sm:w-1/2 text-sm rounded-xl border border-gray-300 px-3.5 py-2.5 bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                    <option value="AKTIF" {{ old('status', $category->status) === 'AKTIF' ? 'selected' : '' }}>AKTIF (Dapat digunakan untuk transaksi)</option>
                    <option value="ARSIP" {{ old('status', $category->status) === 'ARSIP' ? 'selected' : '' }}>ARSIP (Hanya riwayat transaksi lama)</option>
                </select>
            </div>
        </div>

        <!-- Toleransi Tonase & Mutu -->
        <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-xs space-y-5">
            <h2 class="text-base font-bold text-gray-900 border-b border-gray-100 pb-3">2. Batas Toleransi & Mutu Material</h2>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                <!-- Toleransi Susut (%) -->
                <div>
                    <label for="toleransi_susut_persen" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                        Toleransi Susut (%) <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="number" id="toleransi_susut_persen" name="toleransi_susut_persen" value="{{ old('toleransi_susut_persen', $category->toleransi_susut_persen) }}" required min="0" max="100" step="0.01" class="w-full text-sm rounded-xl border border-gray-300 px-3.5 py-2.5 pr-8 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                        <span class="absolute right-3 top-2.5 text-sm text-gray-400 font-semibold">%</span>
                    </div>
                    <span class="text-xs text-gray-500 mt-1 block">Disalin saat PO dibuat (rentang 0 - 100%).</span>
                    @error('toleransi_susut_persen')
                        <span class="text-xs text-rose-600 mt-1 block font-medium">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Kadar Air Maksimum (%) -->
                <div>
                    <label for="kadar_air_maks_persen" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                        Kadar Air Maks (%)
                    </label>
                    <div class="relative">
                        <input type="number" id="kadar_air_maks_persen" name="kadar_air_maks_persen" value="{{ old('kadar_air_maks_persen', $category->kadar_air_maks_persen) }}" min="0" max="100" step="0.01" class="w-full text-sm rounded-xl border border-gray-300 px-3.5 py-2.5 pr-8 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                        <span class="absolute right-3 top-2.5 text-sm text-gray-400 font-semibold">%</span>
                    </div>
                    <span class="text-xs text-gray-500 mt-1 block">Batas maksimum kadar air diperbolehkan.</span>
                    @error('kadar_air_maks_persen')
                        <span class="text-xs text-rose-600 mt-1 block font-medium">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Toleransi Kadar Air Poin -->
                <div>
                    <label for="toleransi_kadar_air_poin" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                        Toleransi Kadar Air (Poin)
                    </label>
                    <div class="relative">
                        <input type="number" id="toleransi_kadar_air_poin" name="toleransi_kadar_air_poin" value="{{ old('toleransi_kadar_air_poin', $category->toleransi_kadar_air_poin) }}" min="0" max="100" step="0.01" class="w-full text-sm rounded-xl border border-gray-300 px-3.5 py-2.5 pr-8 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                        <span class="absolute right-3 top-2.5 text-sm text-gray-400 font-semibold">pt</span>
                    </div>
                    <span class="text-xs text-gray-500 mt-1 block">Toleransi selisih kadar air aktual.</span>
                    @error('toleransi_kadar_air_poin')
                        <span class="text-xs text-rose-600 mt-1 block font-medium">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Tingkat Risiko & Kepatuhan Lingkungan -->
        <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-xs space-y-5">
            <h2 class="text-base font-bold text-gray-900 border-b border-gray-100 pb-3">3. Klasifikasi Risiko & Regulasi Non-B3</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <!-- Tingkat Risiko -->
                <div>
                    <label for="tingkat_risiko" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                        Tingkat Risiko Inspeksi <span class="text-rose-500">*</span>
                    </label>
                    <select id="tingkat_risiko" name="tingkat_risiko" required class="w-full text-sm rounded-xl border border-gray-300 px-3.5 py-2.5 bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                        <option value="RENDAH" {{ old('tingkat_risiko', $category->tingkat_risiko) === 'RENDAH' ? 'selected' : '' }}>RENDAH (Rekomendasi Desk Verification)</option>
                        <option value="TINGGI" {{ old('tingkat_risiko', $category->tingkat_risiko) === 'TINGGI' ? 'selected' : '' }}>TINGGI (Rekomendasi Uji Lapangan/Fisik)</option>
                    </select>
                    <span class="text-xs text-gray-500 mt-1 block">Mempengaruhi mode inspeksi yang disarankan sistem (PBI-18).</span>
                    @error('tingkat_risiko')
                        <span class="text-xs text-rose-600 mt-1 block font-medium">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Checkbox Flags -->
                <div class="space-y-3.5 pt-2">
                    <!-- Wajib Sertifikat Lab -->
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input type="checkbox" id="wajib_sertifikat_lab" name="wajib_sertifikat_lab" value="1" {{ old('wajib_sertifikat_lab', $category->wajib_sertifikat_lab) ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                        </div>
                        <div class="ml-3 text-xs">
                            <label for="wajib_sertifikat_lab" class="font-bold text-gray-700">Wajib Sertifikat Hasil Uji Laboratorium</label>
                            <p class="text-gray-500">Seller wajib melampirkan uji lab sebelum diverifikasi.</p>
                        </div>
                    </div>

                    <!-- Penanda Larangan B3 (is_b3_prohibited) -->
                    <div class="flex items-start p-3 rounded-xl bg-rose-50/60 border border-rose-200">
                        <div class="flex items-center h-5">
                            <input type="checkbox" id="is_b3_prohibited" name="is_b3_prohibited" value="1" {{ old('is_b3_prohibited', $category->is_b3_prohibited) ? 'checked' : '' }} class="h-4 w-4 rounded border-rose-300 text-rose-600 focus:ring-rose-500">
                        </div>
                        <div class="ml-3 text-xs">
                            <label for="is_b3_prohibited" class="font-bold text-rose-900">Kategori Terlarang B3 (is_b3_prohibited)</label>
                            <p class="text-rose-700">Tandai jika kategori ini mengandung limbah B3. Pembuatan listing dengan kategori ini otomatis <strong>ditolak</strong> oleh sistem (PBI-09).</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Templat Spesifikasi Teknis (JSON spec_schema) -->
        <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-xs space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-gray-100 pb-3">
                <div>
                    <h2 class="text-base font-bold text-gray-900">4. Templat Spesifikasi Teknis (spec_schema)</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Parameter mutu teknis material yang wajib atau dapat diisi saat penawaran listing.</p>
                </div>
                <button type="button" id="btn-add-param" class="inline-flex items-center text-xs font-bold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 px-3 py-1.5 rounded-lg transition">
                    + Tambah Parameter
                </button>
            </div>

            <!-- Interactive Parameters Container -->
            <div id="params-container" class="space-y-3">
                <!-- Javascript will populate rows -->
            </div>

            <!-- Fallback Raw JSON Textarea (Hidden or Collapsible) -->
            <div class="pt-3 border-t border-gray-100">
                <details class="text-xs text-gray-500">
                    <summary class="cursor-pointer font-semibold text-gray-700 hover:text-emerald-600 transition">
                        Mode Lanjutan: Editor Raw JSON (spec_schema)
                    </summary>
                    <div class="mt-2">
                        <label for="spec_schema_json" class="sr-only">Raw JSON Templat Spesifikasi</label>
                        <textarea id="spec_schema_json" name="spec_schema_json" rows="6" class="w-full font-mono text-xs rounded-xl border border-gray-300 p-3 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">{{ old('spec_schema_json', json_encode(old('spec_schema', $category->spec_schema ?? []), JSON_PRETTY_PRINT)) }}</textarea>
                    </div>
                </details>
            </div>
        </div>

        <!-- Form Submit & Cancel Actions -->
        <div class="flex items-center justify-end space-x-3 pt-2">
            <a href="{{ route('admin.categories.show', $category) }}" class="px-5 py-2.5 rounded-xl border border-gray-300 text-sm font-semibold text-gray-700 hover:bg-gray-100 transition">
                Batal
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-sm font-bold text-white shadow-xs hover:shadow-md transition focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('params-container');
    const jsonTextarea = document.getElementById('spec_schema_json');
    const btnAdd = document.getElementById('btn-add-param');

    let initialParams = [];
    try {
        initialParams = JSON.parse(jsonTextarea.value || '[]');
        if (!Array.isArray(initialParams)) initialParams = [];
    } catch (e) {
        initialParams = [];
    }

    function syncToJson() {
        const rows = container.querySelectorAll('.param-row');
        const params = [];
        rows.forEach(row => {
            const nama = row.querySelector('.param-nama').value.trim();
            const tipe = row.querySelector('.param-tipe').value;
            const satuan = row.querySelector('.param-satuan').value.trim();
            const wajib = row.querySelector('.param-wajib').checked;
            if (nama !== '') {
                params.push({ nama, tipe, satuan, wajib });
            }
        });
        jsonTextarea.value = JSON.stringify(params, null, 2);
    }

    function renderRow(param = { nama: '', tipe: 'text', satuan: '', wajib: false }) {
        const div = document.createElement('div');
        div.className = 'param-row p-3 rounded-xl bg-gray-50 border border-gray-200 grid grid-cols-1 sm:grid-cols-12 gap-3 items-center';
        div.innerHTML = `
            <div class="sm:col-span-4">
                <label class="sr-only">Nama Parameter</label>
                <input type="text" class="param-nama w-full text-xs rounded-lg border border-gray-300 px-3 py-1.5 focus:ring-emerald-500 focus:border-emerald-500" placeholder="Nama Parameter" value="${escapeHtml(param.nama || '')}">
            </div>
            <div class="sm:col-span-3">
                <label class="sr-only">Tipe Data</label>
                <select class="param-tipe w-full text-xs rounded-lg border border-gray-300 px-2 py-1.5 bg-white">
                    <option value="text" ${param.tipe === 'text' ? 'selected' : ''}>Teks</option>
                    <option value="number" ${param.tipe === 'number' ? 'selected' : ''}>Angka (Number)</option>
                    <option value="select" ${param.tipe === 'select' ? 'selected' : ''}>Pilihan (Select)</option>
                    <option value="boolean" ${param.tipe === 'boolean' ? 'selected' : ''}>Ya/Tidak (Boolean)</option>
                </select>
            </div>
            <div class="sm:col-span-2">
                <label class="sr-only">Satuan</label>
                <input type="text" class="param-satuan w-full text-xs rounded-lg border border-gray-300 px-2 py-1.5" placeholder="Satuan" value="${escapeHtml(param.satuan || '')}">
            </div>
            <div class="sm:col-span-2 flex items-center">
                <label class="flex items-center text-xs text-gray-700 cursor-pointer">
                    <input type="checkbox" class="param-wajib h-4 w-4 rounded border-gray-300 text-emerald-600 mr-1.5" ${param.wajib ? 'checked' : ''}>
                    <span class="font-semibold">Wajib</span>
                </label>
            </div>
            <div class="sm:col-span-1 text-right">
                <button type="button" class="btn-remove-param p-1 text-gray-400 hover:text-rose-600 rounded transition" aria-label="Hapus parameter">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
            </div>
        `;

        div.querySelectorAll('input, select').forEach(el => {
            el.addEventListener('input', syncToJson);
            el.addEventListener('change', syncToJson);
        });

        div.querySelector('.btn-remove-param').addEventListener('click', function () {
            div.remove();
            syncToJson();
        });

        container.appendChild(div);
    }

    function escapeHtml(str) {
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    if (initialParams.length > 0) {
        initialParams.forEach(p => renderRow(p));
    }

    btnAdd.addEventListener('click', function () {
        renderRow({ nama: '', tipe: 'text', satuan: '', wajib: false });
    });

    document.getElementById('category-form').addEventListener('submit', function () {
        syncToJson();
    });
});
</script>
@endsection

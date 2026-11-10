@extends('layouts.app')

@section('title', 'Detail Kategori ' . $category->nama . ' - SymbioNusa')

@section('content')
<div class="py-6 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Breadcrumb -->
    <nav class="flex items-center text-xs font-medium text-gray-500 mb-2" aria-label="Breadcrumb">
        <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 transition">Dashboard</a>
        <span class="mx-2 text-gray-400">/</span>
        <a href="{{ route('admin.categories.index') }}" class="hover:text-emerald-600 transition">Master Kategori</a>
        <span class="mx-2 text-gray-400">/</span>
        <span class="text-gray-800 font-semibold" aria-current="page">{{ $category->nama }}</span>
    </nav>

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <div class="flex items-center space-x-2 mb-1">
                @if($category->status === 'AKTIF')
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                        Status: Aktif
                    </span>
                @else
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-gray-200 text-gray-800">
                        Status: Diarsipkan
                    </span>
                @endif

                @if($category->is_b3_prohibited)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800">
                        Terlarang B3 (is_b3_prohibited)
                    </span>
                @else
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        Non-B3 Aman
                    </span>
                @endif
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">{{ $category->nama }}</h1>
            <p class="mt-1 text-sm text-gray-600">
                ID Kategori: <code class="text-xs font-mono font-bold text-gray-700">#{{ $category->id_categories }}</code>
                @if($category->parent)
                    &bull; Induk: <a href="{{ route('admin.categories.show', $category->parent) }}" class="font-semibold text-emerald-700 hover:underline">{{ $category->parent->nama }}</a>
                @else
                    &bull; <span class="font-semibold text-gray-700">Tingkat Teratas (Root)</span>
                @endif
            </p>
        </div>
        <div class="flex items-center space-x-2">
            <a href="{{ route('admin.categories.edit', $category) }}" class="inline-flex items-center px-4 py-2 bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold rounded-xl shadow-xs transition">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Edit Kategori
            </a>
            <a href="{{ route('admin.categories.index') }}" class="inline-flex items-center px-3.5 py-2 border border-gray-300 text-xs font-semibold rounded-xl text-gray-700 bg-white hover:bg-gray-50 transition">
                Kembali
            </a>
        </div>
    </div>

    <!-- Parameter & Toleransi Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <!-- Toleransi Tonase Card -->
        <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-xs">
            <span class="text-xs font-bold text-gray-500 uppercase tracking-wider block mb-1">Toleransi Susut Pengiriman</span>
            <span class="text-3xl font-extrabold text-emerald-700 block">
                {{ number_format((float) $category->toleransi_susut_persen, 2) }}%
            </span>
            <p class="text-xs text-gray-500 mt-2">
                Disalin ke Purchase Order (PO) saat dibuat untuk evaluasi selisih tonase pengiriman barang.
            </p>
        </div>

        <!-- Kadar Air Card -->
        <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-xs">
            <span class="text-xs font-bold text-gray-500 uppercase tracking-wider block mb-1">Batas Kadar Air Maksimum</span>
            <span class="text-3xl font-extrabold text-gray-900 block">
                @if($category->kadar_air_maks_persen !== null)
                    {{ number_format((float) $category->kadar_air_maks_persen, 2) }}%
                @else
                    <span class="text-lg text-gray-400 font-semibold italic">Tidak Diatur</span>
                @endif
            </span>
            <p class="text-xs text-gray-500 mt-2">
                @if($category->toleransi_kadar_air_poin !== null)
                    Toleransi variasi: <strong>&plusmn;{{ number_format((float) $category->toleransi_kadar_air_poin, 2) }} poin</strong>
                @else
                    Tanpa batas toleransi poin kadar air.
                @endif
            </p>
        </div>

        <!-- Risiko & Sertifikasi Card -->
        <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-xs">
            <span class="text-xs font-bold text-gray-500 uppercase tracking-wider block mb-1">Risiko & Verifikasi</span>
            <div class="mt-1 space-y-1.5">
                <div>
                    <span class="text-xs text-gray-500">Tingkat Risiko:</span>
                    @if($category->tingkat_risiko === 'TINGGI')
                        <span class="ml-1 text-xs font-bold text-amber-800 bg-amber-100 px-2 py-0.5 rounded-md">TINGGI (Uji Lapangan)</span>
                    @else
                        <span class="ml-1 text-xs font-bold text-sky-800 bg-sky-100 px-2 py-0.5 rounded-md">RENDAH (Desk Verification)</span>
                    @endif
                </div>
                <div>
                    <span class="text-xs text-gray-500">Sertifikat Lab:</span>
                    @if($category->wajib_sertifikat_lab)
                        <span class="ml-1 text-xs font-bold text-indigo-800 bg-indigo-100 px-2 py-0.5 rounded-md">Wajib Ada</span>
                    @else
                        <span class="ml-1 text-xs font-semibold text-gray-600 bg-gray-100 px-2 py-0.5 rounded-md">Opsional</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Templat Spesifikasi Teknis (spec_schema) -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-xs mb-6 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50/50 flex justify-between items-center">
            <div>
                <h2 class="text-base font-bold text-gray-900">Templat Parameter Spesifikasi Mutu (spec_schema)</h2>
                <p class="text-xs text-gray-500">Daftar parameter yang harus diisi oleh Seller ketika mempublikasikan listing material dalam kategori ini.</p>
            </div>
            <span class="text-xs font-bold px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-800 border border-emerald-200">
                JSON Schema
            </span>
        </div>

        @php
            $schema = $category->spec_schema;
        @endphp

        @if(!empty($schema) && is_array($schema))
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-left">
                    <thead class="bg-gray-50 text-xs font-bold text-gray-600 uppercase tracking-wider">
                        <tr>
                            <th scope="col" class="px-6 py-3">No</th>
                            <th scope="col" class="px-6 py-3">Nama Parameter</th>
                            <th scope="col" class="px-4 py-3">Tipe Data</th>
                            <th scope="col" class="px-4 py-3">Satuan</th>
                            <th scope="col" class="px-4 py-3 text-right">Penanda Wajib</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white text-sm">
                        @foreach($schema as $idx => $param)
                            <tr class="hover:bg-gray-50/50">
                                <td class="px-6 py-3.5 text-xs text-gray-400 font-mono">{{ $idx + 1 }}</td>
                                <td class="px-6 py-3.5 font-bold text-gray-900">{{ $param['nama'] ?? '-' }}</td>
                                <td class="px-4 py-3.5 text-xs text-gray-600 font-mono uppercase">{{ $param['tipe'] ?? 'TEXT' }}</td>
                                <td class="px-4 py-3.5 text-xs text-gray-600">{{ $param['satuan'] ?? '-' }}</td>
                                <td class="px-4 py-3.5 text-right whitespace-nowrap">
                                    @if(!empty($param['wajib']))
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-rose-100 text-rose-800">
                                            Wajib Diisi
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-gray-100 text-gray-600">
                                            Opsional
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-8 px-4 text-xs text-gray-500">
                Belum ada templat parameter spesifikasi khusus untuk kategori ini.
            </div>
        @endif
    </div>

    <!-- Subkategori & Listing Terkait -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- Subkategori Berjenjang -->
        <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-xs">
            <h2 class="text-sm font-bold text-gray-900 border-b border-gray-100 pb-3 mb-3">
                Subkategori Turunan ({{ $category->children->count() }})
            </h2>
            @if($category->children->count() > 0)
                <ul class="divide-y divide-gray-100 text-xs">
                    @foreach($category->children as $child)
                        <li class="py-2.5 flex justify-between items-center">
                            <a href="{{ route('admin.categories.show', $child) }}" class="font-bold text-gray-800 hover:text-emerald-700 transition">
                                {{ $child->nama }}
                            </a>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $child->status === 'AKTIF' ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-600' }}">
                                {{ $child->status }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="text-xs text-gray-400 py-3 italic">Tidak ada subkategori turunan di bawah kategori ini.</p>
            @endif
        </div>

        <!-- Listing Material Terkait -->
        <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-xs">
            <h2 class="text-sm font-bold text-gray-900 border-b border-gray-100 pb-3 mb-3">
                Listing Menggunakan Kategori Ini ({{ $category->listings->count() }})
            </h2>
            @if($category->listings->count() > 0)
                <ul class="divide-y divide-gray-100 text-xs">
                    @foreach($category->listings->take(5) as $listing)
                        <li class="py-2.5 flex justify-between items-center">
                            <div>
                                <span class="font-bold text-gray-800 block">{{ $listing->judul }}</span>
                                <span class="text-gray-400 text-[10px]">Stok: {{ number_format((float) $listing->stok_kg, 0, ',', '.') }} kg</span>
                            </div>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-800">
                                {{ $listing->status }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="text-xs text-gray-400 py-3 italic">Belum ada listing yang menggunakan kategori ini.</p>
            @endif
        </div>
    </div>

    <!-- Manajemen Status Arsip (CategoryService::canArchive) -->
    <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-xs">
        <h2 class="text-base font-bold text-gray-900 mb-2">Manajemen Status & Arsip</h2>
        <p class="text-xs text-gray-500 mb-4">
            Kategori material yang tidak lagi digunakan dapat diarsipkan. Sesuai aturan bisnis SymbioNusa, kategori hanya dapat diarsipkan jika tidak memiliki subkategori aktif, listing aktif, atau permintaan aktif.
        </p>

        @if($category->status === 'AKTIF')
            @if($canArchive)
                <div class="p-4 rounded-xl bg-gray-50 border border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <span class="text-xs font-bold text-gray-800 block">Kategori Siap Diarsipkan</span>
                        <span class="text-xs text-gray-500">Tidak ada subkategori atau listing aktif yang bergantung pada kategori ini.</span>
                    </div>
                    <form method="POST" action="{{ route('admin.categories.archive', $category) }}" onsubmit="return confirm('Arsipkan kategori {{ $category->nama }}?');">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl transition shadow-xs">
                            Arsipkan Sekarang
                        </button>
                    </form>
                </div>
            @else
                <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-900">
                    <div class="flex items-start space-x-2">
                        <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <div>
                            <span class="text-xs font-bold block mb-1">Kategori Belum Dapat Diarsipkan</span>
                            <ul class="list-disc list-inside text-xs space-y-1">
                                @foreach($archiveBlockReasons as $reason)
                                    <li>{{ $reason }}</li>
                                @endforeach
                            </ul>
                            <span class="text-[11px] text-amber-700 mt-2 block font-medium">
                                Selesaikan, ubah, atau arsipkan entitas terkait terlebih dahulu sebelum mengarsipkan kategori ini.
                            </span>
                        </div>
                    </div>
                </div>
            @endif
        @else
            <div class="p-4 rounded-xl bg-gray-50 border border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <span class="text-xs font-bold text-gray-800 block">Kategori Sedang Diarsipkan</span>
                    <span class="text-xs text-gray-500">Kategori ini tidak dapat dipilih saat membuat listing baru.</span>
                </div>
                <form method="POST" action="{{ route('admin.categories.unarchive', $category) }}">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition shadow-xs">
                        Aktifkan Kembali Kategori
                    </button>
                </form>
            </div>
        @endif
    </div>
</div>
@endsection

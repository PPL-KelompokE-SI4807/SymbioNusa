@extends('layouts.app')

@section('title', 'Master Kategori Material - SymbioNusa')

@section('content')
<div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <nav class="flex items-center text-xs font-medium text-gray-500 mb-1" aria-label="Breadcrumb">
                <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 transition">Dashboard</a>
                <span class="mx-2 text-gray-400">/</span>
                <span class="text-gray-800 font-semibold" aria-current="page">Master Kategori</span>
            </nav>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">Master Kategori Material</h1>
            <p class="mt-1 text-sm text-gray-600">
                Kelola kategori material sisa produksi, templat spesifikasi teknis, toleransi tonase, dan penanda risiko Non-B3.
            </p>
        </div>
        <div class="flex items-center">
            <a href="{{ route('admin.categories.create') }}" class="inline-flex items-center justify-center px-4 py-2.5 bg-emerald-700 hover:bg-emerald-800 text-white text-sm font-semibold rounded-xl shadow-xs hover:shadow-md transition focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                <svg class="w-5 h-5 mr-1.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Kategori
            </a>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-gray-200 shadow-xs">
            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider block">Total Kategori</span>
            <span class="text-2xl sm:text-3xl font-extrabold text-gray-900 mt-1 block">{{ $stats['total'] }}</span>
        </div>
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-gray-200 shadow-xs">
            <span class="text-xs font-semibold text-emerald-700 uppercase tracking-wider block">Kategori Aktif</span>
            <span class="text-2xl sm:text-3xl font-extrabold text-emerald-600 mt-1 block">{{ $stats['aktif'] }}</span>
        </div>
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-gray-200 shadow-xs">
            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider block">Diarsipkan</span>
            <span class="text-2xl sm:text-3xl font-extrabold text-gray-600 mt-1 block">{{ $stats['arsip'] }}</span>
        </div>
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-gray-200 shadow-xs">
            <span class="text-xs font-semibold text-rose-700 uppercase tracking-wider block">Terlarang B3</span>
            <span class="text-2xl sm:text-3xl font-extrabold text-rose-600 mt-1 block">{{ $stats['b3_prohibited'] }}</span>
        </div>
    </div>

    <!-- Search & Filter Card -->
    <div class="bg-white rounded-2xl border border-gray-200 p-4 sm:p-5 mb-6 shadow-xs">
        <form method="GET" action="{{ route('admin.categories.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <!-- Search Keyword -->
            <div class="lg:col-span-2">
                <label for="search-input" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Cari Kategori</label>
                <div class="relative">
                    <input type="text" id="search-input" name="q" value="{{ request('q') }}" placeholder="Cari nama kategori..." class="w-full text-sm rounded-xl border border-gray-300 px-3.5 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                </div>
            </div>

            <!-- Status Filter -->
            <div>
                <label for="status-filter" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Status</label>
                <select id="status-filter" name="status" class="w-full text-sm rounded-xl border border-gray-300 px-3 py-2 bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                    <option value="">Semua Status</option>
                    <option value="AKTIF" {{ request('status') === 'AKTIF' ? 'selected' : '' }}>Aktif</option>
                    <option value="ARSIP" {{ request('status') === 'ARSIP' ? 'selected' : '' }}>Arsip</option>
                </select>
            </div>

            <!-- Risk Level Filter -->
            <div>
                <label for="risiko-filter" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Tingkat Risiko</label>
                <select id="risiko-filter" name="tingkat_risiko" class="w-full text-sm rounded-xl border border-gray-300 px-3 py-2 bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                    <option value="">Semua Risiko</option>
                    <option value="RENDAH" {{ request('tingkat_risiko') === 'RENDAH' ? 'selected' : '' }}>Rendah</option>
                    <option value="TINGGI" {{ request('tingkat_risiko') === 'TINGGI' ? 'selected' : '' }}>Tinggi</option>
                </select>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-gray-900 hover:bg-gray-800 text-white text-sm font-semibold rounded-xl transition focus:outline-none focus:ring-2 focus:ring-gray-900">
                    Saring
                </button>
                @if(request()->hasAny(['q', 'status', 'tingkat_risiko', 'is_b3_prohibited']))
                    <a href="{{ route('admin.categories.index') }}" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-xl transition" title="Reset filter">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Category Table / Hierarchy View -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-xs overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center bg-gray-50/50">
            <h2 class="text-base font-bold text-gray-900">Daftar & Hierarki Kategori</h2>
            <span class="text-xs text-gray-500 font-medium">Menampilkan {{ $categories->total() }} kategori</span>
        </div>

        @if($categories->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-left">
                    <thead class="bg-gray-50 text-xs font-bold text-gray-600 uppercase tracking-wider">
                        <tr>
                            <th scope="col" class="px-6 py-3.5">Nama & Hierarki</th>
                            <th scope="col" class="px-4 py-3.5">Toleransi Susut</th>
                            <th scope="col" class="px-4 py-3.5">Kadar Air Maks</th>
                            <th scope="col" class="px-4 py-3.5">Tingkat Risiko</th>
                            <th scope="col" class="px-4 py-3.5">Status B3</th>
                            <th scope="col" class="px-4 py-3.5">Status</th>
                            <th scope="col" class="px-6 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white text-sm">
                        @foreach($categories as $cat)
                            <tr class="hover:bg-gray-50/70 transition">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        @if($cat->categories_id_categories)
                                            <!-- Subcategory indent indicator -->
                                            <div class="mr-2 text-gray-400 pl-4" aria-hidden="true">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                                </svg>
                                            </div>
                                        @endif
                                        <div>
                                            <a href="{{ route('admin.categories.show', $cat) }}" class="font-bold text-gray-900 hover:text-emerald-700 transition">
                                                {{ $cat->nama }}
                                            </a>
                                            @if($cat->parent)
                                                <span class="block text-xs text-gray-500 mt-0.5">
                                                    Induk: <strong class="text-gray-700">{{ $cat->parent->nama }}</strong>
                                                </span>
                                            @else
                                                <span class="inline-block px-2 py-0.5 mt-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700">
                                                    Kategori Induk
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-gray-700 whitespace-nowrap">
                                    <span class="font-semibold">{{ number_format((float) $cat->toleransi_susut_persen, 2) }}%</span>
                                </td>
                                <td class="px-4 py-4 text-gray-700 whitespace-nowrap">
                                    @if($cat->kadar_air_maks_persen !== null)
                                        <span class="font-medium">{{ number_format((float) $cat->kadar_air_maks_persen, 2) }}%</span>
                                        @if($cat->toleransi_kadar_air_poin !== null)
                                            <span class="text-xs text-gray-500 block">&plusmn;{{ number_format((float) $cat->toleransi_kadar_air_poin, 2) }} poin</span>
                                        @endif
                                    @else
                                        <span class="text-gray-400 text-xs italic">Tidak diatur</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    @if($cat->tingkat_risiko === 'TINGGI')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                                            Risiko Tinggi
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-sky-100 text-sky-800">
                                            Risiko Rendah
                                        </span>
                                    @endif
                                    @if($cat->wajib_sertifikat_lab)
                                        <span class="block text-[11px] text-indigo-700 font-semibold mt-0.5">Wajib Lab</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    @if($cat->is_b3_prohibited)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800" title="Ditolak untuk pembuatan listing baru">
                                            Terlarang B3
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                                            Non-B3 Aman
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    @if($cat->status === 'AKTIF')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            Aktif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-700 border border-gray-300">
                                            Arsip
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end space-x-2">
                                        <a href="{{ route('admin.categories.show', $cat) }}" class="p-1.5 text-gray-600 hover:text-emerald-700 hover:bg-emerald-50 rounded-lg transition" title="Lihat Detail & Templat Spesifikasi" aria-label="Lihat detail {{ $cat->nama }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </a>
                                        <a href="{{ route('admin.categories.edit', $cat) }}" class="p-1.5 text-gray-600 hover:text-amber-700 hover:bg-amber-50 rounded-lg transition" title="Edit Kategori" aria-label="Edit {{ $cat->nama }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>

                                        @if($cat->status === 'AKTIF')
                                            <form method="POST" action="{{ route('admin.categories.archive', $cat) }}" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin mengarsipkan kategori {{ $cat->nama }}?');">
                                                @csrf
                                                <button type="submit" class="p-1.5 text-gray-600 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition" title="Arsipkan Kategori" aria-label="Arsipkan {{ $cat->nama }}">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                                                    </svg>
                                                </button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('admin.categories.unarchive', $cat) }}" class="inline">
                                                @csrf
                                                <button type="submit" class="p-1.5 text-gray-600 hover:text-emerald-700 hover:bg-emerald-50 rounded-lg transition" title="Aktifkan Kembali Kategori" aria-label="Aktifkan {{ $cat->nama }}">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $categories->links() }}
            </div>
        @else
            <div class="text-center py-12 px-4">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                <h3 class="mt-2 text-sm font-bold text-gray-900">Belum ada kategori material</h3>
                <p class="mt-1 text-xs text-gray-500">Mulai dengan menambahkan kategori baru untuk katalog sisa produksi industri.</p>
                <div class="mt-4">
                    <a href="{{ route('admin.categories.create') }}" class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-xs transition">
                        Tambah Kategori Pertama
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

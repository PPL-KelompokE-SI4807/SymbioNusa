@extends('layouts.app')

@section('content')
    <div class="max-w-6xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-6">
        @if (session('success'))
            <div class="rounded-lg bg-emerald-50 border border-emerald-200 p-4 text-sm text-emerald-800">{{ session('success') }}</div>
        @endif

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Antrean Verifikasi Perusahaan</h1>
                <p class="text-sm text-gray-500">Tinjau legalitas mitra Seller dan Buyer sebelum diizinkan bertransaksi.</p>
            </div>
            <div class="flex gap-2">
                @foreach (['' => 'Semua', 'PENDING' => 'Pending', 'TERVERIFIKASI' => 'Terverifikasi', 'DITOLAK' => 'Ditolak'] as $key => $label)
                    <a href="{{ route('admin.companies.index', $key ? ['status' => $key] : []) }}"
                        class="px-3 py-1.5 rounded-lg text-xs font-semibold border {{ $status === $key ? 'bg-emerald-700 text-white border-emerald-700' : 'bg-white text-gray-700 border-gray-300' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase">
                    <tr>
                        <th class="px-4 py-3">Perusahaan</th>
                        <th class="px-4 py-3">NIB</th>
                        <th class="px-4 py-3">Tipe</th>
                        <th class="px-4 py-3">Dokumen</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($companies as $item)
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $item->nama }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $item->nib ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $item->tipe }}</td>
                            <td class="px-4 py-3">{{ $item->documents_count }} berkas</td>
                            <td class="px-4 py-3 font-semibold">{{ $item->status_verifikasi }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.companies.show', $item) }}" class="text-emerald-700 hover:underline font-semibold text-xs">
                                    Tinjau Berkas
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-center text-gray-500">Tidak ada data perusahaan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $companies->links() }}
    </div>
@endsection
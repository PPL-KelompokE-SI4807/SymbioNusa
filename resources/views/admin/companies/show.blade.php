@extends('layouts.app')

@section('content')
    <div class="max-w-5xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-6">
        @if (session('error'))
            <div class="rounded-lg bg-red-50 border border-red-200 p-4 text-sm text-red-800">{{ session('error') }}</div>
        @endif

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">{{ $company->nama }}</h1>
                    <p class="text-sm text-gray-500">NIB: {{ $company->nib ?? '-' }} &bull; Tipe: {{ $company->tipe }} &bull; Status: <strong>{{ $company->status_verifikasi }}</strong></p>
                </div>
                <a href="{{ route('admin.companies.index') }}" class="text-sm text-gray-600 hover:underline">&larr; Kembali</a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm border-t pt-4">
                <div><span class="text-gray-500">Alamat:</span> <p class="font-medium">{{ $company->alamat ?? '-' }}</p></div>
                <div><span class="text-gray-500">Kontak:</span> <p class="font-medium">{{ $company->kontak ?? '-' }}</p></div>
                <div><span class="text-gray-500">Koordinat:</span> <p class="font-medium">{{ $company->lat ?? '-' }}, {{ $company->lng ?? '-' }}</p></div>
                <div><span class="text-gray-500">Persetujuan S&K:</span> <p class="font-medium">{{ $company->tos_versi ?? '-' }} ({{ $company->tos_disetujui_pada?->format('d M Y H:i') ?? 'Belum' }})</p></div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
            <h2 class="text-base font-semibold text-gray-900">Berkas Legalitas Terunggah</h2>
            <ul class="divide-y divide-gray-100 text-sm">
                @forelse ($company->documents as $doc)
                    <li class="py-3 flex justify-between items-center">
                        <div>
                            <p class="font-medium text-gray-900">{{ $doc->jenis }} <span class="ml-2 px-2 py-0.5 rounded text-xs bg-gray-100">{{ $doc->status }}</span></p>
                            <p class="text-xs text-gray-500">Masa berlaku: {{ $doc->berlaku_sampai?->format('d M Y') ?? 'Seumur Hidup' }}</p>
                        </div>
                        <a href="{{ asset('storage/' . $doc->file_url) }}" target="_blank" class="px-3 py-1.5 rounded bg-gray-100 hover:bg-gray-200 text-xs font-semibold">
                            Buka Berkas
                        </a>
                    </li>
                @empty
                    <li class="py-4 text-gray-500">Belum ada berkas terunggah.</li>
                @endforelse
            </ul>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
            <form method="POST" action="{{ route('admin.companies.approve', $company) }}" class="space-y-3">
                @csrf
                <h3 class="font-semibold text-emerald-800 text-sm">Setujui Verifikasi Perusahaan</h3>
                <p class="text-xs text-gray-500">Perusahaan akan mendapat akses penuh untuk membuat listing dan bertransaksi.</p>
                <button type="submit" class="px-4 py-2 rounded-lg bg-emerald-700 text-white text-sm font-semibold hover:bg-emerald-800">
                    Setujui (TERVERIFIKASI)
                </button>
            </form>

            <form method="POST" action="{{ route('admin.companies.reject', $company) }}" class="space-y-3">
                @csrf
                <h3 class="font-semibold text-red-800 text-sm">Tolak Verifikasi</h3>
                <textarea name="alasan_penolakan" rows="2" required placeholder="Tuliskan alasan penolakan / berkas yang perlu diperbaiki..."
                    class="w-full rounded-lg border-gray-300 text-sm"></textarea>
                <button type="submit" class="px-4 py-2 rounded-lg bg-red-600 text-white text-sm font-semibold hover:bg-red-700">
                    Tolak (DITOLAK)
                </button>
            </form>
        </div>
    </div>
@endsection
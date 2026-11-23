@extends('layouts.app')

@section('content')
    <div class="max-w-5xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-6">
        @if (session('success'))
            <div class="rounded-lg bg-emerald-50 border border-emerald-200 p-4 text-sm text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="rounded-lg bg-red-50 border border-red-200 p-4 text-sm text-red-800">
                {{ session('error') }}
            </div>
        @endif

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Profil & Verifikasi Legalitas Perusahaan</h1>
                <p class="text-sm text-gray-500 mt-1">Kelola identitas badan usaha, koordinat lokasi, tipe kemitraan, dan dokumen legalitas Non-B3.</p>
            </div>

            <div class="flex items-center gap-3">
                @php
                    $status = $company->status_verifikasi ?? 'PENDING';
                    $badgeClass = match ($status) {
                        'TERVERIFIKASI' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                        'DITOLAK' => 'bg-red-100 text-red-800 border-red-200',
                        default => 'bg-amber-100 text-amber-800 border-amber-200',
                    };
                @endphp
                <span class="px-3 py-1 rounded-full text-xs font-semibold border {{ $badgeClass }}">
                    Status: {{ $status }}
                </span>

                @if ($company && $company->tipe === 'KEDUANYA')
                    <form method="POST" action="{{ route('companies.switch-role') }}">
                        @csrf
                        @php $nextRole = $user->activeRole() === 'SELLER' ? 'BUYER' : 'SELLER'; @endphp
                        <input type="hidden" name="role" value="{{ $nextRole }}">
                        <button type="submit" class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-800 text-white hover:bg-slate-700">
                            Beralih ke Mode {{ $nextRole }}
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <form method="POST" action="{{ route('companies.update') }}" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-5">
            @csrf
            @method('PUT')

            <h2 class="text-base font-semibold text-gray-900 border-b pb-3">1. Identitas Badan Usaha</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label for="company-nama" class="block text-sm font-medium text-gray-700">Nama Perusahaan *</label>
                    <input type="text" id="company-nama" name="nama" value="{{ old('nama', $company->nama ?? '') }}" required
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                    @error('nama') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="company-nib" class="block text-sm font-medium text-gray-700">Nomor Induk Berusaha (NIB) *</label>
                    <input type="text" id="company-nib" name="nib" value="{{ old('nib', $company->nib ?? '') }}" required
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                    @error('nib') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="company-tipe" class="block text-sm font-medium text-gray-700">Tipe Kemitraan *</label>
                    <select id="company-tipe" name="tipe" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                        @foreach (['SELLER' => 'Seller (Penghasil Sisa Produksi)', 'BUYER' => 'Buyer (Pemanfaat Bahan Sekunder)', 'KEDUANYA' => 'Keduanya (Seller & Buyer)'] as $val => $label)
                            <option value="{{ $val }}" @selected(old('tipe', $company->tipe ?? 'SELLER') === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('tipe') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="company-kontak" class="block text-sm font-medium text-gray-700">Kontak Resmi (Telepon / WA) *</label>
                    <input type="text" id="company-kontak" name="kontak" value="{{ old('kontak', $company->kontak ?? '') }}" required
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                    @error('kontak') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="md:col-span-2">
                    <label for="company-alamat" class="block text-sm font-medium text-gray-700">Alamat Lengkap Pabrik / Gudang *</label>
                    <input type="text" id="company-alamat" name="alamat" value="{{ old('alamat', $company->alamat ?? '') }}" required
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                    @error('alamat') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="company-lat" class="block text-sm font-medium text-gray-700">Latitude (Opsional)</label>
                    <input type="text" id="company-lat" name="lat" value="{{ old('lat', $company->lat ?? '') }}" placeholder="-6.917464"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                    @error('lat') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="company-lng" class="block text-sm font-medium text-gray-700">Longitude (Opsional)</label>
                    <input type="text" id="company-lng" name="lng" value="{{ old('lng', $company->lng ?? '') }}" placeholder="107.619123"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                    @error('lng') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="pt-2">
                <label class="inline-flex items-start gap-2 text-sm text-gray-700">
                    <input type="checkbox" name="tos_accepted" value="1" @checked(old('tos_accepted', (bool) ($company?->tos_disetujui_pada)))
                        class="mt-1 rounded border-gray-300 text-emerald-700 focus:ring-emerald-600">
                    <span>Saya menyatakan seluruh data perusahaan benar dan menyetujui <strong>Syarat & Ketentuan SymbioNusa (v1.0-2026)</strong> bahwa seluruh material yang diperdagangkan merupakan sisa produksi industri <strong>Non-B3</strong>.</span>
                </label>
                @error('tos_accepted') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex justify-end">
                <button type="submit" class="px-5 py-2.5 rounded-lg bg-emerald-700 text-white text-sm font-semibold hover:bg-emerald-800">
                    Simpan Profil Perusahaan
                </button>
            </div>
        </form>

        @if ($company)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-5">
                <h2 class="text-base font-semibold text-gray-900 border-b pb-3">2. Dokumen Legalitas & Lingkungan (PDF/JPG/PNG, Maks. 5 MB)</h2>

                @if (! empty($missingDocuments))
                    <div class="rounded-lg bg-amber-50 border border-amber-200 p-4 text-sm text-amber-900">
                        <p class="font-semibold">Dokumen wajib yang belum lengkap atau sudah kedaluwarsa:</p>
                        <ul class="mt-1 list-disc list-inside">
                            @foreach ($missingDocuments as $label)
                                <li>{{ $label }}</li>
                            @endforeach
                        </ul>
                        <p class="mt-1 text-xs">Admin baru dapat memverifikasi perusahaan setelah semua dokumen wajib terunggah dan masih berlaku.</p>
                    </div>
                @endif

                <form method="POST" action="{{ route('companies.documents.store') }}" enctype="multipart/form-data"
                    class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end bg-gray-50 p-4 rounded-lg border border-gray-200">
                    @csrf
                    <div>
                        <label for="company-jenis" class="block text-xs font-medium text-gray-700">Jenis Dokumen *</label>
                        <select id="company-jenis" name="jenis" required class="mt-1 block w-full rounded-lg border-gray-300 text-sm">
                            @foreach ($documentTypes as $value => $label)
                                <option value="{{ $value }}" @selected(old('jenis') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="company-berlaku_sampai" class="block text-xs font-medium text-gray-700">Berlaku Sampai (Opsional)</label>
                        <input type="date" id="company-berlaku_sampai" name="berlaku_sampai" class="mt-1 block w-full rounded-lg border-gray-300 text-sm">
                    </div>

                    <div>
                        <label for="company-dokumen" class="block text-xs font-medium text-gray-700">Berkas (PDF/JPG/PNG) *</label>
                        <input type="file" id="company-dokumen" name="dokumen" accept=".pdf,.jpg,.jpeg,.png" required class="mt-1 block w-full text-xs text-gray-600">
                    </div>

                    <div class="md:col-span-3 flex justify-end">
                        <button type="submit" class="px-4 py-2 rounded-lg bg-emerald-700 text-white text-xs font-semibold hover:bg-emerald-800">
                            Unggah Dokumen
                        </button>
                    </div>
                </form>

                @if ($errors->hasAny(['jenis', 'berlaku_sampai', 'dokumen']))
                    <div class="text-xs text-red-600 space-y-1">
                        @foreach (['jenis', 'berlaku_sampai', 'dokumen'] as $field)
                            @error($field) <p>{{ $message }}</p> @enderror
                        @endforeach
                    </div>
                @endif

                <div class="overflow-x-auto" tabindex="0" role="region" aria-label="Daftar dokumen legalitas">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase">
                            <tr>
                                <th class="px-4 py-3">Jenis Dokumen</th>
                                <th class="px-4 py-3">Masa Berlaku</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($company->documents as $doc)
                                <tr>
                                    <td class="px-4 py-3 font-medium text-gray-900">{{ $doc->jenis }}</td>
                                    <td class="px-4 py-3 text-gray-600">
                                        {{ $doc->berlaku_sampai ? $doc->berlaku_sampai->format('d M Y') : 'Seumur Hidup' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        @php
                                            $docBadge = match ($doc->status) {
                                                'VALID' => 'bg-emerald-100 text-emerald-800',
                                                'KEDALUWARSA' => 'bg-red-100 text-red-800',
                                                default => 'bg-amber-100 text-amber-800',
                                            };
                                        @endphp
                                        <span class="px-2 py-0.5 rounded text-xs font-semibold {{ $docBadge }}">
                                            {{ $doc->status }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right space-x-3">
                                        <a href="{{ asset('storage/' . $doc->file_url) }}" target="_blank" class="text-emerald-700 hover:underline text-xs font-semibold">Lihat</a>
                                        <form method="POST" action="{{ route('companies.documents.destroy', $doc) }}" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:underline text-xs font-semibold">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-6 text-center text-gray-500">Belum ada dokumen legalitas yang diunggah.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
@endsection
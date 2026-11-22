@extends('layouts.app')
@use('App\Support\Format')
@use('App\Support\StatusMap')

@section('title', 'Profil Perusahaan')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="hover:text-on-surface hover:underline">Dashboard</a>
    <span class="mx-2" aria-hidden="true">/</span>
    <span class="text-on-surface" aria-current="page">Profil Perusahaan</span>
@endsection

@php
    $status = $company->status_verifikasi ?? 'PENDING';
    // Status berkas (ENUM company_documents.status) sebagai teks berwarna + glyph; belum ada baris STATUS_MAP-nya.
    $docVariant = fn ($s) => match ((string) $s) {
        'VALID' => 'success',
        'KEDALUWARSA' => 'danger',
        default => 'warning',
    };
@endphp

@section('content')
    <div class="max-w-5xl">
        <x-ui.page-header title="Profil & Verifikasi Legalitas Perusahaan"
            subtitle="Kelola identitas badan usaha, koordinat lokasi, tipe kemitraan, dan dokumen legalitas Non-B3.">
            <x-slot:actions>
                <x-ui.badge :variant="StatusMap::badge('companies.status_verifikasi', $status)">
                    {{ StatusMap::label('companies.status_verifikasi', $status) }}
                </x-ui.badge>

                @if ($company && $company->tipe === 'KEDUANYA')
                    <form method="POST" action="{{ route('companies.switch-role') }}">
                        @csrf
                        @php $nextRole = $user->activeRole() === 'SELLER' ? 'BUYER' : 'SELLER'; @endphp
                        <input type="hidden" name="role" value="{{ $nextRole }}">
                        <x-ui.button type="submit" variant="secondary">
                            Beralih ke mode {{ ucfirst(strtolower($nextRole)) }}
                        </x-ui.button>
                    </form>
                @endif
            </x-slot:actions>
        </x-ui.page-header>

        <form method="POST" action="{{ route('companies.update') }}" class="mb-6">
            @csrf
            @method('PUT')

            <x-ui.card title="Identitas badan usaha">
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <x-ui.field label="Nama perusahaan" for="nama" name="nama" required>
                        <x-ui.input id="nama" name="nama" :value="old('nama', $company->nama ?? '')" required />
                    </x-ui.field>

                    <x-ui.field label="Nomor Induk Berusaha (NIB)" for="nib" name="nib" required>
                        <x-ui.input id="nib" name="nib" :value="old('nib', $company->nib ?? '')" required class="tabular-nums" />
                    </x-ui.field>

                    <x-ui.field label="Tipe kemitraan" for="tipe" name="tipe" required>
                        <x-ui.select id="tipe" name="tipe" required class="h-11">
                            @foreach (['SELLER' => 'Seller (Penghasil Sisa Produksi)', 'BUYER' => 'Buyer (Pemanfaat Bahan Sekunder)', 'KEDUANYA' => 'Keduanya (Seller & Buyer)'] as $val => $label)
                                <option value="{{ $val }}" @selected(old('tipe', $company->tipe ?? 'SELLER') === $val)>{{ $label }}</option>
                            @endforeach
                        </x-ui.select>
                    </x-ui.field>

                    <x-ui.field label="Kontak resmi (telepon / WA)" for="kontak" name="kontak" required>
                        <x-ui.input id="kontak" name="kontak" type="tel" :value="old('kontak', $company->kontak ?? '')" required />
                    </x-ui.field>

                    <x-ui.field label="Alamat lengkap pabrik / gudang" for="alamat" name="alamat" required class="md:col-span-2">
                        <x-ui.input id="alamat" name="alamat" :value="old('alamat', $company->alamat ?? '')" required />
                    </x-ui.field>

                    <x-ui.field label="Latitude (opsional)" for="lat" name="lat">
                        <x-ui.input id="lat" name="lat" :value="old('lat', $company->lat ?? '')" placeholder="-6.917464" class="tabular-nums" />
                    </x-ui.field>

                    <x-ui.field label="Longitude (opsional)" for="lng" name="lng">
                        <x-ui.input id="lng" name="lng" :value="old('lng', $company->lng ?? '')" placeholder="107.619123" class="tabular-nums" />
                    </x-ui.field>
                </div>

                <div class="mt-6 border-t border-outline-hairline pt-5">
                    <label class="flex items-start gap-3 text-body-md text-on-surface">
                        <input type="checkbox" name="tos_accepted" value="1" @checked(old('tos_accepted', (bool) ($company?->tos_disetujui_pada)))
                            @error('tos_accepted') aria-invalid="true" aria-describedby="tos_accepted-error" @enderror
                            class="mt-0.5 size-5 shrink-0 rounded-sm accent-primary focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus-ring">
                        <span>Saya menyatakan seluruh data perusahaan benar dan menyetujui <strong>Syarat & Ketentuan SymbioNusa (v1.0-2026)</strong> bahwa seluruh material yang diperdagangkan merupakan sisa produksi industri <strong>Non-B3</strong>.</span>
                    </label>
                    @error('tos_accepted')
                        <p id="tos_accepted-error" class="mt-2 flex items-center gap-1.5 text-body-sm font-medium text-error">
                            <x-ui.icon name="alert" class="size-4 shrink-0" /> {{ $message }}
                        </p>
                    @enderror
                </div>

                <div class="mt-6 flex justify-end">
                    <x-ui.button type="submit">Simpan Profil Perusahaan</x-ui.button>
                </div>
            </x-ui.card>
        </form>

        @if ($company)
            <x-ui.card title="Dokumen legalitas & lingkungan" :padding="false">
                <x-slot:actions>
                    <span class="text-body-sm text-on-surface-muted">PDF, JPG, atau PNG, maks. 5 MB</span>
                </x-slot:actions>

                <form method="POST" action="{{ route('companies.documents.store') }}" enctype="multipart/form-data"
                    class="grid grid-cols-1 items-start gap-4 border-t border-outline-hairline px-[18px] py-5 sm:px-6 md:grid-cols-3">
                    @csrf
                    <x-ui.field label="Jenis dokumen" for="jenis" name="jenis" required>
                        <x-ui.select id="jenis" name="jenis" required class="h-11">
                            <option value="NIB">NIB / OSS</option>
                            <option value="NPWP">NPWP Perusahaan</option>
                            <option value="AKTA_PENDIRIAN">Akta Pendirian</option>
                            <option value="IZIN_LINGKUNGAN">Izin Lingkungan / UKL-UPL</option>
                            <option value="SURAT_NON_B3">Surat Pernyataan Non-B3</option>
                        </x-ui.select>
                    </x-ui.field>

                    <x-ui.field label="Berlaku sampai (opsional)" for="berlaku_sampai" name="berlaku_sampai">
                        <x-ui.input id="berlaku_sampai" name="berlaku_sampai" type="date" class="tabular-nums" />
                    </x-ui.field>

                    <x-ui.field label="Berkas" for="dokumen" name="dokumen" required>
                        <input type="file" id="dokumen" name="dokumen" accept=".pdf,.jpg,.jpeg,.png" required
                            @error('dokumen') aria-invalid="true" aria-describedby="dokumen-error" @enderror
                            @class([
                                'block w-full rounded-sm border border-dashed bg-surface px-3 py-1.5 text-body-sm text-on-surface',
                                'file:mr-3 file:h-8 file:cursor-pointer file:rounded-sm file:border-0 file:bg-primary-container file:px-3 file:text-label-md file:font-semibold file:text-on-primary-container',
                                'hover:file:bg-primary hover:file:text-on-primary',
                                'focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus-ring',
                                $errors->has('dokumen') ? 'border-error' : 'border-outline-input',
                            ])>
                    </x-ui.field>

                    <div class="flex justify-end md:col-span-3">
                        <x-ui.button type="submit" variant="secondary">
                            <x-ui.icon name="plus" class="size-[18px]" />
                            Unggah Dokumen
                        </x-ui.button>
                    </div>
                </form>

                @if ($company->documents->count() > 0)
                    {{-- Di bawah 1024 px tabel menjadi baris ledger (DESIGN.md › Responsive). --}}
                    <ul class="divide-y divide-outline-hairline border-t border-outline-hairline lg:hidden" aria-label="Dokumen legalitas perusahaan">
                        @foreach ($company->documents as $doc)
                            <li class="flex flex-wrap items-center justify-between gap-3 px-[18px] py-4 sm:px-6">
                                <div class="min-w-0">
                                    <p class="text-dash-row text-on-surface">{{ $doc->jenis }}</p>
                                    <p class="text-body-sm text-on-surface-muted">
                                        Berlaku sampai {{ $doc->berlaku_sampai ? Format::date($doc->berlaku_sampai) : 'seumur hidup' }}
                                    </p>
                                    <x-ui.badge class="mt-1.5" :variant="$docVariant($doc->status)">{{ ucfirst(strtolower((string) $doc->status)) }}</x-ui.badge>
                                </div>
                                <div class="flex items-center gap-4">
                                    <x-ui.button variant="text" href="{{ asset('storage/'.$doc->file_url) }}" target="_blank" class="min-h-11"
                                        aria-label="Lihat {{ $doc->jenis }}">Lihat</x-ui.button>
                                    <form method="POST" action="{{ route('companies.documents.destroy', $doc) }}">
                                        @csrf
                                        @method('DELETE')
                                        <x-ui.button type="submit" variant="text-danger" class="min-h-11"
                                            aria-label="Hapus {{ $doc->jenis }}">Hapus</x-ui.button>
                                    </form>
                                </div>
                            </li>
                        @endforeach
                    </ul>

                    <x-ui.table label="Dokumen legalitas perusahaan" class="hidden lg:block">
                        <x-slot:head>
                            <th scope="col" class="px-6">Jenis dokumen</th>
                            <th scope="col" class="px-4">Berlaku sampai</th>
                            <th scope="col" class="px-4">Status</th>
                            <th scope="col" class="px-6 text-right">Aksi</th>
                        </x-slot:head>
                        @foreach ($company->documents as $doc)
                            <tr class="h-14 border-t border-outline-hairline align-middle">
                                <td class="px-6 py-3 text-dash-row text-on-surface">{{ $doc->jenis }}</td>
                                <td class="px-4 py-3 tabular-nums whitespace-nowrap text-on-surface-muted">
                                    {{ $doc->berlaku_sampai ? Format::date($doc->berlaku_sampai) : 'Seumur hidup' }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <x-ui.badge :variant="$docVariant($doc->status)">{{ ucfirst(strtolower((string) $doc->status)) }}</x-ui.badge>
                                </td>
                                <td class="px-6 py-3 whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-4">
                                        <x-ui.button variant="text" href="{{ asset('storage/'.$doc->file_url) }}" target="_blank" class="min-h-10"
                                            aria-label="Lihat {{ $doc->jenis }}">Lihat</x-ui.button>
                                        <form method="POST" action="{{ route('companies.documents.destroy', $doc) }}">
                                            @csrf
                                            @method('DELETE')
                                            <x-ui.button type="submit" variant="text-danger" class="min-h-10"
                                                aria-label="Hapus {{ $doc->jenis }}">Hapus</x-ui.button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </x-ui.table>
                @else
                    <div class="border-t border-outline-hairline px-6 pb-8 pt-6 text-center">
                        <p class="text-dash-sub text-on-surface">Belum ada dokumen legalitas</p>
                        <p class="mt-1 text-body-sm text-on-surface-muted">Unggah NIB dan dokumen lingkungan agar Admin bisa memverifikasi perusahaan Anda.</p>
                    </div>
                @endif
            </x-ui.card>
        @endif
    </div>
@endsection

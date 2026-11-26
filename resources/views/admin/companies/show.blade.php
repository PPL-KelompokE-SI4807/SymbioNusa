@extends('layouts.app')
@use('App\Support\Format')
@use('App\Support\StatusMap')

@section('title', 'Verifikasi '.$company->nama)

@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="hover:text-on-surface hover:underline">Dashboard</a>
    <span class="mx-2" aria-hidden="true">/</span>
    <a href="{{ route('admin.companies.index') }}" class="hover:text-on-surface hover:underline">Verifikasi Mitra</a>
    <span class="mx-2" aria-hidden="true">/</span>
    <span class="text-on-surface" aria-current="page">{{ $company->nama }}</span>
@endsection

@php
    // Status berkas (ENUM company_documents.status) sebagai teks berwarna + glyph; belum ada baris STATUS_MAP-nya.
    $docVariant = fn ($s) => match ((string) $s) {
        'VALID' => 'success',
        'KEDALUWARSA' => 'danger',
        default => 'warning',
    };
@endphp

@section('content')
    <div class="max-w-5xl">
        <x-ui.page-header :title="$company->nama"
            :subtitle="'NIB '.($company->nib ?? '–').' · '.ucfirst(strtolower((string) $company->tipe))">
            <x-slot:actions>
                <x-ui.button href="{{ route('admin.companies.index') }}" variant="secondary">
                    <x-ui.icon name="arrow-left" class="size-4" />
                    Kembali
                </x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        <x-ui.card title="Profil perusahaan" class="mb-6">
            <x-slot:actions>
                <x-ui.badge :variant="StatusMap::badge('companies.status_verifikasi', $company->status_verifikasi)">
                    {{ StatusMap::label('companies.status_verifikasi', $company->status_verifikasi) }}
                </x-ui.badge>
            </x-slot:actions>

            <dl class="grid grid-cols-1 gap-x-6 gap-y-4 text-body-md sm:grid-cols-2">
                <div>
                    <dt class="text-label-md text-on-surface-muted">Alamat</dt>
                    <dd class="mt-1 text-on-surface">{{ $company->alamat ?? '–' }}</dd>
                </div>
                <div>
                    <dt class="text-label-md text-on-surface-muted">Kontak</dt>
                    <dd class="mt-1 text-on-surface">{{ $company->kontak ?? '–' }}</dd>
                </div>
                <div>
                    <dt class="text-label-md text-on-surface-muted">Koordinat</dt>
                    <dd class="mt-1 tabular-nums text-on-surface">{{ $company->lat ?? '–' }}, {{ $company->lng ?? '–' }}</dd>
                </div>
                <div>
                    <dt class="text-label-md text-on-surface-muted">Persetujuan S&K</dt>
                    <dd class="mt-1 text-on-surface">
                        {{ $company->tos_versi ?? '–' }}
                        <span class="text-on-surface-muted">· {{ $company->tos_disetujui_pada ? Format::date($company->tos_disetujui_pada).', '.$company->tos_disetujui_pada->format('H.i').' WIB' : 'Belum disetujui' }}</span>
                    </dd>
                </div>
            </dl>
        </x-ui.card>

        @if ($needsReReview)
            <x-ui.alert variant="warning" title="Tinjauan Ulang" class="mb-6">
                Perusahaan ini kembali ke antrean karena ada dokumen wajib yang kedaluwarsa.
            </x-ui.alert>
        @endif

        {{-- Dokumen wajib per tipe mitra (CompanyVerificationService::requiredDocumentGroups); status = teks + glyph, tanpa pill. --}}
        <x-ui.card :title="'Dokumen wajib · '.ucfirst(strtolower((string) $company->tipe))" :padding="false" class="mb-6">
            <ul class="divide-y divide-outline-hairline border-t border-outline-hairline">
                @foreach (array_keys($requiredGroups) as $label)
                    @php($isMissing = in_array($label, $missingDocuments, true))
                    <li class="flex flex-wrap items-center justify-between gap-x-4 gap-y-1 px-[18px] py-3 sm:px-6">
                        <span class="text-body-md text-on-surface">{{ $label }}</span>
                        <span @class(['flex items-center gap-1.5 text-label-md font-bold', 'text-error' => $isMissing, 'text-success' => ! $isMissing])>
                            <x-ui.icon :name="$isMissing ? 'alert' : 'check'" class="size-4 shrink-0" />
                            {{ $isMissing ? 'Belum ada / kedaluwarsa' : 'Lengkap' }}
                        </span>
                    </li>
                @endforeach
            </ul>
        </x-ui.card>

        <x-ui.card :title="'Berkas legalitas · '.$company->documents->count()" :padding="false" class="mb-6">
            <ul class="divide-y divide-outline-hairline border-t border-outline-hairline">
                @forelse ($company->documents as $doc)
                    <li class="flex flex-wrap items-center justify-between gap-3 px-[18px] py-4 sm:px-6">
                        <div class="min-w-0">
                            <p class="flex flex-wrap items-center gap-x-3 gap-y-1">
                                <span class="text-dash-row text-on-surface">{{ $doc->jenis }}</span>
                                <x-ui.badge :variant="$docVariant($doc->status)">{{ ucfirst(strtolower((string) $doc->status)) }}</x-ui.badge>
                            </p>
                            <p class="mt-0.5 text-body-sm text-on-surface-muted">
                                Berlaku sampai {{ $doc->berlaku_sampai ? Format::date($doc->berlaku_sampai) : 'seumur hidup' }}
                            </p>
                        </div>
                        <x-ui.button href="{{ asset('storage/'.$doc->file_url) }}" target="_blank" variant="secondary"
                            aria-label="Buka berkas {{ $doc->jenis }}">
                            Buka berkas
                        </x-ui.button>
                    </li>
                @empty
                    <li class="px-[18px] py-6 sm:px-6">
                        <p class="text-dash-sub text-on-surface">Belum ada berkas terunggah</p>
                        <p class="mt-1 text-body-sm text-on-surface-muted">Perusahaan perlu mengunggah dokumen legalitas sebelum bisa diverifikasi.</p>
                    </li>
                @endforelse
            </ul>
        </x-ui.card>

        <x-ui.card title="Keputusan verifikasi">
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 md:gap-0 md:divide-x md:divide-outline-hairline">
                <form method="POST" action="{{ route('admin.companies.approve', $company) }}" class="space-y-3 md:pr-6">
                    @csrf
                    <h3 class="text-dash-sub text-on-surface">Setujui verifikasi</h3>
                    <p class="text-body-sm text-on-surface-muted">Perusahaan mendapat akses penuh untuk membuat listing dan bertransaksi.</p>
                    {{-- Server menolak persetujuan bila dokumen wajib kurang/kedaluwarsa; tombol tidak menjanjikan aksi yang pasti gagal (DESIGN.md › Action resolution). --}}
                    @php($approveBlocked = $missingDocuments !== [])
                    <x-ui.button type="submit" :disabled="$approveBlocked" :aria-describedby="$approveBlocked ? 'approve-blocked' : null">
                        <x-ui.icon name="check" class="size-[18px]" />
                        Setujui
                    </x-ui.button>
                    @if ($approveBlocked)
                        <p id="approve-blocked" class="text-body-sm font-medium text-warning">Belum bisa disetujui: dokumen wajib belum lengkap atau kedaluwarsa.</p>
                    @endif
                </form>

                <form method="POST" action="{{ route('admin.companies.reject', $company) }}"
                    class="space-y-3 border-t border-outline-hairline pt-6 md:border-t-0 md:pt-0 md:pl-6">
                    @csrf
                    <h3 class="text-dash-sub text-on-surface">Tolak verifikasi</h3>
                    <x-ui.field label="Alasan penolakan" for="alasan_penolakan" name="alasan_penolakan" required>
                        <x-ui.textarea id="alasan_penolakan" name="alasan_penolakan" rows="2" required
                            placeholder="Tuliskan alasan penolakan atau berkas yang perlu diperbaiki…">{{ old('alasan_penolakan') }}</x-ui.textarea>
                    </x-ui.field>
                    <x-ui.button type="submit" variant="danger">Tolak</x-ui.button>
                </form>
            </div>
        </x-ui.card>
    </div>
@endsection

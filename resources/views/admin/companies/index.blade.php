@extends('layouts.app')
@use('App\Support\StatusMap')

@section('title', 'Verifikasi Mitra')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="hover:text-on-surface hover:underline">Dashboard</a>
    <span class="mx-2" aria-hidden="true">/</span>
    <span class="text-on-surface" aria-current="page">Verifikasi Mitra</span>
@endsection

@section('content')
    <x-ui.page-header title="Antrean Verifikasi Perusahaan"
        subtitle="Tinjau legalitas mitra Seller dan Buyer sebelum diizinkan bertransaksi." />

    {{-- Saringan status: kontrol bersudut (bukan pill), satu-satunya pengendali daftar di bawahnya. --}}
    <nav aria-label="Saring status verifikasi" class="mb-6">
        <ul class="inline-flex flex-wrap gap-1 rounded-sm border border-outline-control bg-surface-muted p-1">
            @foreach (['' => 'Semua', 'PENDING' => 'Menunggu', 'TERVERIFIKASI' => 'Terverifikasi', 'DITOLAK' => 'Ditolak'] as $key => $label)
                <li>
                    <a href="{{ route('admin.companies.index', $key ? ['status' => $key] : []) }}"
                        @if ($status === $key) aria-current="page" @endif
                        @class([
                            'flex min-h-10 items-center rounded-md px-3.5 text-label-md font-semibold transition-colors',
                            'focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus-ring',
                            'bg-surface text-on-surface shadow-card' => $status === $key,
                            'text-on-surface-muted hover:text-on-surface' => $status !== $key,
                        ])>
                        {{ $label }}
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>

    <x-ui.card title="Daftar perusahaan" :padding="false">
        <x-slot:actions>
            <span class="text-body-sm text-on-surface-muted">{{ $companies->total() }} perusahaan</span>
        </x-slot:actions>

        @if ($companies->count() > 0)
            {{-- Di bawah 1024 px tabel menjadi baris ledger (DESIGN.md › Responsive). --}}
            <ul class="divide-y divide-outline-hairline border-t border-outline-hairline lg:hidden" aria-label="Daftar perusahaan untuk verifikasi">
                @foreach ($companies as $item)
                    <li class="flex items-center gap-3 px-[18px] py-4 sm:px-6">
                        <x-ui.monogram :name="$item->nama" />
                        <div class="min-w-0 flex-1">
                            <p class="text-dash-row text-on-surface">{{ $item->nama }}</p>
                            <p class="text-body-sm text-on-surface-muted">
                                {{ ucfirst(strtolower((string) $item->tipe)) }} · NIB <span class="tabular-nums">{{ $item->nib ?? '–' }}</span> · {{ $item->documents_count }} berkas
                            </p>
                            <x-ui.badge class="mt-1.5" :variant="StatusMap::badge('companies.status_verifikasi', $item->status_verifikasi)">
                                {{ StatusMap::label('companies.status_verifikasi', $item->status_verifikasi) }}
                            </x-ui.badge>
                        </div>
                        <x-ui.button href="{{ route('admin.companies.show', $item) }}" variant="secondary" size="lg"
                            aria-label="Tinjau berkas {{ $item->nama }}">Tinjau</x-ui.button>
                    </li>
                @endforeach
            </ul>

            <x-ui.table label="Daftar perusahaan untuk verifikasi" class="hidden lg:block">
                <x-slot:head>
                    <th scope="col" class="px-6">Perusahaan</th>
                    <th scope="col" class="px-4">NIB</th>
                    <th scope="col" class="px-4">Tipe</th>
                    <th scope="col" class="px-4 text-right">Dokumen</th>
                    <th scope="col" class="px-4">Status</th>
                    <th scope="col" class="px-6 text-right">Aksi</th>
                </x-slot:head>

                @foreach ($companies as $item)
                    <tr class="h-14 border-t border-outline-hairline align-middle hover:bg-surface-muted/60">
                        <td class="px-6 py-3">
                            <div class="flex items-center gap-3">
                                <x-ui.monogram :name="$item->nama" size="sm" />
                                <span class="text-dash-row text-on-surface">{{ $item->nama }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 tabular-nums whitespace-nowrap text-on-surface-muted">{{ $item->nib ?? '–' }}</td>
                        <td class="px-4 py-3 whitespace-nowrap">{{ ucfirst(strtolower((string) $item->tipe)) }}</td>
                        <td class="px-4 py-3 text-right tabular-nums whitespace-nowrap">{{ $item->documents_count }} berkas</td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <x-ui.badge :variant="StatusMap::badge('companies.status_verifikasi', $item->status_verifikasi)">
                                {{ StatusMap::label('companies.status_verifikasi', $item->status_verifikasi) }}
                            </x-ui.badge>
                        </td>
                        <td class="px-6 py-3 text-right whitespace-nowrap">
                            <x-ui.button href="{{ route('admin.companies.show', $item) }}" variant="secondary"
                                aria-label="Tinjau berkas {{ $item->nama }}">Tinjau</x-ui.button>
                        </td>
                    </tr>
                @endforeach
            </x-ui.table>

            @if ($companies->hasPages())
                <div class="border-t border-outline-hairline px-6 py-4">
                    {{ $companies->links() }}
                </div>
            @endif
        @else
            <div class="border-t border-outline-hairline px-6 pb-10 pt-8 text-center">
                <p class="text-dash-sub text-on-surface">Tidak ada perusahaan pada saringan ini</p>
                <p class="mt-1 text-body-sm text-on-surface-muted">Pilih status lain untuk melihat perusahaan yang sudah diproses.</p>
            </div>
        @endif
    </x-ui.card>
@endsection

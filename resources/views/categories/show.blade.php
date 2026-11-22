@extends('layouts.app')
@use('App\Support\Format')
@use('App\Support\StatusMap')

@section('title', 'Detail Kategori ' . $category->nama)

@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="hover:text-on-surface hover:underline">Dashboard</a>
    <span class="mx-2" aria-hidden="true">/</span>
    <a href="{{ route('admin.categories.index') }}" class="hover:text-on-surface hover:underline">Kategori Material</a>
    <span class="mx-2" aria-hidden="true">/</span>
    <span class="text-on-surface" aria-current="page">{{ $category->nama }}</span>
@endsection

@php
    $schema = is_array($category->spec_schema) ? $category->spec_schema : [];
    $tipeLabel = ['text' => 'Teks', 'number' => 'Angka', 'select' => 'Pilihan', 'boolean' => 'Ya/Tidak'];
@endphp

@section('content')
    <div class="max-w-5xl">
        <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
            <div class="min-w-0">
                <div class="mb-2 flex flex-wrap items-center gap-2">
                    <x-ui.badge :variant="StatusMap::badge('categories.status', $category->status)">
                        {{ StatusMap::label('categories.status', $category->status) }}
                    </x-ui.badge>
                    @if ($category->is_b3_prohibited)
                        <x-ui.badge variant="danger">Dilarang (B3)</x-ui.badge>
                    @endif
                </div>
                <h1 class="page-title text-on-surface">{{ $category->nama }}</h1>
                <p class="mt-1 text-body-md text-on-surface-muted">
                    @if ($category->parent)
                        Subkategori dari
                        <a href="{{ route('admin.categories.show', $category->parent) }}"
                            class="text-primary hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus-ring">{{ $category->parent->nama }}</a>
                    @else
                        Kategori tingkat teratas
                    @endif
                    <span aria-hidden="true">·</span>
                    ID <span class="tabular-nums">#{{ $category->id_categories }}</span>
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <x-ui.button variant="secondary" href="{{ route('admin.categories.index') }}">Kembali</x-ui.button>
                <x-ui.button href="{{ route('admin.categories.edit', $category) }}">Edit Kategori</x-ui.button>
            </div>
        </div>

        <x-ui.summary-strip label="Aturan mutu kategori" cols="3" class="mb-6">
            <x-ui.kpi label="Toleransi susut pengiriman"
                :value="Format::number($category->toleransi_susut_persen, 2).'%'"
                caption="Disalin ke PO saat dibuat untuk evaluasi selisih tonase." />

            <x-ui.kpi label="Kadar air maksimum"
                :value="$category->kadar_air_maks_persen !== null ? Format::number($category->kadar_air_maks_persen, 2).'%' : 'Tidak diatur'"
                :caption="$category->toleransi_kadar_air_poin !== null
                    ? 'Toleransi ±'.Format::number($category->toleransi_kadar_air_poin, 2).' poin'
                    : 'Tanpa toleransi poin kadar air.'" />

            <div class="col-span-2 min-w-0 bg-surface p-[18px] sm:px-6 sm:py-5 xl:col-span-1">
                <dt class="text-label-md font-semibold text-on-surface-muted">Risiko & verifikasi</dt>
                <dd><dl class="mt-3 space-y-3 text-body-md">
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-on-surface-muted">Tingkat risiko</dt>
                        <dd>
                            <x-ui.badge :variant="StatusMap::badge('categories.tingkat_risiko', $category->tingkat_risiko)">
                                {{ StatusMap::label('categories.tingkat_risiko', $category->tingkat_risiko) }}
                            </x-ui.badge>
                        </dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-on-surface-muted">Sertifikat lab</dt>
                        <dd class="text-on-surface">{{ $category->wajib_sertifikat_lab ? 'Wajib' : 'Opsional' }}</dd>
                    </div>
                </dl></dd>
            </div>
        </x-ui.summary-strip>

        <x-ui.card title="Templat spesifikasi" :padding="false" class="mb-6">
            <p class="-mt-2 px-6 pb-4 text-body-sm text-on-surface-muted">
                Parameter yang diisi Seller saat membuat listing dengan kategori ini.
            </p>

            @if (count($schema) > 0)
                <x-ui.table label="Parameter templat spesifikasi">
                    <x-slot:head>
                        <th scope="col" class="w-12 px-6 text-right">No</th>
                        <th scope="col" class="px-4">Parameter</th>
                        <th scope="col" class="px-4">Tipe</th>
                        <th scope="col" class="px-4">Satuan</th>
                        <th scope="col" class="px-6">Pengisian</th>
                    </x-slot:head>
                    @foreach ($schema as $idx => $param)
                        <tr class="h-14 border-t border-outline-hairline">
                            <td class="px-6 py-3 text-right tabular-nums text-on-surface-muted">{{ $idx + 1 }}</td>
                            <td class="px-4 py-3 font-medium text-on-surface">{{ $param['nama'] ?? '–' }}</td>
                            <td class="px-4 py-3 text-on-surface">{{ $tipeLabel[$param['tipe'] ?? 'text'] ?? ($param['tipe'] ?? 'Teks') }}</td>
                            <td class="px-4 py-3 text-on-surface">{{ filled($param['satuan'] ?? null) ? $param['satuan'] : '–' }}</td>
                            <td class="px-6 py-3 whitespace-nowrap">
                                @if (! empty($param['wajib']))
                                    <x-ui.badge variant="warning">Wajib</x-ui.badge>
                                @else
                                    <x-ui.badge>Opsional</x-ui.badge>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </x-ui.table>
            @else
                <p class="border-t border-outline-hairline px-6 py-8 text-center text-body-sm text-on-surface-muted">
                    Belum ada parameter templat untuk kategori ini.
                </p>
            @endif
        </x-ui.card>

        <div class="mb-6 grid grid-cols-1 gap-6 md:grid-cols-2">
            <x-ui.card :title="'Subkategori · '.$category->children->count()">
                @if ($category->children->count() > 0)
                    <ul class="divide-y divide-outline-hairline">
                        @foreach ($category->children as $child)
                            <li class="flex items-center justify-between gap-3 py-3">
                                <a href="{{ route('admin.categories.show', $child) }}"
                                    class="min-w-0 truncate text-body-md text-on-surface hover:text-primary hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus-ring">
                                    {{ $child->nama }}
                                </a>
                                <x-ui.badge :variant="StatusMap::badge('categories.status', $child->status)">
                                    {{ StatusMap::label('categories.status', $child->status) }}
                                </x-ui.badge>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-body-sm text-on-surface-muted">Tidak ada subkategori di bawah kategori ini.</p>
                @endif
            </x-ui.card>

            <x-ui.card :title="'Listing dengan kategori ini · '.$category->listings->count()">
                @if ($category->listings->count() > 0)
                    <ul class="divide-y divide-outline-hairline">
                        @foreach ($category->listings->take(5) as $listing)
                            <li class="flex items-center justify-between gap-3 py-3">
                                <div class="min-w-0">
                                    <p class="truncate text-body-md text-on-surface">{{ $listing->judul }}</p>
                                    <p class="text-body-sm tabular-nums text-on-surface-muted">Stok {{ Format::kg($listing->stok_kg) }}</p>
                                </div>
                                <x-ui.badge :variant="StatusMap::badge('listings.status', $listing->status)">
                                    {{ StatusMap::label('listings.status', $listing->status) }}
                                </x-ui.badge>
                            </li>
                        @endforeach
                    </ul>
                    @if ($category->listings->count() > 5)
                        <p class="mt-3 text-body-sm text-on-surface-muted">Menampilkan 5 dari {{ Format::number($category->listings->count()) }} listing.</p>
                    @endif
                @else
                    <p class="text-body-sm text-on-surface-muted">Belum ada listing yang memakai kategori ini.</p>
                @endif
            </x-ui.card>
        </div>

        <x-ui.card title="Status & arsip">
            <p class="-mt-2 mb-4 text-body-sm text-on-surface-muted">
                Kategori hanya bisa diarsipkan bila tidak punya subkategori aktif, listing aktif, atau kebutuhan aktif.
            </p>

            @if ($category->status === 'AKTIF')
                @if ($canArchive)
                    <div class="flex flex-wrap items-center justify-between gap-4 border-t border-outline-hairline pt-4">
                        <div>
                            <p class="text-dash-row text-on-surface">Kategori siap diarsipkan</p>
                            <p class="text-body-sm text-on-surface-muted">Tidak ada subkategori atau listing aktif yang bergantung pada kategori ini.</p>
                        </div>
                        <form method="POST" action="{{ route('admin.categories.archive', $category) }}"
                            onsubmit="return confirm(@js('Arsipkan kategori '.$category->nama.'?'))">
                            @csrf
                            <x-ui.button type="submit" variant="danger">Arsipkan</x-ui.button>
                        </form>
                    </div>
                @else
                    <x-ui.alert variant="warning" title="Kategori belum bisa diarsipkan">
                        <ul class="mt-1 list-inside list-disc space-y-1">
                            @foreach ($archiveBlockReasons as $reason)
                                <li>{{ $reason }}</li>
                            @endforeach
                        </ul>
                        <p class="mt-2 text-body-sm">Selesaikan, ubah, atau arsipkan data terkait terlebih dahulu.</p>
                    </x-ui.alert>
                @endif
            @else
                <div class="flex flex-wrap items-center justify-between gap-4 border-t border-outline-hairline pt-4">
                    <div>
                        <p class="text-dash-row text-on-surface">Kategori sedang diarsipkan</p>
                        <p class="text-body-sm text-on-surface-muted">Kategori ini tidak bisa dipilih saat membuat listing baru.</p>
                    </div>
                    <form method="POST" action="{{ route('admin.categories.unarchive', $category) }}">
                        @csrf
                        <x-ui.button type="submit" variant="secondary">Aktifkan Kembali</x-ui.button>
                    </form>
                </div>
            @endif
        </x-ui.card>
    </div>
@endsection

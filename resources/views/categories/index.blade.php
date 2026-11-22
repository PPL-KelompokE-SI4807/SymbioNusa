@extends('layouts.app')
@use('App\Support\Format')
@use('App\Support\StatusMap')

@section('title', 'Kategori Material')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="hover:text-on-surface hover:underline">Dashboard</a>
    <span class="mx-2" aria-hidden="true">/</span>
    <span class="text-on-surface" aria-current="page">Kategori Material</span>
@endsection

@php
    $filtered = request()->hasAny(['q', 'status', 'tingkat_risiko', 'is_b3_prohibited']);
@endphp

@section('content')
    <x-ui.page-header title="Kategori Material"
        subtitle="Kelola kategori material sisa produksi, templat spesifikasi, toleransi tonase, dan penanda B3.">
        <x-slot:actions>
            <x-ui.button href="{{ route('admin.categories.create') }}">
                <x-ui.icon name="plus" />
                Tambah Kategori
            </x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.summary-strip label="Ringkasan kategori" class="mb-6">
        <x-ui.kpi label="Total kategori" :value="Format::number($stats['total'])" />
        <x-ui.kpi label="Aktif" :value="Format::number($stats['aktif'])" />
        <x-ui.kpi label="Diarsipkan" :value="Format::number($stats['arsip'])" />
        <x-ui.kpi label="Dilarang (B3)" :value="Format::number($stats['b3_prohibited'])" caption="Ditolak saat membuat listing" />
    </x-ui.summary-strip>

    <x-ui.card class="mb-6">
        <form method="GET" action="{{ route('admin.categories.index') }}" role="search" aria-label="Saring kategori"
            class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-[minmax(0,2fr)_minmax(0,1fr)_minmax(0,1fr)_auto] lg:items-end">
            <x-ui.field label="Cari kategori" for="search-input" class="sm:col-span-2 lg:col-span-1">
                <x-ui.input id="search-input" name="q" type="search" :value="request('q')" placeholder="Cari nama kategori…" />
            </x-ui.field>

            <x-ui.field label="Status" for="status-filter">
                <x-ui.select id="status-filter" name="status">
                    <option value="">Semua status</option>
                    <option value="AKTIF" @selected(request('status') === 'AKTIF')>Aktif</option>
                    <option value="ARSIP" @selected(request('status') === 'ARSIP')>Diarsipkan</option>
                </x-ui.select>
            </x-ui.field>

            <x-ui.field label="Tingkat risiko" for="risiko-filter">
                <x-ui.select id="risiko-filter" name="tingkat_risiko">
                    <option value="">Semua risiko</option>
                    <option value="RENDAH" @selected(request('tingkat_risiko') === 'RENDAH')>Risiko Rendah</option>
                    <option value="TINGGI" @selected(request('tingkat_risiko') === 'TINGGI')>Risiko Tinggi</option>
                </x-ui.select>
            </x-ui.field>

            <div class="flex items-center gap-2 sm:col-span-2 lg:col-span-1">
                <x-ui.button type="submit" variant="secondary">Terapkan</x-ui.button>
                @if ($filtered)
                    <x-ui.button variant="text" href="{{ route('admin.categories.index') }}" class="h-10 px-2">Reset</x-ui.button>
                @endif
            </div>
        </form>
    </x-ui.card>

    <x-ui.card title="Daftar & hierarki kategori" :padding="false">
        <x-slot:actions>
            <span class="text-body-sm text-on-surface-muted">{{ Format::number($categories->total()) }} kategori</span>
        </x-slot:actions>

        @if ($categories->count() > 0)
            {{-- Di bawah 1280 px tabel 7 kolom tidak muat tanpa digeser, jadi tampil sebagai daftar kartu. --}}
            <ul class="divide-y divide-outline-hairline border-t border-outline-hairline xl:hidden" aria-label="Daftar kategori material">
                @foreach ($categories as $cat)
                    <li class="flex flex-col gap-3 px-[18px] py-4 sm:px-6">
                        <div class="min-w-0">
                            <a href="{{ route('admin.categories.show', $cat) }}"
                                class="text-dash-row text-on-surface hover:text-primary hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus-ring">
                                {{ $cat->nama }}
                            </a>
                            <p class="text-body-sm text-on-surface-muted">
                                {{ $cat->parent ? 'Subkategori dari '.$cat->parent->nama : 'Kategori induk' }}
                            </p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <x-ui.badge :variant="StatusMap::badge('categories.status', $cat->status)">
                                {{ StatusMap::label('categories.status', $cat->status) }}
                            </x-ui.badge>
                            <x-ui.badge :variant="StatusMap::badge('categories.tingkat_risiko', $cat->tingkat_risiko)">
                                {{ StatusMap::label('categories.tingkat_risiko', $cat->tingkat_risiko) }}
                            </x-ui.badge>
                            @if ($cat->is_b3_prohibited)
                                <x-ui.badge variant="danger">Dilarang (B3)</x-ui.badge>
                            @endif
                        </div>
                        <dl class="grid grid-cols-2 gap-x-4 gap-y-1 text-body-sm">
                            <dt class="text-on-surface-muted">Toleransi susut</dt>
                            <dd class="text-right tabular-nums text-on-surface">{{ Format::number($cat->toleransi_susut_persen, 2) }}%</dd>
                            <dt class="text-on-surface-muted">Kadar air maks.</dt>
                            <dd class="text-right tabular-nums text-on-surface">
                                @if ($cat->kadar_air_maks_persen !== null)
                                    {{ Format::number($cat->kadar_air_maks_persen, 2) }}%@if ($cat->toleransi_kadar_air_poin !== null) <span class="text-on-surface-muted">±{{ Format::number($cat->toleransi_kadar_air_poin, 2) }}</span>@endif
                                @else
                                    <span class="text-on-surface-muted">Tidak diatur</span>
                                @endif
                            </dd>
                            @if ($cat->wajib_sertifikat_lab)
                                <dt class="text-on-surface-muted">Uji lab</dt>
                                <dd class="text-right text-on-surface">Wajib</dd>
                            @endif
                        </dl>
                        <div class="flex items-center gap-4">
                            <x-ui.button variant="text" href="{{ route('admin.categories.edit', $cat) }}" class="min-h-10" aria-label="Edit {{ $cat->nama }}">Edit</x-ui.button>
                            @if ($cat->status === 'AKTIF')
                                <form method="POST" action="{{ route('admin.categories.archive', $cat) }}"
                                    onsubmit="return confirm(@js('Arsipkan kategori '.$cat->nama.'?'))">
                                    @csrf
                                    <x-ui.button type="submit" variant="text-danger" class="min-h-10" aria-label="Arsipkan {{ $cat->nama }}">Arsipkan</x-ui.button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('admin.categories.unarchive', $cat) }}">
                                    @csrf
                                    <x-ui.button type="submit" variant="text" class="min-h-10" aria-label="Aktifkan kembali {{ $cat->nama }}">Aktifkan</x-ui.button>
                                </form>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>

            <x-ui.table label="Daftar kategori material" class="hidden xl:block">
                <x-slot:head>
                    <th scope="col" class="px-6">Nama & hierarki</th>
                    <th scope="col" class="px-4 text-right">Toleransi susut</th>
                    <th scope="col" class="px-4 text-right">Kadar air maks.</th>
                    <th scope="col" class="px-4">Risiko</th>
                    <th scope="col" class="px-4">B3</th>
                    <th scope="col" class="px-4">Status</th>
                    <th scope="col" class="px-6 text-right">Aksi</th>
                </x-slot:head>

                @foreach ($categories as $cat)
                    <tr class="h-14 border-t border-outline-hairline align-middle hover:bg-surface-muted/60">
                        <td class="px-6 py-3">
                            <div @class(['flex items-start gap-2', 'pl-4' => $cat->categories_id_categories])>
                                @if ($cat->categories_id_categories)
                                    <x-ui.icon name="chevron-right" class="mt-0.5 size-4 shrink-0 text-on-surface-muted" />
                                @endif
                                <div class="min-w-0">
                                    <a href="{{ route('admin.categories.show', $cat) }}"
                                        class="text-dash-row text-on-surface hover:text-primary hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus-ring">
                                        {{ $cat->nama }}
                                    </a>
                                    <p class="text-body-sm text-on-surface-muted">
                                        {{ $cat->parent ? 'Subkategori dari '.$cat->parent->nama : 'Kategori induk' }}
                                    </p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-right tabular-nums whitespace-nowrap">
                            {{ Format::number($cat->toleransi_susut_persen, 2) }}%
                        </td>
                        <td class="px-4 py-3 text-right tabular-nums whitespace-nowrap">
                            @if ($cat->kadar_air_maks_persen !== null)
                                {{ Format::number($cat->kadar_air_maks_persen, 2) }}%
                                @if ($cat->toleransi_kadar_air_poin !== null)
                                    <span class="block text-body-sm text-on-surface-muted">±{{ Format::number($cat->toleransi_kadar_air_poin, 2) }} poin</span>
                                @endif
                            @else
                                <span class="text-on-surface-muted">Tidak diatur</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <x-ui.badge :variant="StatusMap::badge('categories.tingkat_risiko', $cat->tingkat_risiko)">
                                {{ StatusMap::label('categories.tingkat_risiko', $cat->tingkat_risiko) }}
                            </x-ui.badge>
                            @if ($cat->wajib_sertifikat_lab)
                                <span class="mt-1 block text-body-sm text-on-surface-muted">Wajib uji lab</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            @if ($cat->is_b3_prohibited)
                                <x-ui.badge variant="danger" title="Ditolak saat membuat listing baru">Dilarang (B3)</x-ui.badge>
                            @else
                                <span class="text-on-surface-muted">Non-B3</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <x-ui.badge :variant="StatusMap::badge('categories.status', $cat->status)">
                                {{ StatusMap::label('categories.status', $cat->status) }}
                            </x-ui.badge>
                        </td>
                        <td class="px-6 py-3 whitespace-nowrap">
                            <div class="flex items-center justify-end gap-4">
                                <x-ui.button variant="text" href="{{ route('admin.categories.show', $cat) }}" aria-label="Lihat {{ $cat->nama }}">Lihat</x-ui.button>
                                <x-ui.button variant="text" href="{{ route('admin.categories.edit', $cat) }}" aria-label="Edit {{ $cat->nama }}">Edit</x-ui.button>
                                @if ($cat->status === 'AKTIF')
                                    <form method="POST" action="{{ route('admin.categories.archive', $cat) }}"
                                        onsubmit="return confirm(@js('Arsipkan kategori '.$cat->nama.'?'))">
                                        @csrf
                                        <x-ui.button type="submit" variant="text-danger" aria-label="Arsipkan {{ $cat->nama }}">Arsipkan</x-ui.button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('admin.categories.unarchive', $cat) }}">
                                        @csrf
                                        <x-ui.button type="submit" variant="text" aria-label="Aktifkan kembali {{ $cat->nama }}">Aktifkan</x-ui.button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-ui.table>

            @if ($categories->hasPages())
                <div class="border-t border-outline-hairline px-6 py-4">
                    {{ $categories->links() }}
                </div>
            @endif
        @else
            <div class="px-6 pb-10 pt-4 text-center">
                @if ($filtered)
                    <p class="text-dash-sub text-on-surface">Tidak ada kategori yang cocok dengan saringan.</p>
                    <p class="mt-1 text-body-sm text-on-surface-muted">Ubah kata kunci atau saringan, atau tampilkan semua kategori.</p>
                    <div class="mt-4">
                        <x-ui.button variant="secondary" href="{{ route('admin.categories.index') }}">Tampilkan semua</x-ui.button>
                    </div>
                @else
                    <p class="text-dash-sub text-on-surface">Belum ada kategori material.</p>
                    <p class="mt-1 text-body-sm text-on-surface-muted">Tambahkan kategori pertama untuk katalog sisa produksi industri.</p>
                    <div class="mt-4">
                        <x-ui.button href="{{ route('admin.categories.create') }}">Tambah Kategori</x-ui.button>
                    </div>
                @endif
            </div>
        @endif
    </x-ui.card>
@endsection

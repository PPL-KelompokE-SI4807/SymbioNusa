@extends('layouts.app')
@use('App\Enums\InspectorStatus')
@use('App\Support\Format')

{{-- SCREENS.md 02 · Registri Inspector — daftar (PBI-01 / SYM-5) --}}

@section('title', 'Registri Inspector')

@section('breadcrumb')
    <span>Master Data</span>
    <span class="mx-2" aria-hidden="true">/</span>
    <span class="text-on-surface" aria-current="page">Registri Inspector</span>
@endsection

@php
    $filtered = request()->hasAny(['q', 'keahlian', 'wilayah', 'status']);
@endphp

@section('content')
    <x-ui.page-header title="Registri Inspector"
        subtitle="Kelola Inspector terdaftar, bidang keahlian, wilayah tugas, dan masa berlaku sertifikasi.">
        <x-slot:actions>
            <x-ui.button href="{{ route('admin.inspectors.create') }}">
                <x-ui.icon name="plus" />
                Daftarkan Inspector
            </x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.summary-strip label="Ringkasan registri Inspector" class="mb-6">
        <x-ui.kpi label="Total Inspector" :value="Format::number($stats['total'])" />
        <x-ui.kpi label="Aktif" :value="Format::number($stats['aktif'])" caption="Bisa ditugaskan" />
        <x-ui.kpi label="Sertifikat ≤ 30 hari" :value="Format::number($stats['segera_berakhir'])" caption="Perlu diperpanjang"
            :tone="$stats['segera_berakhir'] > 0 ? 'warning' : 'plain'" />
        <x-ui.kpi label="Nonaktif" :value="Format::number($stats['nonaktif'])" caption="Kedaluwarsa atau dikeluarkan" />
    </x-ui.summary-strip>

    <x-ui.card class="mb-6">
        <form method="GET" action="{{ route('admin.inspectors.index') }}" role="search" aria-label="Saring Inspector"
            class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-[minmax(0,2fr)_minmax(0,1fr)_minmax(0,1fr)_minmax(0,1fr)_auto] xl:items-end">
            <x-ui.field label="Cari" for="search-input" class="sm:col-span-2 xl:col-span-1">
                <x-ui.input id="search-input" name="q" type="search" :value="request('q')" placeholder="Nama atau nomor sertifikat…" />
            </x-ui.field>

            <x-ui.field label="Bidang keahlian" for="keahlian-filter">
                <x-ui.select id="keahlian-filter" name="keahlian">
                    <option value="">Semua keahlian</option>
                    @foreach ($filterCategories as $cat)
                        <option value="{{ $cat->id_categories }}" @selected((string) request('keahlian') === (string) $cat->id_categories)>{{ $cat->nama }}</option>
                    @endforeach
                </x-ui.select>
            </x-ui.field>

            <x-ui.field label="Wilayah tugas" for="wilayah-filter">
                <x-ui.select id="wilayah-filter" name="wilayah">
                    <option value="">Semua wilayah</option>
                    @foreach ($provinces as $prov)
                        <option value="{{ $prov }}" @selected(request('wilayah') === $prov)>{{ $prov }}</option>
                    @endforeach
                </x-ui.select>
            </x-ui.field>

            <x-ui.field label="Status" for="status-filter">
                <x-ui.select id="status-filter" name="status">
                    <option value="">Semua status</option>
                    @foreach ($statuses as $st)
                        <option value="{{ $st->value }}" @selected(request('status') === $st->value)>{{ $st->label() }}</option>
                    @endforeach
                </x-ui.select>
            </x-ui.field>

            <div class="flex items-center gap-2 sm:col-span-2 xl:col-span-1">
                <x-ui.button type="submit" variant="secondary">Terapkan</x-ui.button>
                @if ($filtered)
                    <x-ui.button variant="text" href="{{ route('admin.inspectors.index') }}" class="h-10 px-2">Reset</x-ui.button>
                @endif
            </div>
        </form>
    </x-ui.card>

    <x-ui.card title="Daftar Inspector" :padding="false">
        <x-slot:actions>
            <span class="text-body-sm text-on-surface-muted">{{ Format::number($inspectors->total()) }} Inspector</span>
        </x-slot:actions>

        @if ($inspectors->count() > 0)
            {{-- Di bawah 1536 px tabel 7 kolom (plus sidebar 240 px) tidak muat tanpa memotong kolom Aksi, jadi tampil sebagai kartu. --}}
            <ul class="divide-y divide-outline-hairline border-t border-outline-hairline 2xl:hidden" aria-label="Daftar Inspector">
                @foreach ($inspectors as $ins)
                    @php
                        $status = $ins->status($today);
                        $expertise = $ins->expertiseCategories($categories);
                        $days = $ins->daysUntilExpiry($today);
                    @endphp
                    <li class="flex flex-col gap-3 px-[18px] py-4 sm:px-6">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <a href="{{ route('admin.inspectors.show', $ins) }}"
                                    class="text-dash-row text-on-surface hover:text-primary hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus-ring">
                                    {{ $ins->user->nama }}
                                </a>
                                <p class="truncate text-body-sm text-on-surface-muted">{{ $ins->wilayah_tugas ?? '–' }}</p>
                            </div>
                            <x-ui.badge :variant="$status->badge()">{{ $status->label() }}</x-ui.badge>
                        </div>
                        @include('inspectors.partials.expertise-chips', ['expertise' => $expertise, 'limit' => 3])
                        <dl class="grid grid-cols-[auto_minmax(0,1fr)] gap-x-4 gap-y-1 text-body-sm">
                            <dt class="text-on-surface-muted">No. sertifikat</dt>
                            <dd class="text-right tabular-nums break-all text-on-surface">{{ $ins->no_sertifikat ?? '–' }}</dd>
                            <dt class="text-on-surface-muted">Berlaku sampai</dt>
                            <dd class="text-right tabular-nums text-on-surface">{{ Format::date($ins->berlaku_sampai) }}</dd>
                        </dl>
                        @include('inspectors.partials.expiry-note', ['days' => $days, 'status' => $status, 'class' => 'text-body-sm'])
                        <div class="flex flex-wrap items-center gap-x-4">
                            @include('inspectors.partials.row-actions', ['ins' => $ins, 'status' => $status, 'touch' => true])
                        </div>
                    </li>
                @endforeach
            </ul>

            <x-ui.table label="Daftar Inspector" class="hidden 2xl:block">
                <x-slot:head>
                    <th scope="col" class="px-4">Nama Inspector</th>
                    <th scope="col" class="px-4">Bidang Keahlian</th>
                    <th scope="col" class="px-4">Wilayah Tugas</th>
                    <th scope="col" class="px-4">No. Sertifikat</th>
                    <th scope="col" class="px-4">Berlaku Sampai</th>
                    <th scope="col" class="px-4">Status</th>
                    <th scope="col" class="px-4 text-right">Aksi</th>
                </x-slot:head>

                @foreach ($inspectors as $ins)
                    @php
                        $status = $ins->status($today);
                        $days = $ins->daysUntilExpiry($today);
                    @endphp
                    <tr class="h-14 border-t border-outline-hairline align-middle hover:bg-surface-muted/60">
                        <td class="px-4 py-3">
                            <a href="{{ route('admin.inspectors.show', $ins) }}"
                                class="text-dash-row text-on-surface hover:text-primary hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus-ring">
                                {{ $ins->user->nama }}
                            </a>
                        </td>
                        <td class="px-4 py-3">
                            @include('inspectors.partials.expertise-chips', ['expertise' => $ins->expertiseCategories($categories), 'limit' => 1])
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">{{ $ins->wilayah_tugas ?? '–' }}</td>
                        <td class="px-4 py-3 whitespace-nowrap tabular-nums">{{ $ins->no_sertifikat ?? '–' }}</td>
                        <td class="px-4 py-3 whitespace-nowrap tabular-nums">
                            {{ Format::date($ins->berlaku_sampai) }}
                            @include('inspectors.partials.expiry-note', ['days' => $days, 'status' => $status, 'class' => 'block text-body-sm'])
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <x-ui.badge :variant="$status->badge()">{{ $status->label() }}</x-ui.badge>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="flex items-center justify-end gap-3">
                                @include('inspectors.partials.row-actions', ['ins' => $ins, 'status' => $status, 'touch' => false])
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-ui.table>

            @if ($inspectors->hasPages())
                <div class="border-t border-outline-hairline px-6 py-4">
                    {{ $inspectors->links() }}
                </div>
            @endif
        @else
            <div class="border-t border-outline-hairline px-6 pb-10 pt-8 text-center">
                @if ($filtered)
                    <p class="text-dash-sub text-on-surface">Tidak ada Inspector yang cocok dengan saringan.</p>
                    <p class="mt-1 text-body-sm text-on-surface-muted">Ubah kata kunci atau saringan, atau tampilkan semua Inspector.</p>
                    <div class="mt-4">
                        <x-ui.button variant="secondary" href="{{ route('admin.inspectors.index') }}">Tampilkan semua</x-ui.button>
                    </div>
                @else
                    <p class="text-dash-sub text-on-surface">Belum ada Inspector terdaftar.</p>
                    <p class="mt-1 text-body-sm text-on-surface-muted">Daftarkan Inspector beserta sertifikasinya agar bisa ditugaskan memverifikasi mutu.</p>
                    <div class="mt-4">
                        <x-ui.button href="{{ route('admin.inspectors.create') }}">Daftarkan Inspector</x-ui.button>
                    </div>
                @endif
            </div>
        @endif
    </x-ui.card>
@endsection

@extends('layouts.app')

@section('title', 'RFQ & Negosiasi')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="hover:underline">Dashboard</a>
    <span class="mx-2 text-on-surface-muted" aria-hidden="true">/</span>
    <span class="text-on-surface" aria-current="page">RFQ & Negosiasi</span>
@endsection

@section('content')
    <x-ui.page-header title="RFQ & Negosiasi" subtitle="Pantau pengajuan RFQ dan utas negosiasi harga material sisa produksi.">
        @if (auth()->user()?->role === 'BUYER')
            <x-slot:actions>
                <x-ui.button href="{{ route('rfq.create') }}">
                    <x-ui.icon name="plus" />
                    Ajukan RFQ
                </x-ui.button>
            </x-slot:actions>
        @endif
    </x-ui.page-header>


    {{-- Strip ringkasan sesuai Screen 07: satu permukaan --}}
    <x-ui.summary-strip label="Ringkasan RFQ" class="mb-6">
        <x-ui.kpi label="Total RFQ" :value="$totalRfq" caption="Seluruh pengajuan RFQ" />
        <x-ui.kpi label="Terbuka / Negosiasi" :value="$terbukaNegosiasi" caption="Perlu respons/tindak lanjut" />
        <x-ui.kpi label="Disepakati" :value="$disepakati" caption="Siap diproses ke PO" />
        <x-ui.kpi label="Ditolak / Kedaluwarsa" :value="$ditolakKedaluwarsa" caption="Selesai tanpa kesepakatan" />
    </x-ui.summary-strip>

    {{-- Filter bar --}}
    <x-ui.card class="mb-6">
        <form method="GET" action="{{ route('rfq.index') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-4 items-end">
            <div>
                <x-ui.field label="Pencarian" for="q">
                    <x-ui.input id="q" name="q" :value="request('q')" placeholder="Cari No. RFQ atau material..." />
                </x-ui.field>
            </div>
            <div>
                <x-ui.field label="Status" for="status">
                    <x-ui.select id="status" name="status">
                        <option value="">Semua Status</option>
                        @foreach (\App\Enums\RfqStatus::cases() as $case)
                            <option value="{{ $case->value }}" @selected(request('status') === $case->value)>
                                {{ $case->label() }}
                            </option>
                        @endforeach
                    </x-ui.select>
                </x-ui.field>
            </div>
            <div>
                <x-ui.field label="Kategori Material" for="kategori">
                    <x-ui.select id="kategori" name="kategori">
                        <option value="">Semua Kategori</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id_categories }}" @selected(request('kategori') == $cat->id_categories)>
                                {{ $cat->nama }}
                            </option>
                        @endforeach
                    </x-ui.select>
                </x-ui.field>
            </div>
            <div class="flex items-center gap-2">
                <x-ui.button type="submit" variant="secondary">Saring</x-ui.button>
                @if (request()->hasAny(['q', 'status', 'kategori']))
                    <x-ui.button href="{{ route('rfq.index') }}" variant="text" class="h-10 px-2">Reset</x-ui.button>
                @endif
            </div>
        </form>
    </x-ui.card>

    {{-- Data Table --}}
    <x-ui.card :padding="false">
        {{-- Di bawah 1024 px tabel menjadi baris ledger (DESIGN.md › Responsive). --}}
        @if ($rfqs->count() > 0)
            <ul class="divide-y divide-outline-hairline lg:hidden" aria-label="Daftar RFQ">
                @foreach ($rfqs as $rfq)
                    @php
                        $latestOffer = $rfq->latestOffer;
                        $volumeTampil = $rfq->volume_kg_disepakati ?? $latestOffer?->volume_kg ?? 0;
                        $hargaTampil = $rfq->harga_disepakati ?? $latestOffer?->harga_per_kg ?? 0;
                        $putaranTampil = $latestOffer?->urutan ?? 1;
                        $statusValue = $rfq->status instanceof \App\Enums\RfqStatus ? $rfq->status->value : $rfq->status;
                    @endphp
                    <li class="flex items-start gap-3 px-[18px] py-4 sm:px-6">
                        <div class="min-w-0 flex-1">
                            <p class="flex flex-wrap items-baseline gap-x-2">
                                <span class="text-dash-row tabular-nums text-on-surface">RFQ-{{ str_pad((string) $rfq->id_rfq_contract, 4, '0', STR_PAD_LEFT) }}</span>
                                <span class="text-body-md font-semibold text-on-surface">{{ $rfq->listing?->judul ?? '–' }}</span>
                            </p>
                            <p class="text-body-sm text-on-surface-muted">
                                {{ auth()->user()?->role === 'SELLER' ? ($rfq->company?->nama ?? '–') : ($rfq->listing?->company?->nama ?? '–') }}
                            </p>
                            <p class="mt-1 text-body-sm tabular-nums text-on-surface">
                                {{ \App\Support\Format::number($volumeTampil) }} kg · {{ \App\Support\Format::rupiah($hargaTampil) }}/kg
                                <span class="text-on-surface-muted">· putaran {{ $putaranTampil }}/5 · tenggat {{ $rfq->tenggat ? \App\Support\Format::date($rfq->tenggat) : '–' }}</span>
                            </p>
                            <div class="mt-1.5">
                                <x-ui.badge :variant="\App\Support\StatusMap::badge('rfq_contracts.status', $statusValue)">
                                    {{ \App\Support\StatusMap::label('rfq_contracts.status', $statusValue) }}
                                </x-ui.badge>
                            </div>
                        </div>
                        <x-ui.button href="{{ route('rfq.show', $rfq) }}" variant="secondary" size="lg">Lihat</x-ui.button>
                    </li>
                @endforeach
            </ul>
        @endif

        <x-ui.table label="Tabel Daftar RFQ" class="{{ $rfqs->count() > 0 ? 'hidden lg:block' : '' }}">
            <x-slot:head>
                <th class="px-4 py-3">No. RFQ</th>
                <th class="px-4 py-3">Material</th>
                <th class="px-4 py-3">{{ auth()->user()?->role === 'SELLER' ? 'Buyer' : 'Seller' }}</th>
                <th class="px-4 py-3 text-right">Volume (kg)</th>
                <th class="px-4 py-3 text-right">Harga Diajukan</th>
                <th class="px-4 py-3 text-center">Putaran</th>
                <th class="px-4 py-3">Tenggat</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3 text-right">Aksi</th>
            </x-slot:head>

            @forelse ($rfqs as $rfq)
                @php
                    $latestOffer = $rfq->latestOffer;
                    $volumeTampil = $rfq->volume_kg_disepakati ?? $latestOffer?->volume_kg ?? 0;
                    $hargaTampil = $rfq->harga_disepakati ?? $latestOffer?->harga_per_kg ?? 0;
                    $putaranTampil = $latestOffer?->urutan ?? 1;
                    $statusValue = $rfq->status instanceof \App\Enums\RfqStatus ? $rfq->status->value : $rfq->status;
                @endphp
                <tr class="h-14 border-t border-outline-hairline hover:bg-surface-muted/60">
                    <td class="px-4 py-3 text-dash-row tabular-nums whitespace-nowrap text-on-surface">
                        RFQ-{{ str_pad((string) $rfq->id_rfq_contract, 4, '0', STR_PAD_LEFT) }}
                    </td>
                    <td class="px-4 py-3">
                        <span class="font-semibold text-on-surface">{{ $rfq->listing?->judul ?? '–' }}</span>
                        @if ($rfq->listing?->category)
                            <span class="block text-body-sm text-on-surface-muted">{{ $rfq->listing->category->nama }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-on-surface">
                        @if (auth()->user()?->role === 'SELLER')
                            {{ $rfq->company?->nama ?? '–' }}
                        @else
                            {{ $rfq->listing?->company?->nama ?? '–' }}
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right tabular-nums text-on-surface">
                        {{ \App\Support\Format::number($volumeTampil) }} kg
                    </td>
                    <td class="px-4 py-3 text-right tabular-nums text-on-surface">
                        {{ \App\Support\Format::rupiah($hargaTampil) }}/kg
                    </td>
                    <td class="px-4 py-3 text-center tabular-nums text-on-surface-muted">
                        {{ $putaranTampil }}/5
                    </td>
                    <td class="px-4 py-3 text-body-sm text-on-surface-muted whitespace-nowrap">
                        {{ $rfq->tenggat ? \App\Support\Format::date($rfq->tenggat) : '–' }}
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        <x-ui.badge :variant="\App\Support\StatusMap::badge('rfq_contracts.status', $statusValue)">
                            {{ \App\Support\StatusMap::label('rfq_contracts.status', $statusValue) }}
                        </x-ui.badge>
                    </td>
                    <td class="px-4 py-3 text-right whitespace-nowrap">
                        <x-ui.button href="{{ route('rfq.show', $rfq) }}" variant="secondary">
                            Lihat
                        </x-ui.button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="px-4 py-12 text-center">
                        <p class="text-dash-sub text-on-surface">Tidak ada RFQ yang cocok</p>
                        <p class="mt-1 text-body-sm text-on-surface-muted">Ubah pencarian atau saringan untuk melihat RFQ lain.</p>
                    </td>
                </tr>
            @endforelse
        </x-ui.table>

        @if ($rfqs->hasPages())
            <div class="border-t border-outline-hairline px-6 py-4">
                {{ $rfqs->links() }}
            </div>
        @endif
    </x-ui.card>
@endsection

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
                <x-ui.button href="{{ route('rfq.create') }}" variant="primary">
                    + Ajukan RFQ
                </x-ui.button>
            </x-slot:actions>
        @endif
    </x-ui.page-header>

    @if (session('success'))
        <x-ui.alert variant="success" class="mb-6">
            {{ session('success') }}
        </x-ui.alert>
    @endif

    {{-- KPI strip sesuai Screen 07 --}}
    <div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
        <x-ui.kpi label="Total RFQ" :value="$totalRfq" caption="Seluruh pengajuan RFQ" />
        <x-ui.kpi label="Terbuka / Negosiasi" :value="$terbukaNegosiasi" caption="Perlu respons/tindak lanjut" />
        <x-ui.kpi label="Disepakati" :value="$disepakati" caption="Siap diproses ke PO" />
        <x-ui.kpi label="Ditolak / Kedaluwarsa" :value="$ditolakKedaluwarsa" caption="Selesai tanpa kesepakatan" />
    </div>

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
                <x-ui.button type="submit" variant="primary">Saring</x-ui.button>
                @if (request()->hasAny(['q', 'status', 'kategori']))
                    <x-ui.button href="{{ route('rfq.index') }}" variant="secondary">Reset</x-ui.button>
                @endif
            </div>
        </form>
    </x-ui.card>

    {{-- Data Table --}}
    <x-ui.card padding="false">
        <x-ui.table label="Tabel Daftar RFQ">
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
                <tr class="border-t border-outline hover:bg-surface-container-lowest">
                    <td class="px-4 py-3 font-medium text-on-surface">
                        RFQ-{{ str_pad((string) $rfq->id_rfq_contract, 4, '0', STR_PAD_LEFT) }}
                    </td>
                    <td class="px-4 py-3">
                        <span class="font-medium text-on-surface">{{ $rfq->listing?->judul ?? '–' }}</span>
                        @if ($rfq->listing?->category)
                            <span class="block text-body-xs text-on-surface-muted">{{ $rfq->listing->category->nama }}</span>
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
                    <td class="px-4 py-3 text-right whitespace-nowrap space-x-2">
                        <x-ui.button href="{{ route('rfq.show', $rfq) }}" variant="secondary" size="sm">
                            Lihat
                        </x-ui.button>
                        @if (auth()->user()?->role === 'BUYER' && $statusValue === 'DISEPAKATI')
                            @if (! $rfq->purchaseOrder)
                                <x-ui.button href="{{ route('po.create', ['rfq_id' => $rfq->id_rfq_contract]) }}" variant="primary" size="sm">
                                    Buat PO
                                </x-ui.button>
                            @else
                                <x-ui.button href="{{ route('po.show', $rfq->purchaseOrder) }}" variant="secondary" size="sm">
                                    Lihat PO
                                </x-ui.button>
                            @endif
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="px-4 py-12 text-center text-on-surface-muted">
                        Tidak ada data RFQ yang cocok dengan kriteria filter.
                    </td>
                </tr>
            @endforelse
        </x-ui.table>

        @if ($rfqs->hasPages())
            <div class="border-t border-outline p-4">
                {{ $rfqs->links() }}
            </div>
        @endif
    </x-ui.card>
@endsection

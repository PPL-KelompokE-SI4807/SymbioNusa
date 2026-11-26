@extends('layouts.app')

{{-- SCREENS 24 · Pengiriman & Manifes (PBI-11 / SYM-15). Daftar pengiriman per peran, Kaca (PANDUAN_KACA §5, §6.10). --}}

@php
    use App\Enums\ShipmentStatus;
    use App\Support\Format;

    $total = array_sum($counts);
@endphp

@section('title', 'Pengiriman & Manifes')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="hover:text-on-surface">Dashboard</a>
    <span aria-hidden="true">/</span>
    <span class="text-on-surface" aria-current="page">Pengiriman</span>
@endsection

@section('content')
<x-app.kaca>
    <x-ui.page-header title="Pengiriman & Manifes" :subtitle="$canCreate ? 'Catat armada dan muatan, lalu terbitkan manifes angkut untuk order yang sudah dibayar.' : 'Pengiriman material untuk order perusahaan Anda.'">
        @if ($canCreate)
            <x-slot:actions>
                <x-ui.button :href="route('shipments.create')">
                    <x-ui.icon name="truck" class="size-[18px]" />
                    Buat pengiriman
                </x-ui.button>
            </x-slot:actions>
        @endif
    </x-ui.page-header>

    @if (session('status'))
        <x-ui.alert variant="success" class="mb-4">{{ session('status') }}</x-ui.alert>
    @endif

    <x-ui.summary-strip label="Ringkasan pengiriman" class="mb-4" data-rise style="--i: 0">
        @if ($canCreate)
            <x-ui.kpi label="Siap dikirim" :value="Format::number($readyOrders->count())" caption="Dibayar, dokumen disetujui" />
        @endif
        <x-ui.kpi label="Draf" :value="Format::number($counts['DRAF'] ?? 0)" caption="Belum terbit manifes" />
        <x-ui.kpi label="Dalam perjalanan" :value="Format::number($counts['DALAM_PERJALANAN'] ?? 0)" caption="Manifes terbit, belum tiba" />
        @unless ($canCreate)
            <x-ui.kpi label="Tiba" :value="Format::number($counts['TIBA'] ?? 0)" caption="Menunggu penerimaan Buyer" />
        @endunless
        <x-ui.kpi label="Manifes bulan ini" :value="Format::number($issuedThisMonth)" :caption="now()->locale('id')->translatedFormat('F Y')" />
    </x-ui.summary-strip>

    <div class="dash-grid grid grid-cols-1 gap-4 lg:grid-cols-12">
        @if ($canCreate && $readyOrders->isNotEmpty())
            <x-ui.card title="Siap dikirim" class="lg:col-span-12" :padding="false" data-rise style="--i: 1">
                <ul class="divide-y divide-line px-[18px] pb-2 sm:px-6">
                    @foreach ($readyOrders as $po)
                        @php($listing = $po->rfqContract?->listing)
                        <li class="flex flex-wrap items-center justify-between gap-3 py-3">
                            <div class="min-w-0">
                                <p class="truncate text-[15px] font-bold">
                                    <span class="mono">{{ $po->formattedNumber() }}</span> · {{ $listing?->judul ?? 'Material' }}
                                </p>
                                <p class="truncate text-body-sm text-on-surface-muted">
                                    {{ $po->buyer?->nama ?? 'Buyer' }} · {{ Format::kg($po->volume_kg) }}, toleransi {{ Format::kg($po->minVolumeKg()) }}–{{ Format::kg($po->maxVolumeKg()) }}
                                </p>
                            </div>
                            <x-ui.button variant="secondary" :href="route('shipments.create', ['order' => $po->id_purchase_order])">Catat pengiriman</x-ui.button>
                        </li>
                    @endforeach
                </ul>
            </x-ui.card>
        @endif

        <x-ui.card title="Daftar pengiriman" class="lg:col-span-12" :padding="false" data-rise style="--i: 2">
            <form method="GET" action="{{ route('shipments.index') }}" class="flex flex-wrap items-end gap-3 px-[18px] pb-4 sm:px-6" role="search">
                <div class="min-w-0 flex-1 basis-56">
                    <label for="q" class="sr-only">Cari pengiriman</label>
                    <x-ui.input id="q" name="q" icon="search" :value="$search" placeholder="Cari nomor manifes, PO, pelat, sopir, atau rute" />
                </div>
                <div class="basis-44">
                    <label for="status" class="sr-only">Status</label>
                    <x-ui.select id="status" name="status">
                        <option value="">Semua status ({{ $total }})</option>
                        @foreach (ShipmentStatus::cases() as $case)
                            <option value="{{ $case->value }}" @selected($status === $case)>{{ $case->label() }} ({{ $counts[$case->value] ?? 0 }})</option>
                        @endforeach
                    </x-ui.select>
                </div>
                <x-ui.button type="submit" variant="secondary">Terapkan</x-ui.button>
            </form>

            @if ($shipments->isEmpty())
                <div class="px-[18px] pb-6 sm:px-6">
                    <div class="grid place-items-center rounded-[16px] border border-dashed border-line-strong px-4 py-12 text-center">
                        @if ($search !== '' || $status)
                            <p class="text-[16px] font-bold">Tidak ada pengiriman yang cocok</p>
                            <p class="mt-1 text-[14px] text-meta">Ubah kata kunci atau status, atau <a href="{{ route('shipments.index') }}" class="font-semibold text-primary underline">lihat semua pengiriman</a>.</p>
                        @else
                            <p class="text-[16px] font-bold">Belum ada pengiriman</p>
                            <p class="mt-1 text-[14px] text-meta">
                                {{ $canCreate ? 'Pengiriman bisa dicatat setelah order dibayar dan dokumen non-B3-nya disetujui Inspector.' : 'Pengiriman muncul di sini setelah Seller menerbitkan manifes angkut.' }}
                            </p>
                        @endif
                    </div>
                </div>
            @else
                <x-ui.table label="Daftar pengiriman">
                    <x-slot:head>
                        <th class="px-[18px] sm:px-6">Manifes</th>
                        <th class="px-3">{{ $canCreate ? 'Buyer' : 'Seller' }}</th>
                        <th class="px-3">Rute</th>
                        <th class="px-3">Jadwal berangkat</th>
                        <th class="px-3 text-right">Berat dikirim</th>
                        <th class="px-3">Status</th>
                        <th class="px-[18px] text-right sm:px-6"><span class="sr-only">Aksi</span></th>
                    </x-slot:head>

                    @foreach ($shipments as $s)
                        @php($po = $s->purchaseOrder)
                        <tr class="h-14 border-t border-outline-hairline hover:bg-primary-soft">
                            <td class="whitespace-nowrap px-[18px] py-2 sm:px-6">
                                <a href="{{ route('shipments.show', $s) }}" class="font-bold text-on-surface hover:underline">{{ $s->displayNumber() }}</a>
                                <span class="block text-body-sm text-on-surface-muted mono">{{ $po->formattedNumber() }}</span>
                            </td>
                            <td class="px-3 py-2">{{ ($canCreate ? $po->buyer?->nama : $po->seller?->nama) ?? '–' }}</td>
                            <td class="max-w-56 px-3 py-2"><span class="line-clamp-2">{{ $s->rute ?? '–' }}</span></td>
                            <td class="whitespace-nowrap px-3 py-2">{{ $s->jadwal ? Format::datetime($s->jadwal) : '–' }}</td>
                            <td class="whitespace-nowrap px-3 py-2 text-right tabular-nums">{{ Format::kg($s->berat_dikirim_kg) }}</td>
                            <td class="whitespace-nowrap px-3 py-2"><x-ui.badge :variant="$s->status->badge()">{{ $s->status->label() }}</x-ui.badge></td>
                            <td class="px-[18px] py-2 text-right sm:px-6">
                                <x-ui.button variant="text" :href="route('shipments.show', $s)">Lihat</x-ui.button>
                            </td>
                        </tr>
                    @endforeach
                </x-ui.table>

                @if ($shipments->hasPages())
                    <div class="border-t border-line px-[18px] py-3 sm:px-6">{{ $shipments->links() }}</div>
                @endif
            @endif
        </x-ui.card>
    </div>
</x-app.kaca>
@endsection

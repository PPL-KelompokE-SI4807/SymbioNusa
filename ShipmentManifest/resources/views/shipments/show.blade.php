@extends('layouts.app')

@php
    use App\Enums\ShipmentStatus;
    use App\Support\ShipmentTolerance;

    $fmt = fn ($n, $d = 2) => number_format((float) $n, $d, ',', '.');
    $dt = fn ($v) => $v ? $v->format('d M Y H:i') : '-';
    $eval = $tolerance->evaluate((float) $shipment->berat_dikirim_kg);
    $steps = [ShipmentStatus::Draf, ShipmentStatus::DalamPengiriman, ShipmentStatus::Tiba];
    $currentIdx = array_search($shipment->status, $steps, true);
@endphp

@section('title', 'Detail Pengiriman')

@section('content')
    <a href="{{ route('shipments.index') }}" class="text-sm text-zinc-500 hover:text-zinc-800">&larr; Kembali ke daftar</a>

    <div class="mb-6 mt-2 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-2xl font-semibold tracking-tight">{{ $shipment->no_manifes ?? 'Draf Pengiriman #'.$shipment->id }}</h1>
                <x-status-badge :status="$shipment->status" />
            </div>
            <p class="mt-1 text-sm text-zinc-500">Order {{ $order->nomor_po }} &middot; {{ $order->material_name }}</p>
        </div>

        <div class="flex flex-wrap gap-2">
            @can('update', $shipment)
                <a href="{{ route('shipments.edit', $shipment) }}" class="btn-secondary">Ubah</a>
                <form method="POST" action="{{ route('shipments.start', $shipment) }}"
                      onsubmit="return confirm('Mulai pengiriman? Manifes akan diterbitkan dan status order menjadi Dalam Pengiriman.')">
                    @csrf
                    <button class="btn-primary">Mulai Pengiriman</button>
                </form>
            @endcan
            @can('arrive', $shipment)
                <form method="POST" action="{{ route('shipments.arrive', $shipment) }}" onsubmit="return confirm('Tandai pengiriman ini sudah tiba?')">
                    @csrf
                    <button class="btn-primary">Tandai Tiba</button>
                </form>
            @endcan
            @can('downloadManifest', $shipment)
                <a href="{{ route('shipments.manifest', $shipment) }}" target="_blank" class="btn-secondary">Unduh Manifes</a>
            @endcan
        </div>
    </div>

    {{-- Stepper --}}
    @if ($shipment->status === ShipmentStatus::Dibatalkan)
        <div class="mb-6 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            Dibatalkan pada {{ $dt($shipment->dibatalkan_at) }}@if ($shipment->alasan_batal) &mdash; {{ $shipment->alasan_batal }}@endif
        </div>
    @else
        <ol class="card mb-6 grid grid-cols-3 gap-2 p-4 text-center text-xs sm:text-sm">
            @foreach ($steps as $i => $step)
                @php $done = $i <= $currentIdx; @endphp
                <li class="flex flex-col items-center gap-2">
                    <span class="flex h-8 w-8 items-center justify-center rounded-full text-sm font-semibold {{ $done ? 'bg-emerald-600 text-white' : 'bg-zinc-100 text-zinc-500' }}">{{ $i + 1 }}</span>
                    <span class="{{ $done ? 'font-medium text-zinc-900' : 'text-zinc-500' }}">{{ $step->label() }}</span>
                </li>
            @endforeach
        </ol>
    @endif

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <section class="card p-5 sm:p-6">
                <h2 class="text-base font-semibold">Armada &amp; Rute</h2>
                <dl class="mt-4 grid gap-4 text-sm sm:grid-cols-2">
                    <div><dt class="text-zinc-500">Armada</dt><dd class="font-medium">{{ $shipment->armada }}</dd></div>
                    <div><dt class="text-zinc-500">Plat nomor</dt><dd class="font-medium">{{ $shipment->plat }}</dd></div>
                    <div><dt class="text-zinc-500">Sopir</dt><dd class="font-medium">{{ $shipment->sopir }}@if ($shipment->kontak_sopir) &middot; {{ $shipment->kontak_sopir }}@endif</dd></div>
                    <div><dt class="text-zinc-500">Jarak</dt><dd class="font-medium">{{ $shipment->jarak_km ? $fmt($shipment->jarak_km).' km' : '-' }}</dd></div>
                    <div><dt class="text-zinc-500">Jadwal berangkat</dt><dd class="font-medium">{{ $dt($shipment->jadwal_berangkat) }}</dd></div>
                    <div><dt class="text-zinc-500">Estimasi tiba</dt><dd class="font-medium">{{ $dt($shipment->estimasi_tiba) }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-zinc-500">Rute asal</dt><dd class="font-medium">{{ $shipment->rute_asal }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-zinc-500">Rute tujuan</dt><dd class="font-medium">{{ $shipment->rute_tujuan }}</dd></div>
                    @if ($shipment->catatan)
                        <div class="sm:col-span-2"><dt class="text-zinc-500">Catatan</dt><dd>{{ $shipment->catatan }}</dd></div>
                    @endif
                </dl>
            </section>

            <section class="card p-5 sm:p-6">
                <h2 class="text-base font-semibold">Surat Jalan</h2>
                @forelse ($shipment->documents as $d)
                    @if ($loop->first)<ul class="mt-3 divide-y divide-zinc-100 text-sm">@endif
                        <li class="flex items-center justify-between gap-3 py-2">
                            <a href="{{ route('shipments.documents.download', [$shipment, $d]) }}" class="truncate font-medium text-emerald-700 hover:underline">{{ $d->nama_asli }}</a>
                            <span class="text-xs text-zinc-500">{{ $d->ukuran_kb }} KB</span>
                        </li>
                    @if ($loop->last)</ul>@endif
                @empty
                    <p class="mt-3 text-sm text-zinc-500">Belum ada surat jalan terlampir.</p>
                @endforelse
            </section>
        </div>

        <div class="space-y-6">
            <section class="card p-5 sm:p-6">
                <h2 class="text-base font-semibold">Muatan</h2>
                <dl class="mt-4 space-y-3 text-sm">
                    <div class="flex justify-between"><dt class="text-zinc-500">Berat dikirim</dt><dd class="font-semibold tabular-nums">{{ $fmt($shipment->berat_dikirim_kg) }} kg</dd></div>
                    <div class="flex justify-between"><dt class="text-zinc-500">Volume nominal PO</dt><dd class="tabular-nums">{{ $fmt($order->volume_kg) }} kg</dd></div>
                    <div class="flex justify-between"><dt class="text-zinc-500">Toleransi</dt><dd>{{ $fmt($order->toleransi_tonase_persen, 1) }}%</dd></div>
                    <div class="flex justify-between"><dt class="text-zinc-500">Rentang</dt><dd class="tabular-nums">{{ $fmt($tolerance->min()) }} - {{ $fmt($tolerance->max()) }}</dd></div>
                </dl>
                @if ($eval === ShipmentTolerance::OK)
                    <p class="mt-4 rounded-md bg-emerald-50 px-3 py-2 text-xs text-emerald-800">Dalam rentang toleransi.</p>
                @elseif ($eval === ShipmentTolerance::UNDER)
                    <p class="mt-4 rounded-md bg-amber-50 px-3 py-2 text-xs text-amber-800">
                        Di bawah batas bawah. Akan dievaluasi saat penerimaan (PBI-12).
                        @if ($shipment->alasan_di_bawah_batas)<br><span class="font-medium">Alasan:</span> {{ $shipment->alasan_di_bawah_batas }}@endif
                    </p>
                @endif
            </section>

            <section class="card p-5 sm:p-6">
                <h2 class="text-base font-semibold">Para Pihak</h2>
                <dl class="mt-4 space-y-3 text-sm">
                    <div><dt class="text-zinc-500">Seller</dt><dd class="font-medium">{{ $order->seller?->nama }}</dd></div>
                    <div><dt class="text-zinc-500">Buyer</dt><dd class="font-medium">{{ $order->buyer?->nama }}</dd></div>
                    <div>
                        <dt class="text-zinc-500">Verifikasi dokumen non-B3</dt>
                        <dd class="font-medium">
                            @if ($verification)
                                Disetujui{{ $verification->inspector ? ' oleh '.$verification->inspector->name : '' }}
                                @if ($verification->verified_at) ({{ $dt($verification->verified_at) }})@endif
                            @else
                                Belum disetujui
                            @endif
                        </dd>
                    </div>
                </dl>
            </section>

            <section class="card p-5 sm:p-6">
                <h2 class="text-base font-semibold">Riwayat</h2>
                <ul class="mt-3 space-y-2 text-sm text-zinc-600">
                    <li>Dibuat: {{ $dt($shipment->created_at) }}@if ($shipment->creator) oleh {{ $shipment->creator->name }}@endif</li>
                    @if ($shipment->berangkat_at)<li>Berangkat: {{ $dt($shipment->berangkat_at) }}</li>@endif
                    @if ($shipment->tiba_at)<li>Tiba: {{ $dt($shipment->tiba_at) }}</li>@endif
                </ul>
            </section>

            @can('cancel', $shipment)
                <form method="POST" action="{{ route('shipments.cancel', $shipment) }}" class="card space-y-3 p-5"
                      onsubmit="return confirm('Batalkan draf pengiriman ini?')">
                    @csrf
                    <label for="alasan_batal" class="label">Batalkan draf</label>
                    <textarea id="alasan_batal" name="alasan_batal" rows="2" placeholder="Alasan (opsional)" class="input"></textarea>
                    <button class="btn-danger w-full">Batalkan Draf</button>
                </form>
            @endcan
        </div>
    </div>
@endsection

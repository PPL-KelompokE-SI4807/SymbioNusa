@extends('layouts.app')

{{-- SCREENS 24 · Detail pengiriman (PBI-11 / SYM-15), Kaca (PANDUAN_KACA §6.11): fakta ⅔ + aksi dan pelacak ⅓. --}}

@php
    use App\Enums\ShipmentStatus;
    use App\Services\Shipment\ShipmentService;
    use App\Support\Format;

    $listing = $order->rfqContract?->listing;
    $user = auth()->user();
    $canIssue = $user->can('issue', $shipment);
    $canEdit = $user->can('update', $shipment);
    $canArrive = $user->can('arrive', $shipment);
    $canManifest = $user->can('downloadManifest', $shipment);
    $isCancelled = $shipment->status === ShipmentStatus::DIBATALKAN;

    $steps = [ShipmentStatus::DRAF, ShipmentStatus::DALAM_PERJALANAN, ShipmentStatus::TIBA];
    $currentIndex = array_search($shipment->status, $steps, true);
@endphp

@section('title', $shipment->displayNumber().' · Pengiriman')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="hover:text-on-surface">Dashboard</a>
    <span aria-hidden="true">/</span>
    <a href="{{ route('shipments.index') }}" class="hover:text-on-surface">Pengiriman</a>
    <span aria-hidden="true">/</span>
    <span class="text-on-surface" aria-current="page">{{ $shipment->displayNumber() }}</span>
@endsection

@section('content')
<x-app.kaca>
    <x-ui.page-header :title="$shipment->displayNumber()" :subtitle="$order->formattedNumber().' · '.($listing?->judul ?? 'Material').' · '.($order->buyer?->nama ?? 'Buyer')">
        <x-slot:actions>
            <x-ui.badge :variant="$shipment->status->badge()">{{ $shipment->status->label() }}</x-ui.badge>
        </x-slot:actions>
    </x-ui.page-header>

    @if (session('status'))
        <x-ui.alert variant="success" class="mb-4">{{ session('status') }}</x-ui.alert>
    @endif
    @if ($errors->any())
        <x-ui.alert variant="danger" class="mb-4" title="Aksi belum berhasil">
            <ul class="list-disc space-y-1 pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-ui.alert>
    @endif

    <div class="dash-grid grid grid-cols-1 gap-4 lg:grid-cols-12">
        <div class="space-y-4 lg:col-span-8">
            <x-ui.card title="Data pengiriman" data-rise style="--i: 0">
                <dl class="grid gap-x-6 sm:grid-cols-2">
                    @foreach ([
                        'Armada' => $shipment->armada,
                        'Nomor pelat' => $shipment->plat,
                        'Sopir' => $shipment->sopir,
                        'Jadwal berangkat' => $shipment->jadwal ? Format::datetime($shipment->jadwal) : null,
                        'Rute' => $shipment->rute,
                        'Manifes terbit' => $issuedAt ? Format::datetime($issuedAt) : null,
                    ] as $label => $value)
                        <div class="border-b border-line py-3">
                            <dt class="text-body-sm text-on-surface-muted">{{ $label }}</dt>
                            <dd class="mt-0.5 font-semibold {{ $label === 'Nomor pelat' ? 'mono' : '' }}">{{ $value ?? '–' }}</dd>
                        </div>
                    @endforeach
                </dl>
            </x-ui.card>

            <x-ui.card title="Muatan dan toleransi" data-rise style="--i: 1">
                <dl class="grid gap-4 sm:grid-cols-3">
                    <div>
                        <dt class="text-body-sm text-on-surface-muted">Berat dikirim</dt>
                        <dd class="mt-1 text-[22px] font-black tabular-nums">{{ Format::kg($shipment->berat_dikirim_kg) }}</dd>
                    </div>
                    <div>
                        <dt class="text-body-sm text-on-surface-muted">Volume PO</dt>
                        <dd class="mt-1 text-[22px] font-black tabular-nums">{{ Format::kg($order->volume_kg) }}</dd>
                    </div>
                    <div>
                        <dt class="text-body-sm text-on-surface-muted">Rentang toleransi ({{ Format::number($order->toleransi_persen, 2) }}%)</dt>
                        <dd class="mt-1 text-[22px] font-black tabular-nums">{{ Format::number($order->minVolumeKg()) }}–{{ Format::kg($order->maxVolumeKg()) }}</dd>
                    </div>
                </dl>
                <p class="mt-4 flex items-start gap-2 border-t border-line pt-3 text-body-md">
                    @if ($weightState === ShipmentService::WEIGHT_OK)
                        <x-ui.icon name="check-circle" class="mt-0.5 size-5 shrink-0 text-success" />
                        <span>Berat dikirim berada dalam rentang toleransi PO.</span>
                    @elseif ($weightState === ShipmentService::WEIGHT_UNDER)
                        <x-ui.icon name="alert" class="mt-0.5 size-5 shrink-0 text-accent-text" />
                        <span><span class="font-bold text-accent-text">Di bawah batas bawah.</span> Selisihnya dievaluasi saat penerimaan barang (PBI-12).</span>
                    @else
                        <x-ui.icon name="x-circle" class="mt-0.5 size-5 shrink-0 text-error" />
                        <span><span class="kaca-flag text-danger font-bold">Melebihi batas atas.</span> Ubah berat sebelum manifes bisa terbit.</span>
                    @endif
                </p>
            </x-ui.card>

            <x-ui.card title="Surat jalan" data-rise style="--i: 2">
                @if ($shipment->documents->isEmpty())
                    <p class="text-body-md text-on-surface-muted">Tidak ada surat jalan yang diunggah.</p>
                @else
                    <ul class="divide-y divide-line">
                        @foreach ($shipment->documents as $doc)
                            <li class="flex items-center justify-between gap-3 py-2.5">
                                <span class="flex min-w-0 items-center gap-2">
                                    <x-ui.icon name="file" class="size-5 shrink-0 text-on-surface-muted" />
                                    <span class="truncate font-semibold">{{ $doc->fileName() }}</span>
                                </span>
                                <x-ui.button variant="secondary" :href="route('shipments.documents.download', [$shipment, $doc])" aria-label="Unduh {{ $doc->fileName() }}">
                                    <x-ui.icon name="download" class="size-[18px]" />
                                    Unduh
                                </x-ui.button>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-ui.card>
        </div>

        <div class="space-y-4 lg:col-span-4">
            @if ($canIssue || $canArrive || $canManifest || $canEdit)
                <x-ui.card title="Tindakan" data-rise style="--i: 3">
                    <div class="space-y-3">
                        @if ($canIssue)
                            <form method="POST" action="{{ route('shipments.issue', $shipment) }}" class="space-y-3"
                                  onsubmit="return confirm('Terbitkan manifes sekarang? Order akan berpindah ke Dalam Pengiriman dan data pengiriman tidak bisa diubah lagi.')">
                                @csrf
                                @if ($weightState === ShipmentService::WEIGHT_UNDER)
                                    <x-ui.field label="Alasan berat kurang" for="alasan_berat" name="alasan_berat" helper="Wajib, karena berat di bawah batas bawah toleransi." required>
                                        <x-ui.textarea id="alasan_berat" name="alasan_berat" rows="3" maxlength="500" required>{{ old('alasan_berat') }}</x-ui.textarea>
                                    </x-ui.field>
                                @endif
                                <x-ui.button type="submit" class="w-full" :disabled="$weightState === ShipmentService::WEIGHT_OVER"
                                             :title="$weightState === ShipmentService::WEIGHT_OVER ? 'Berat melebihi batas atas toleransi' : null">
                                    <x-ui.icon name="file" class="size-[18px]" />
                                    Terbitkan manifes
                                </x-ui.button>
                            </form>
                        @endif

                        @if ($canManifest)
                            <x-ui.button :variant="$canArrive ? 'secondary' : 'primary'" class="w-full" :href="route('shipments.manifest', $shipment)" target="_blank" rel="noopener">
                                <x-ui.icon name="download" class="size-[18px]" />
                                Buka manifes angkut
                            </x-ui.button>
                        @endif

                        @if ($canArrive)
                            <form method="POST" action="{{ route('shipments.arrive', $shipment) }}" onsubmit="return confirm('Tandai pengiriman ini sudah tiba di Buyer?')">
                                @csrf
                                <x-ui.button type="submit" class="w-full">
                                    <x-ui.icon name="check" class="size-[18px]" />
                                    Tandai sudah tiba
                                </x-ui.button>
                            </form>
                        @endif

                        @if ($canEdit)
                            <div class="flex flex-wrap items-center justify-between gap-2 border-t border-line pt-3">
                                <x-ui.button variant="secondary" :href="route('shipments.edit', $shipment)">Ubah draf</x-ui.button>
                                <form method="POST" action="{{ route('shipments.cancel', $shipment) }}" onsubmit="return confirm('Batalkan draf pengiriman ini?')">
                                    @csrf
                                    <x-ui.button type="submit" variant="text-danger">Batalkan draf</x-ui.button>
                                </form>
                            </div>
                        @endif
                    </div>
                </x-ui.card>
            @endif

            <x-ui.card title="Status pengiriman" data-rise style="--i: 4">
                @if ($isCancelled)
                    <p class="text-body-md">Draf ini dibatalkan dan tidak bisa dilanjutkan. Order tetap Dibayar dan bisa dicatat ulang.</p>
                @else
                    <ol class="space-y-4">
                        @foreach ($steps as $i => $step)
                            @php($done = $i < $currentIndex)
                            @php($current = $i === $currentIndex)
                            <li class="flex items-center gap-3" @if ($current) aria-current="step" @endif>
                                <span @class([
                                    'grid size-7 shrink-0 place-items-center rounded-full border-2 text-body-sm font-bold',
                                    'border-primary bg-primary text-on-primary' => $done,
                                    'border-primary text-primary' => $current,
                                    'border-line-strong text-on-surface-muted' => ! $done && ! $current,
                                ])>
                                    @if ($done)
                                        <x-ui.icon name="check" class="size-4" />
                                    @else
                                        {{ $i + 1 }}
                                    @endif
                                </span>
                                <span @class(['text-body-md', 'font-bold' => $current, 'text-on-surface-muted' => ! $done && ! $current])>{{ $step->label() }}</span>
                            </li>
                        @endforeach
                    </ol>
                @endif
            </x-ui.card>

            <x-ui.card title="Rujukan order" data-rise style="--i: 5">
                <dl class="divide-y divide-line text-body-md">
                    <div class="flex justify-between gap-4 py-2.5">
                        <dt class="text-on-surface-muted">Purchase Order</dt>
                        <dd><a href="{{ route('po.show', $order) }}" class="mono font-bold text-primary hover:underline">{{ $order->formattedNumber() }}</a></dd>
                    </div>
                    <div class="flex justify-between gap-4 py-2.5">
                        <dt class="text-on-surface-muted">Status order</dt>
                        <dd><x-ui.badge :variant="$order->status->badge()">{{ $order->status->label() }}</x-ui.badge></dd>
                    </div>
                    <div class="flex justify-between gap-4 py-2.5">
                        <dt class="text-on-surface-muted">Seller</dt>
                        <dd class="text-right font-semibold">{{ $order->seller?->nama ?? '–' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 py-2.5">
                        <dt class="text-on-surface-muted">Verifikasi dokumen</dt>
                        <dd class="text-right font-semibold">{{ $verification ? 'Disetujui'.($verification->inspector ? ' · '.$verification->inspector->nama : '') : 'Belum disetujui' }}</dd>
                    </div>
                </dl>
            </x-ui.card>
        </div>
    </div>
</x-app.kaca>
@endsection

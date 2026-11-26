@extends('layouts.app')

{{--
    SCREENS 24 · Form pengiriman (PBI-11 / SYM-15), Kaca (PANDUAN_KACA §6.9).
    Kolom mengikuti DDL shipments_manifests: armada, plat, sopir, jadwal, rute, berat_dikirim_kg; surat jalan ke shipment_documents.
    Alasan berat kurang tidak punya kolom: dicatat di riwayat status order saat manifes terbit.
--}}

@php
    use App\Services\Shipment\ShipmentService;
    use App\Support\Format;

    $isEdit = $shipment !== null;
    $orderData = $orders->mapWithKeys(fn ($po) => [$po->id_purchase_order => [
        'number' => $po->formattedNumber(),
        'material' => $po->rfqContract?->listing?->judul ?? 'Material',
        'category' => $po->rfqContract?->listing?->category?->nama,
        'buyer' => $po->buyer?->nama ?? 'Buyer',
        'volume' => Format::kg($po->volume_kg),
        'tolerance' => Format::number($po->toleransi_persen, 2).'%',
        'min' => $po->minVolumeKg(),
        'max' => $po->maxVolumeKg(),
        'minLabel' => Format::kg($po->minVolumeKg()),
        'maxLabel' => Format::kg($po->maxVolumeKg()),
    ]]);
    $weight = old('berat_dikirim_kg', $shipment?->berat_dikirim_kg);
@endphp

@section('title', $isEdit ? 'Ubah draf pengiriman' : 'Buat pengiriman')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="hover:text-on-surface">Dashboard</a>
    <span aria-hidden="true">/</span>
    <a href="{{ route('shipments.index') }}" class="hover:text-on-surface">Pengiriman</a>
    <span aria-hidden="true">/</span>
    <span class="text-on-surface" aria-current="page">{{ $isEdit ? 'Ubah draf' : 'Buat' }}</span>
@endsection

@section('content')
<x-app.kaca>
    <x-ui.page-header :title="$isEdit ? 'Ubah draf pengiriman' : 'Buat pengiriman'"
        subtitle="Lengkapi data armada dan muatan. Manifes angkut terbit saat Anda menekan Terbitkan manifes." />

    @if ($orders->isEmpty())
        <x-ui.card class="max-w-2xl">
            <div class="grid place-items-center rounded-[16px] border border-dashed border-line-strong px-4 py-12 text-center">
                <p class="text-[16px] font-bold">Belum ada order yang siap dikirim</p>
                <p class="mt-1 max-w-md text-[14px] text-meta">Order bisa dikirim setelah Buyer membayar dan Inspector menyetujui verifikasi dokumen non-B3-nya.</p>
                <x-ui.button variant="secondary" :href="route('po.index')" class="mt-4">Lihat Purchase Order</x-ui.button>
            </div>
        </x-ui.card>
    @else
        @if ($errors->any())
            <x-ui.alert variant="danger" class="mb-4" title="Pengiriman belum tersimpan">
                <ul class="list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-ui.alert>
        @endif

        <div class="dash-grid grid grid-cols-1 gap-4 lg:grid-cols-12"
             x-data="{
                orders: @js($orderData),
                orderId: @js((string) $selectedOrderId),
                weight: @js($weight !== null ? (string) $weight : ''),
                get order() { return this.orders[this.orderId] ?? null },
                get state() {
                    const w = parseFloat(this.weight);
                    if (! this.order || isNaN(w) || w <= 0) return 'empty';
                    if (w > this.order.max) return 'over';
                    if (w < this.order.min) return 'under';
                    return 'ok';
                },
             }">
            <x-ui.card title="Data pengiriman" class="lg:col-span-8" data-rise style="--i: 0">
                <form method="POST" enctype="multipart/form-data"
                      action="{{ $isEdit ? route('shipments.update', $shipment) : route('shipments.store') }}" class="space-y-5">
                    @csrf
                    @if ($isEdit)
                        @method('PUT')
                    @endif

                    @if ($isEdit)
                        <div class="rounded-[16px] border border-line bg-field px-4 py-3">
                            <p class="text-body-sm text-on-surface-muted">Order</p>
                            <p class="font-bold"><span class="mono">{{ $shipment->purchaseOrder->formattedNumber() }}</span> · {{ $orderData[$shipment->purchase_orders_id_purchase_order]['material'] }}</p>
                        </div>
                    @else
                        <x-ui.field label="Order" for="purchase_orders_id_purchase_order" name="purchase_orders_id_purchase_order" helper="Hanya order yang sudah dibayar dan dokumennya disetujui." required>
                            <x-ui.select id="purchase_orders_id_purchase_order" name="purchase_orders_id_purchase_order" x-model="orderId" required>
                                @foreach ($orders as $po)
                                    <option value="{{ $po->id_purchase_order }}" @selected((int) $selectedOrderId === $po->id_purchase_order)>
                                        {{ $po->formattedNumber() }} · {{ $orderData[$po->id_purchase_order]['material'] }} · {{ $po->buyer?->nama }}
                                    </option>
                                @endforeach
                            </x-ui.select>
                        </x-ui.field>
                    @endif

                    <div class="grid gap-5 sm:grid-cols-2">
                        <x-ui.field label="Armada" for="armada" name="armada" helper="Jenis atau nama kendaraan, mis. Hino Dutro 4 ban." required>
                            <x-ui.input id="armada" name="armada" :value="old('armada', $shipment?->armada)" maxlength="100" required />
                        </x-ui.field>
                        <x-ui.field label="Nomor pelat" for="plat" name="plat" helper="Contoh: D 1234 ABC." required>
                            <x-ui.input id="plat" name="plat" :value="old('plat', $shipment?->plat)" maxlength="20" autocapitalize="characters" required />
                        </x-ui.field>
                        <x-ui.field label="Nama sopir" for="sopir" name="sopir" required>
                            <x-ui.input id="sopir" name="sopir" :value="old('sopir', $shipment?->sopir)" maxlength="100" autocomplete="off" required />
                        </x-ui.field>
                        <x-ui.field label="Jadwal berangkat" for="jadwal" name="jadwal" required>
                            <x-ui.input id="jadwal" name="jadwal" type="datetime-local" :value="old('jadwal', $shipment?->jadwal?->format('Y-m-d\TH:i'))" required />
                        </x-ui.field>
                    </div>

                    <x-ui.field label="Rute" for="rute" name="rute" helper="Asal dan tujuan, mis. Gudang Cimahi → Pabrik Cikarang." required>
                        <x-ui.input id="rute" name="rute" :value="old('rute', $shipment?->rute)" maxlength="255" required />
                    </x-ui.field>

                    <x-ui.field label="Berat dikirim (kg)" for="berat_dikirim_kg" name="berat_dikirim_kg" required>
                        <x-slot:aside>
                            <span class="text-body-sm text-on-surface-muted" x-show="order" x-cloak>Toleransi <span class="tabular-nums" x-text="order?.minLabel + ' – ' + order?.maxLabel"></span></span>
                        </x-slot:aside>
                        <x-ui.input id="berat_dikirim_kg" name="berat_dikirim_kg" type="number" step="0.01" min="0.01" inputmode="decimal"
                                    :value="$weight" x-model="weight" required aria-describedby="berat-state" />
                    </x-ui.field>

                    <div id="berat-state" aria-live="polite">
                        <div x-show="state === 'over'" x-cloak>
                            <x-ui.alert variant="danger">Berat melebihi batas atas toleransi (<span x-text="order?.maxLabel"></span>). Kurangi muatan; pengiriman di atas batas ditolak.</x-ui.alert>
                        </div>
                        <div x-show="state === 'under'" x-cloak class="space-y-3">
                            <x-ui.alert variant="warning">Berat di bawah batas bawah toleransi (<span x-text="order?.minLabel"></span>). Pengiriman tetap boleh, tetapi alasan wajib diisi sebelum manifes terbit. Selisihnya dievaluasi saat penerimaan.</x-ui.alert>
                            <x-ui.field label="Alasan berat kurang" for="alasan_berat" name="alasan_berat" helper="Dicatat di riwayat order saat manifes terbit.">
                                <x-ui.textarea id="alasan_berat" name="alasan_berat" rows="2" maxlength="500">{{ old('alasan_berat') }}</x-ui.textarea>
                            </x-ui.field>
                        </div>
                    </div>

                    @php($docInvalid = $errors->has('dokumen') || $errors->has('dokumen.*'))
                    <x-ui.field label="Surat jalan internal" for="dokumen" name="dokumen"
                                :error="$errors->first('dokumen') ?: $errors->first('dokumen.*')"
                                helper="Opsional. PDF, JPG, atau PNG, maks. 5 MB per berkas, maks. {{ ShipmentService::MAX_DOCUMENTS }} berkas.">
                        <input type="file" id="dokumen" name="dokumen[]" multiple accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png"
                            @if ($docInvalid) aria-invalid="true" @endif
                            aria-describedby="dokumen-helper{{ $docInvalid ? ' dokumen-error' : '' }}"
                            @class([
                                'block w-full rounded-sm border border-dashed bg-surface px-3 py-3 text-body-md text-on-surface',
                                'file:mr-4 file:h-10 file:cursor-pointer file:rounded-sm file:border-0 file:bg-primary-container file:px-3 file:text-label-md file:text-on-primary-container',
                                'hover:file:bg-primary hover:file:text-on-primary',
                                'focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus-ring',
                                $docInvalid ? 'border-error' : 'border-outline-input',
                            ])>
                    </x-ui.field>

                    <div class="flex flex-wrap justify-end gap-2 border-t border-line pt-4">
                        <x-ui.button variant="text" :href="$isEdit ? route('shipments.show', $shipment) : route('shipments.index')">Batal</x-ui.button>
                        <x-ui.button type="submit" variant="secondary" name="aksi" value="draf">Simpan draf</x-ui.button>
                        <x-ui.button type="submit" name="aksi" value="terbitkan" x-bind:disabled="state === 'over'"
                                     onclick="return confirm('Terbitkan manifes sekarang? Order akan berpindah ke Dalam Pengiriman dan data pengiriman tidak bisa diubah lagi.')">
                            Terbitkan manifes
                        </x-ui.button>
                    </div>
                </form>
            </x-ui.card>

            <div class="space-y-4 lg:col-span-4">
                <x-ui.card title="Prasyarat" data-rise style="--i: 1">
                    <ul class="space-y-3 text-body-md">
                        <li class="flex items-start gap-2.5">
                            <x-ui.icon name="check-circle" class="mt-0.5 size-5 shrink-0 text-success" />
                            <span>Order sudah Dibayar</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <x-ui.icon name="check-circle" class="mt-0.5 size-5 shrink-0 text-success" />
                            <span>Verifikasi dokumen non-B3 disetujui</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <span x-show="state === 'ok' || state === 'under'"><x-ui.icon name="check-circle" class="mt-0.5 size-5 shrink-0 text-success" /></span>
                            <span x-show="state === 'over'" x-cloak><x-ui.icon name="x-circle" class="mt-0.5 size-5 shrink-0 text-error" /></span>
                            <span x-show="state === 'empty'" x-cloak><x-ui.icon name="clock" class="mt-0.5 size-5 shrink-0 text-on-surface-muted" /></span>
                            <span>Berat dikirim ≤ batas atas toleransi <span class="font-bold tabular-nums" x-text="order?.maxLabel"></span></span>
                        </li>
                    </ul>
                </x-ui.card>

                <x-ui.card title="Detail pesanan" data-rise style="--i: 2">
                    <dl class="divide-y divide-line text-body-md" x-show="order">
                        <div class="flex justify-between gap-4 py-2.5"><dt class="text-on-surface-muted">Order</dt><dd class="mono font-bold" x-text="order?.number"></dd></div>
                        <div class="flex justify-between gap-4 py-2.5"><dt class="text-on-surface-muted">Material</dt><dd class="text-right font-semibold" x-text="order?.material"></dd></div>
                        <div class="flex justify-between gap-4 py-2.5"><dt class="text-on-surface-muted">Buyer</dt><dd class="text-right font-semibold" x-text="order?.buyer"></dd></div>
                        <div class="flex justify-between gap-4 py-2.5"><dt class="text-on-surface-muted">Volume PO</dt><dd class="tabular-nums font-semibold" x-text="order?.volume"></dd></div>
                        <div class="flex justify-between gap-4 py-2.5"><dt class="text-on-surface-muted">Toleransi</dt><dd class="tabular-nums font-semibold" x-text="order?.tolerance"></dd></div>
                    </dl>
                </x-ui.card>

                @if ($isEdit && $shipment->documents->isNotEmpty())
                    <x-ui.card title="Surat jalan tersimpan" data-rise style="--i: 3">
                        <ul class="divide-y divide-line">
                            @foreach ($shipment->documents as $doc)
                                <li class="flex items-center justify-between gap-3 py-2.5">
                                    <a href="{{ route('shipments.documents.download', [$shipment, $doc]) }}" class="min-w-0 truncate font-semibold text-primary hover:underline">{{ $doc->fileName() }}</a>
                                    <form method="POST" action="{{ route('shipments.documents.destroy', [$shipment, $doc]) }}" onsubmit="return confirm('Hapus surat jalan ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <x-ui.button type="submit" variant="text-danger" aria-label="Hapus {{ $doc->fileName() }}">Hapus</x-ui.button>
                                    </form>
                                </li>
                            @endforeach
                        </ul>
                    </x-ui.card>
                @endif
            </div>
        </div>
    @endif
</x-app.kaca>
@endsection

@extends('layouts.app')

@section('title', 'Buat Purchase Order')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="hover:text-on-surface">Dashboard</a>
    <span class="text-on-surface-muted">/</span>
    <a href="{{ route('po.index') }}" class="hover:text-on-surface">Purchase Order</a>
    <span class="text-on-surface-muted">/</span>
    <span class="text-on-surface" aria-current="page">Buat PO</span>
@endsection

@section('content')
    <x-ui.page-header title="Buat Purchase Order" subtitle="Terbitkan Purchase Order resmi berdasarkan kontrak kesepakatan RFQ.">
        <x-slot:actions>
            <x-ui.button href="{{ route('po.index') }}" variant="secondary">
                &larr; Kembali
            </x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    @if ($errors->any())
        <div class="mb-6">
            <x-ui.alert variant="error">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-ui.alert>
        </div>
    @endif

    @if ($availableRfqs->isEmpty())
        <x-ui.card>
            <div class="py-8 text-center">
                <p class="text-body-lg text-on-surface-muted">
                    Tidak ada kontrak RFQ berstatus <strong>Disepakati</strong> yang siap dibuatkan Purchase Order.
                </p>
                <div class="mt-4">
                    <x-ui.button href="{{ route('rfq.index') }}" variant="primary">
                        Lihat Daftar RFQ
                    </x-ui.button>
                </div>
            </div>
        </x-ui.card>
    @else
        @php
            $initialRfqId = old('rfq_contracts_id_rfq_contract', $selectedRfq?->id_rfq_contract ?? $availableRfqs->first()?->id_rfq_contract);
            
            $rfqsJson = $availableRfqs->mapWithKeys(function ($rfq) {
                $vol = (float) ($rfq->volume_kg_disepakati ?? $rfq->latestOffer?->volume_kg ?? 0);
                $hrg = (float) ($rfq->harga_disepakati ?? $rfq->latestOffer?->harga_per_kg ?? 0);
                $total = round($vol * $hrg, 2);
                $cat = $rfq->listing?->category;
                $tolPersen = (float) ($cat?->toleransi_susut_persen ?? 0);
                $minVol = round($vol * (1 - ($tolPersen / 100)), 2);
                $maxVol = round($vol * (1 + ($tolPersen / 100)), 2);

                return [
                    (string) $rfq->id_rfq_contract => [
                        'id' => $rfq->id_rfq_contract,
                        'rfq_number' => 'RFQ-' . str_pad((string) $rfq->id_rfq_contract, 4, '0', STR_PAD_LEFT),
                        'seller' => $rfq->listing?->company?->nama ?? '–',
                        'material' => $rfq->listing?->judul ?? '–',
                        'kategori' => $cat?->nama ?? '–',
                        'volume' => $vol,
                        'volume_fmt' => \App\Support\Format::kg($vol),
                        'harga' => $hrg,
                        'harga_fmt' => \App\Support\Format::rupiah($hrg) . '/kg',
                        'total' => $total,
                        'total_fmt' => \App\Support\Format::rupiah($total),
                        'toleransi_persen' => $tolPersen,
                        'min_vol_fmt' => \App\Support\Format::kg($minVol),
                        'max_vol_fmt' => \App\Support\Format::kg($maxVol),
                        'kadar_air_maks' => $cat?->kadar_air_maks_persen !== null ? (float) $cat->kadar_air_maks_persen : null,
                        'toleransi_kadar_air_poin' => $cat?->toleransi_kadar_air_poin !== null ? (float) $cat->toleransi_kadar_air_poin : null,
                    ]
                ];
            });
        @endphp

        <form method="POST" action="{{ route('po.store') }}" 
              x-data="{
                  selectedId: '{{ $initialRfqId }}',
                  rfqs: {{ Js::from($rfqsJson) }},
                  get current() {
                      return this.rfqs[this.selectedId] || null;
                  }
              }"
              class="space-y-6">
            @csrf

            {{-- Step 1: Pilih Kontrak RFQ --}}
            <x-ui.card title="Pilih Kontrak RFQ Disepakati">
                <x-ui.field label="Kontrak RFQ" for="rfq_contracts_id_rfq_contract" name="rfq_contracts_id_rfq_contract" required>
                    <x-ui.select id="rfq_contracts_id_rfq_contract" name="rfq_contracts_id_rfq_contract" x-model="selectedId" required>
                        @foreach ($availableRfqs as $rfq)
                            @php
                                $rfqNumber = 'RFQ-' . str_pad((string) $rfq->id_rfq_contract, 4, '0', STR_PAD_LEFT);
                                $volStr = \App\Support\Format::kg($rfq->volume_kg_disepakati ?? $rfq->latestOffer?->volume_kg ?? 0);
                                $hrgStr = \App\Support\Format::rupiah($rfq->harga_disepakati ?? $rfq->latestOffer?->harga_per_kg ?? 0);
                            @endphp
                            <option value="{{ $rfq->id_rfq_contract }}">
                                {{ $rfqNumber }} · {{ $rfq->listing?->judul }} ({{ $volStr }} @ {{ $hrgStr }}/kg) — Seller: {{ $rfq->listing?->company?->nama }}
                            </option>
                        @endforeach
                    </x-ui.select>
                </x-ui.field>
            </x-ui.card>

            {{-- Step 2: Blok Rincian & Toleransi Kesepakatan (Read-Only Snapshot) --}}
            <template x-if="current">
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    {{-- Rincian Nilai & Pihak --}}
                    <x-ui.card title="Rincian Pesanan (Terkunci dari Kesepakatan)">
                        <dl class="divide-y divide-outline text-body-sm">
                            <div class="py-2.5 flex justify-between">
                                <dt class="text-on-surface-muted">Seller (Penyedia Material)</dt>
                                <dd class="font-medium text-on-surface" x-text="current.seller"></dd>
                            </div>
                            <div class="py-2.5 flex justify-between">
                                <dt class="text-on-surface-muted">Material Sisa Produksi</dt>
                                <dd class="font-medium text-on-surface">
                                    <span x-text="current.material"></span>
                                    <span class="text-body-xs text-on-surface-muted block text-right" x-text="current.kategori"></span>
                                </dd>
                            </div>
                            <div class="py-2.5 flex justify-between">
                                <dt class="text-on-surface-muted">Volume Nominal</dt>
                                <dd class="font-semibold text-on-surface tabular-nums" x-text="current.volume_fmt"></dd>
                            </div>
                            <div class="py-2.5 flex justify-between">
                                <dt class="text-on-surface-muted">Harga Satuan Disepakati</dt>
                                <dd class="font-semibold text-on-surface tabular-nums" x-text="current.harga_fmt"></dd>
                            </div>
                            <div class="py-3 flex justify-between border-t-2 border-primary/20 bg-surface-container-lowest px-3 rounded-md">
                                <dt class="font-semibold text-primary">Total Nominal PO</dt>
                                <dd class="text-headline-sm font-bold text-primary tabular-nums" x-text="current.total_fmt"></dd>
                            </div>
                        </dl>
                    </x-ui.card>

                    {{-- Snapshot Toleransi Kategori --}}
                    <x-ui.card title="Snapshot Batas Toleransi Mutu & Tonase">
                        <div class="mb-4 rounded-md bg-info-container p-3 text-body-xs text-on-info-container">
                            <p class="font-medium">&bullet; Catatan Snapshot Toleransi:</p>
                            <p class="mt-0.5">Parameter toleransi disalin permanen dari standar kategori material saat PO dibuat dan tidak akan berubah meskipun master kategori diperbarui di kemudian hari.</p>
                        </div>

                        <dl class="divide-y divide-outline text-body-sm">
                            <div class="py-2.5 flex justify-between">
                                <dt class="text-on-surface-muted">Toleransi Susut Tonase</dt>
                                <dd class="font-medium text-on-surface text-right">
                                    <span x-text="current.toleransi_persen + '%'"></span>
                                    <span class="text-body-xs text-on-surface-muted block" x-text="'Batas: ' + current.min_vol_fmt + ' – ' + current.max_vol_fmt"></span>
                                </dd>
                            </div>
                            <div class="py-2.5 flex justify-between">
                                <dt class="text-on-surface-muted">Batas Kadar Air Maksimum</dt>
                                <dd class="font-medium text-on-surface text-right">
                                    <span x-text="current.kadar_air_maks !== null ? current.kadar_air_maks + '%' : 'Tidak ditentukan'"></span>
                                    <template x-if="current.toleransi_kadar_air_poin !== null">
                                        <span class="text-body-xs text-on-surface-muted block" x-text="'(Toleransi: ' + current.toleransi_kadar_air_poin + ' poin)'"></span>
                                    </template>
                                </dd>
                            </div>
                        </dl>
                    </x-ui.card>
                </div>
            </template>

            {{-- Step 3: Pengaturan Pengiriman & Aksi --}}
            <x-ui.card title="Detail Tambahan & Pengiriman">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <x-ui.field label="Perkiraan Jarak Pengiriman (km)" for="jarak_km" name="jarak_km">
                            <x-ui.input id="jarak_km" name="jarak_km" type="number" step="0.1" min="0" :value="old('jarak_km')" placeholder="Contoh: 25.5" />
                        </x-ui.field>
                        <p class="mt-1 text-body-xs text-on-surface-muted">
                            Jarak ini akan digunakan untuk menghitung estimasi reduksi jejak karbon emisi angkutan.
                        </p>
                    </div>
                </div>

                <div class="mt-6 flex flex-col sm:flex-row justify-end gap-3 border-t border-outline pt-4">
                    <button type="submit" name="action" value="draft" 
                            class="inline-flex items-center justify-center rounded-md border border-outline bg-surface px-4 py-2 text-label-md font-medium text-on-surface hover:bg-surface-container transition-colors">
                        Simpan Draf
                    </button>
                    <button type="submit" name="action" value="send" 
                            class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-label-md font-medium text-on-primary hover:bg-primary-hover transition-colors shadow-sm">
                        Kirim ke Seller
                    </button>
                </div>
            </x-ui.card>
        </form>
    @endif
@endsection

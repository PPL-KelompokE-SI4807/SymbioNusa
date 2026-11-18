@extends('layouts.app')

@section('title', 'Verifikasi Bukti Pembayaran')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="hover:underline">Dashboard</a>
    <span class="mx-2 text-on-surface-muted" aria-hidden="true">/</span>
    <a href="{{ route('seller.invoices.index') }}" class="hover:underline">Invoice</a>
    <span class="mx-2 text-on-surface-muted" aria-hidden="true">/</span>
    <span class="text-on-surface" aria-current="page">Verifikasi Pembayaran</span>
@endsection

@section('content')
    <x-ui.page-header
        title="Verifikasi Bukti Pembayaran"
        subtitle="Periksa kesesuaian berkas transfer dan nominal pembayaran dari Buyer."
    />

    @if ($errors->any())
        <x-ui.alert variant="danger" class="mb-6">
            <p class="font-medium">Terdapat kesalahan:</p>
            <ul class="mt-1 list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-ui.alert>
    @endif

    @php
        $invoice = $payment->invoice;
        $po = $invoice?->purchaseOrder;
        $buyer = $po?->buyer;

        $nominalInvoice = (float) ($invoice?->total ?? 0);
        $nominalTransfer = (float) $payment->nominal;
        $isMatch = abs($nominalInvoice - $nominalTransfer) < 0.01;
    @endphp

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
        {{-- Kolom Kiri: Pratinjau Berkas Bukti Transfer (5 Cols) --}}
        <div class="lg:col-span-5">
            <x-ui.card>
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-base font-semibold text-on-surface">Pratinjau Bukti Transfer</h2>
                    <a
                        href="{{ route('payments.proof', $payment) }}"
                        target="_blank"
                        class="text-xs text-primary font-medium hover:underline flex items-center gap-1"
                    >
                        Buka di Tab Baru ↗
                    </a>
                </div>

                <div class="overflow-hidden rounded-md border border-outline bg-surface-alt flex items-center justify-center min-h-[360px] max-h-[520px]">
                    @php
                        $ext = strtolower(pathinfo((string) $payment->bukti_url, PATHINFO_EXTENSION));
                    @endphp

                    @if (in_array($ext, ['jpg', 'jpeg', 'png'], true))
                        <img
                            src="{{ route('payments.proof', $payment) }}"
                            alt="Bukti Transfer Pembayaran"
                            class="max-h-[500px] w-auto object-contain"
                        />
                    @else
                        {{-- PDF file embed / iframe --}}
                        <div class="p-6 text-center">
                            <x-ui.icon name="document-text" class="mx-auto h-12 w-12 text-on-surface-muted" />
                            <p class="mt-2 text-sm font-semibold text-on-surface">Dokumen PDF Bukti Transfer</p>
                            <p class="text-xs text-on-surface-muted mt-1">Dokumen tersimpan dengan format PDF.</p>
                            <div class="mt-4">
                                <x-ui.button href="{{ route('payments.proof', $payment) }}" variant="secondary" size="sm" target="_blank">
                                    Unduh / Buka Dokumen PDF
                                </x-ui.button>
                            </div>
                        </div>
                    @endif
                </div>
            </x-ui.card>
        </div>

        {{-- Kolom Tengah: Rincian & Komparasi Nominal (4 Cols) --}}
        <div class="lg:col-span-4">
            <x-ui.card class="h-full flex flex-col justify-between">
                <div>
                    <h2 class="text-base font-semibold text-on-surface mb-4">Rincian Transaksi</h2>

                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between border-b border-outline pb-2">
                            <dt class="text-on-surface-muted">No. Invoice</dt>
                            <dd class="font-semibold text-on-surface">{{ $invoice?->nomor ?? '–' }}</dd>
                        </div>
                        <div class="flex justify-between border-b border-outline pb-2">
                            <dt class="text-on-surface-muted">Perusahaan Buyer</dt>
                            <dd class="font-medium text-on-surface text-right">{{ $buyer?->nama ?? '–' }}</dd>
                        </div>
                        <div class="flex justify-between border-b border-outline pb-2">
                            <dt class="text-on-surface-muted">Tanggal Transfer</dt>
                            <dd class="text-on-surface">{{ \App\Support\Format::date($payment->tgl_transfer) }}</dd>
                        </div>
                        <div class="flex justify-between border-b border-outline pb-2">
                            <dt class="text-on-surface-muted">Rekening Asal</dt>
                            <dd class="text-on-surface text-right max-w-[200px] break-words">{{ $payment->rekening_asal ?: '–' }}</dd>
                        </div>
                    </dl>

                    {{-- Komparasi Nominal --}}
                    <div class="mt-6 rounded-md border border-outline bg-surface-alt p-4">
                        <h3 class="text-xs font-semibold uppercase tracking-wider text-on-surface-muted mb-3">
                            Komparasi Nominal
                        </h3>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-on-surface-muted">Tagihan Invoice:</span>
                                <span class="font-medium text-on-surface">{{ \App\Support\Format::rupiah($nominalInvoice) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-on-surface-muted">Nominal Transfer:</span>
                                <span class="font-bold text-on-surface">{{ \App\Support\Format::rupiah($nominalTransfer) }}</span>
                            </div>
                            <div class="border-t border-outline pt-2 flex items-center justify-between">
                                <span class="text-xs text-on-surface-muted">Indikator Kesesuaian:</span>
                                @if ($isMatch)
                                    <x-ui.badge variant="success">
                                        ✓ Sesuai (100%)
                                    </x-ui.badge>
                                @else
                                    <x-ui.badge variant="danger">
                                        ✕ Ada Selisih ({{ \App\Support\Format::rupiah(abs($nominalInvoice - $nominalTransfer)) }})
                                    </x-ui.badge>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-outline text-xs text-on-surface-muted">
                    Memverifikasi pembayaran ini akan otomatis mengubah status pesanan PO terkait menjadi <strong>Dibayar</strong>.
                </div>
            </x-ui.card>
        </div>

        {{-- Kolom Kanan: Kartu Tindakan / Aksi (3 Cols) --}}
        <div class="lg:col-span-3">
            <x-ui.card>
                <h2 class="text-base font-semibold text-on-surface mb-4">Keputusan Verifikasi</h2>

                {{-- Form Setujui / Verifikasi --}}
                <form method="POST" action="{{ route('seller.payments.processVerify', $payment) }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="status" value="VALID" />

                    <p class="text-xs text-on-surface-muted leading-relaxed">
                        Pastikan mutasi dana telah benar-benar masuk ke rekening bank perusahaan Anda sebelum menyetujui verifikasi.
                    </p>

                    <x-ui.button type="submit" variant="primary" class="w-full justify-center">
                        ✓ Verifikasi Pembayaran
                    </x-ui.button>
                </form>

                <div class="relative my-5 border-t border-outline">
                    <span class="absolute left-1/2 -top-2.5 -translate-x-1/2 bg-surface px-2 text-[10px] font-semibold uppercase tracking-wider text-on-surface-muted">
                        Atau Tolak
                    </span>
                </div>

                {{-- Form Tolak Pembayaran --}}
                <form method="POST" action="{{ route('seller.payments.processVerify', $payment) }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="status" value="DITOLAK" />

                    <div>
                        <x-ui.field label="Alasan Penolakan" for="alasan_tolak" required helper="Jelaskan alasan agar Buyer dapat mengunggah bukti yang benar.">
                            <x-ui.textarea
                                id="alasan_tolak"
                                name="alasan_tolak"
                                rows="3"
                                placeholder="Contoh: Dana belum masuk ke mutasi bank / bukti transfer buram."
                                required
                            >{{ old('alasan_tolak') }}</x-ui.textarea>
                        </x-ui.field>
                    </div>

                    <x-ui.button type="submit" variant="danger" class="w-full justify-center">
                        ✕ Tolak Pembayaran
                    </x-ui.button>
                </form>

                <div class="mt-4 pt-3 border-t border-outline text-center">
                    <x-ui.button href="{{ route('seller.invoices.index') }}" variant="ghost" size="sm" class="w-full">
                        ← Kembali ke Daftar Invoice
                    </x-ui.button>
                </div>
            </x-ui.card>
        </div>
    </div>
@endsection

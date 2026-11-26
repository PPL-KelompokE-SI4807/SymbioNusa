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
                    <h2 class="text-dash-title text-on-surface">Pratinjau Bukti Transfer</h2>
                    <a
                        href="{{ route('payments.proof', $payment) }}"
                        target="_blank"
                        class="inline-flex min-h-10 items-center gap-1 text-label-md font-semibold text-primary hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus-ring"
                    >
                        Buka di Tab Baru ↗
                    </a>
                </div>

                <div class="flex max-h-[520px] min-h-[360px] items-center justify-center overflow-hidden rounded-md border border-outline-hairline bg-surface">
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
                            <x-ui.icon name="file" class="mx-auto size-12 text-on-surface-muted" />
                            <p class="mt-2 text-dash-sub text-on-surface">Dokumen PDF bukti transfer</p>
                            <p class="mt-1 text-body-sm text-on-surface-muted">Pratinjau tidak tersedia untuk PDF; buka dokumennya untuk memeriksa.</p>
                            <div class="mt-4">
                                <x-ui.button href="{{ route('payments.proof', $payment) }}" variant="secondary" target="_blank">
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
                    <h2 class="mb-4 text-dash-title text-on-surface">Rincian Transaksi</h2>

                    <dl class="divide-y divide-outline-hairline text-body-md">
                        <div class="flex justify-between gap-4 py-2.5">
                            <dt class="text-on-surface-muted">No. Invoice</dt>
                            <dd class="font-semibold tabular-nums text-on-surface">{{ $invoice?->nomor ?? '–' }}</dd>
                        </div>
                        <div class="flex justify-between gap-4 py-2.5">
                            <dt class="text-on-surface-muted">Perusahaan Buyer</dt>
                            <dd class="font-medium text-on-surface text-right">{{ $buyer?->nama ?? '–' }}</dd>
                        </div>
                        <div class="flex justify-between gap-4 py-2.5">
                            <dt class="text-on-surface-muted">Tanggal Transfer</dt>
                            <dd class="text-on-surface">{{ \App\Support\Format::date($payment->tgl_transfer) }}</dd>
                        </div>
                        <div class="flex justify-between gap-4 py-2.5">
                            <dt class="text-on-surface-muted">Rekening Asal</dt>
                            <dd class="text-on-surface text-right max-w-[200px] break-words">{{ $payment->rekening_asal ?: '–' }}</dd>
                        </div>
                    </dl>

                    {{-- Komparasi Nominal --}}
                    <div class="mt-6 border-t border-outline-hairline pt-4">
                        <h3 class="mb-3 text-dash-sub text-on-surface">
                            Komparasi Nominal
                        </h3>
                        <div class="space-y-2 text-body-md tabular-nums">
                            <div class="flex justify-between">
                                <span class="text-on-surface-muted">Tagihan Invoice:</span>
                                <span class="font-medium text-on-surface">{{ \App\Support\Format::rupiah($nominalInvoice) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-on-surface-muted">Nominal Transfer:</span>
                                <span class="font-bold text-on-surface">{{ \App\Support\Format::rupiah($nominalTransfer) }}</span>
                            </div>
                            <div class="border-t border-outline-hairline pt-2 flex items-center justify-between">
                                <span class="text-body-sm text-on-surface-muted">Kesesuaian</span>
                                @if ($isMatch)
                                    <x-ui.badge variant="success">
                                        Sesuai
                                    </x-ui.badge>
                                @else
                                    <x-ui.badge variant="danger">
                                        Selisih ({{ \App\Support\Format::rupiah(abs($nominalInvoice - $nominalTransfer)) }})
                                    </x-ui.badge>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 border-t border-outline-hairline pt-3 text-body-sm text-on-surface-muted">
                    Memverifikasi pembayaran ini akan otomatis mengubah status pesanan PO terkait menjadi <strong>Dibayar</strong>.
                </div>
            </x-ui.card>
        </div>

        {{-- Kolom Kanan: Kartu Tindakan / Aksi (3 Cols) --}}
        <div class="lg:col-span-3">
            <x-ui.card>
                <h2 class="mb-4 text-dash-title text-on-surface">Keputusan verifikasi</h2>

                {{-- Form Setujui / Verifikasi --}}
                <form method="POST" action="{{ route('seller.payments.processVerify', $payment) }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="status" value="VALID" />

                    <p class="text-body-sm text-on-surface-muted">
                        Pastikan mutasi dana telah benar-benar masuk ke rekening bank perusahaan Anda sebelum menyetujui verifikasi.
                    </p>

                    <x-ui.button type="submit" variant="primary" class="w-full justify-center">
                        <x-ui.icon name="check" class="size-[18px]" />
                        Verifikasi Pembayaran
                    </x-ui.button>
                </form>

                <div class="relative my-6 border-t border-outline-hairline">
                    <span class="absolute left-1/2 -top-2.5 -translate-x-1/2 bg-surface px-2 text-label-sm font-semibold text-on-surface-muted">
                        atau tolak
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
                        <x-ui.icon name="x" class="size-[18px]" />
                        Tolak Pembayaran
                    </x-ui.button>
                </form>

                <div class="mt-4 pt-3 border-t border-outline-hairline text-center">
                    <x-ui.button href="{{ route('seller.invoices.index') }}" variant="text" class="min-h-10 w-full">
                        <x-ui.icon name="arrow-left" class="size-4" />
                        Kembali ke daftar invoice
                    </x-ui.button>
                </div>
            </x-ui.card>
        </div>
    </div>
@endsection

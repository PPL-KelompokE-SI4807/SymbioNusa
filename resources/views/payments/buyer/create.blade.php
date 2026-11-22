@extends('layouts.app')

@section('title', 'Unggah Bukti Pembayaran')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="hover:underline">Dashboard</a>
    <span class="mx-2 text-on-surface-muted" aria-hidden="true">/</span>
    <a href="{{ route('buyer.invoices.index') }}" class="hover:underline">Invoice & Pembayaran</a>
    <span class="mx-2 text-on-surface-muted" aria-hidden="true">/</span>
    <span class="text-on-surface" aria-current="page">Unggah Bukti</span>
@endsection

@section('content')
    <x-ui.page-header
        title="Unggah Bukti Pembayaran"
        subtitle="Kirimkan bukti transfer bank untuk diverifikasi oleh Seller sebelum material dikirim."
    />

    @if ($errors->any())
        <x-ui.alert variant="danger" class="mb-6">
            <p class="font-medium">Terdapat kesalahan pada isian formulir:</p>
            <ul class="mt-1 list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-ui.alert>
    @endif

    @if ($invoice->latestPayment && ($invoice->latestPayment->status_verifikasi?->value === 'DITOLAK' || $invoice->latestPayment->status_verifikasi === 'DITOLAK'))
        <x-ui.alert variant="danger" class="mb-6">
            <p class="font-semibold">Pembayaran Sebelumnya Ditolak</p>
            <p class="text-sm mt-1">Alasan: {{ $invoice->latestPayment->alasan_tolak ?: 'Bukti transfer tidak sesuai ketentuan.' }}</p>
            <p class="text-xs text-on-surface-muted mt-1">Silakan lakukan transfer ulang atau perbaiki bukti pembayaran di bawah ini.</p>
        </x-ui.alert>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Kolom Kiri: Formulir Unggah Bukti --}}
        <div class="lg:col-span-2">
            <x-ui.card>
                <h2 class="mb-4 text-dash-title text-on-surface">Formulir Bukti Pembayaran</h2>

                <form method="POST" action="{{ route('buyer.invoices.store', $invoice) }}" enctype="multipart/form-data" class="space-y-5">
                    @csrf

                    <div>
                        <x-ui.field label="Tanggal Transfer" for="tgl_transfer" required helper="Tanggal saat Anda melakukan transaksi di bank / ATM / mobile banking.">
                            <x-ui.input
                                type="date"
                                id="tgl_transfer"
                                name="tgl_transfer"
                                :value="old('tgl_transfer', now()->toDateString())"
                                required
                            />
                        </x-ui.field>
                    </div>

                    <div>
                        <x-ui.field
                            label="Nominal Transfer (Rp)"
                            for="nominal"
                            required
                            helper="Nominal harus sama persis dengan total tagihan invoice: {{ \App\Support\Format::rupiah($invoice->total) }}"
                        >
                            <x-ui.input
                                type="number"
                                step="0.01"
                                id="nominal"
                                name="nominal"
                                :value="old('nominal', (float) $invoice->total)"
                                required
                            />
                        </x-ui.field>
                    </div>

                    <div class="border-t border-outline-hairline pt-4">
                        <h3 class="mb-3 text-dash-sub text-on-surface">Rekening asal (pengirim)</h3>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                            <div>
                                <x-ui.field label="Nama Bank" for="bank_asal" required>
                                    <x-ui.input
                                        id="bank_asal"
                                        name="bank_asal"
                                        :value="old('bank_asal')"
                                        placeholder="Contoh: BCA / Mandiri / BNI"
                                        required
                                    />
                                </x-ui.field>
                            </div>
                            <div>
                                <x-ui.field label="Nomor Rekening" for="nomor_rekening_asal" required>
                                    <x-ui.input
                                        id="nomor_rekening_asal"
                                        name="nomor_rekening_asal"
                                        :value="old('nomor_rekening_asal')"
                                        placeholder="1234567890"
                                        required
                                    />
                                </x-ui.field>
                            </div>
                            <div>
                                <x-ui.field label="Nama Pemilik Rekening" for="nama_rekening_asal" required>
                                    <x-ui.input
                                        id="nama_rekening_asal"
                                        name="nama_rekening_asal"
                                        :value="old('nama_rekening_asal')"
                                        placeholder="Nama sesuai buku tabungan"
                                        required
                                    />
                                </x-ui.field>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-outline-hairline pt-4">
                        <x-ui.field
                            label="Berkas Bukti Transfer"
                            for="bukti"
                            required
                            helper="Format berkas: JPG, JPEG, PNG, atau PDF. Ukuran maksimal 5 MB."
                        >
                            <input
                                type="file"
                                id="bukti"
                                name="bukti"
                                accept=".jpg,.jpeg,.png,.pdf"
                                class="block w-full rounded-sm border border-dashed border-outline-input bg-surface px-3 py-3 text-body-md text-on-surface file:mr-4 file:h-10 file:cursor-pointer file:rounded-sm file:border-0 file:bg-primary-container file:px-3 file:text-label-md file:font-semibold file:text-on-primary-container hover:file:bg-primary hover:file:text-on-primary focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus-ring"
                                required
                            />
                        </x-ui.field>
                    </div>

                    <div class="text-body-sm text-on-surface-muted">
                        <strong>Catatan:</strong> Bukti transfer hanya dapat diganti sebelum diverifikasi valid oleh Seller. Pastikan bukti terlihat jelas dan memuat stempel/referensi transaksi bank.
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <x-ui.button href="{{ route('buyer.invoices.index') }}" variant="secondary">
                            Batal
                        </x-ui.button>
                        <x-ui.button type="submit" variant="primary">
                            Kirim untuk Verifikasi
                        </x-ui.button>
                    </div>
                </form>
            </x-ui.card>
        </div>

        {{-- Kolom Kanan: Ringkasan Invoice --}}
        <div>
            <x-ui.card>
                <h2 class="mb-4 text-dash-title text-on-surface">Ringkasan Invoice</h2>

                <dl class="divide-y divide-outline-hairline text-body-md">
                    <div class="flex justify-between gap-4 py-2.5">
                        <dt class="text-on-surface-muted">Nomor Invoice</dt>
                        <dd class="font-semibold tabular-nums text-on-surface">{{ $invoice->nomor }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 py-2.5">
                        <dt class="text-on-surface-muted">Pihak Seller</dt>
                        <dd class="font-medium text-on-surface text-right">{{ $invoice->purchaseOrder?->seller?->nama ?? '–' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 py-2.5">
                        <dt class="text-on-surface-muted">Jatuh Tempo</dt>
                        <dd class="text-on-surface">{{ \App\Support\Format::date($invoice->jatuh_tempo) }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 py-2.5">
                        <dt class="text-on-surface-muted">Total Tagihan</dt>
                        <dd class="text-dash-sub tabular-nums text-on-surface">{{ \App\Support\Format::rupiah($invoice->total) }}</dd>
                    </div>
                </dl>

                <div class="mt-5 border-t border-outline-hairline pt-4">
                    <h3 class="mb-2 text-label-md font-bold text-on-surface">Instruksi pembayaran</h3>
                    <p class="text-body-sm text-on-surface-muted">
                        Silakan lakukan pembayaran manual via transfer antar-bank ke rekening resmi Seller yang tertera pada dokumen kontrak PO. Simpan struk/tangkapan layar mutasi untuk diunggah di formulir ini.
                    </p>
                </div>
            </x-ui.card>
        </div>
    </div>
@endsection

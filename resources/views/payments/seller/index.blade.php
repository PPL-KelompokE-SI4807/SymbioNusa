@extends('layouts.app')

@section('title', 'Invoice & Verifikasi Pembayaran')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="hover:underline">Dashboard</a>
    <span class="mx-2 text-on-surface-muted" aria-hidden="true">/</span>
    <span class="text-on-surface" aria-current="page">Invoice</span>
@endsection

@section('content')
    <x-ui.page-header
        title="Invoice & Verifikasi Pembayaran"
        subtitle="Pantau status tagihan invoice dan verifikasi bukti transfer pembayaran dari Buyer."
    />

    @if (session('success'))
        <x-ui.alert variant="success" class="mb-6">
            {{ session('success') }}
        </x-ui.alert>
    @endif

    {{-- Strip ringkasan: satu permukaan (data dari controller) --}}
    <x-ui.summary-strip label="Ringkasan invoice" class="mb-6">
        <x-ui.kpi label="Total Invoice" :value="$kpi['total']" caption="Invoice yang diterbitkan" />
        <x-ui.kpi label="Perlu Verifikasi" :value="$kpi['menunggu']" caption="Bukti bayar masuk" :tone="$kpi['menunggu'] > 0 ? 'warning' : 'plain'" />
        <x-ui.kpi label="Sudah Lunas" :value="$kpi['lunas']" caption="Pembayaran terverifikasi" />
        <x-ui.kpi label="Bukti Ditolak" :value="$kpi['ditolak']" caption="Menunggu kirim ulang" />
    </x-ui.summary-strip>

    <x-ui.card :padding="false">
        <x-ui.table label="Daftar Invoice dan Pembayaran Seller">
            <x-slot:head>
                <th scope="col" class="px-4 py-3 text-left">No. Invoice</th>
                <th scope="col" class="px-4 py-3 text-left">Buyer</th>
                <th scope="col" class="px-4 py-3 text-left">Total Tagihan</th>
                <th scope="col" class="px-4 py-3 text-left">Tgl. Transfer</th>
                <th scope="col" class="px-4 py-3 text-left">Status Invoice</th>
                <th scope="col" class="px-4 py-3 text-left">Verifikasi Bayar</th>
                <th scope="col" class="px-4 py-3 text-right">Aksi</th>
            </x-slot:head>

            @forelse ($invoices as $invoice)
                @php
                    $payment = $invoice->latestPayment;
                    $po = $invoice->purchaseOrder;
                    $isMenunggu = $payment && ($payment->status_verifikasi?->value === 'MENUNGGU' || $payment->status_verifikasi === 'MENUNGGU');
                @endphp
                <tr class="h-14 border-t border-outline-hairline hover:bg-surface-muted/60">
                    <td class="px-4 py-3 text-dash-row tabular-nums text-on-surface">
                        {{ $invoice->nomor }}
                    </td>
                    <td class="px-4 py-3 text-on-surface">
                        {{ $po?->buyer?->nama ?? '–' }}
                    </td>
                    <td class="px-4 py-3 font-semibold tabular-nums text-on-surface">
                        {{ \App\Support\Format::rupiah($invoice->total) }}
                    </td>
                    <td class="px-4 py-3 text-on-surface-muted">
                        {{ $payment?->tgl_transfer ? \App\Support\Format::date($payment->tgl_transfer) : '–' }}
                    </td>
                    <td class="px-4 py-3">
                        <x-ui.badge :variant="\App\Support\StatusMap::badge('invoices.status', $invoice->status)">
                            {{ \App\Support\StatusMap::label('invoices.status', $invoice->status) }}
                        </x-ui.badge>
                    </td>
                    <td class="px-4 py-3">
                        @if ($payment)
                            <x-ui.badge :variant="$payment->status_verifikasi instanceof \App\Enums\PaymentVerificationStatus ? $payment->status_verifikasi->badge() : \App\Support\StatusMap::badge('payments.status_verifikasi', $payment->status_verifikasi)">
                                {{ $payment->status_verifikasi instanceof \App\Enums\PaymentVerificationStatus ? $payment->status_verifikasi->label() : \App\Support\StatusMap::label('payments.status_verifikasi', $payment->status_verifikasi) }}
                            </x-ui.badge>
                        @else
                            <span class="text-xs text-on-surface-muted">Belum ada bukti</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right">
                        @if ($isMenunggu)
                            <x-ui.button href="{{ route('seller.payments.verify', $payment) }}" variant="secondary">
                                Verifikasi
                            </x-ui.button>
                        @elseif ($payment)
                            <x-ui.button href="{{ route('payments.proof', $payment) }}" variant="text" target="_blank" class="min-h-10">
                                Lihat Bukti
                            </x-ui.button>
                        @else
                            <span class="text-on-surface-muted text-xs">–</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-4 py-10 text-center">
                        <p class="text-dash-sub text-on-surface">Belum ada invoice</p>
                        <p class="mt-1 text-body-sm text-on-surface-muted">Invoice terbit otomatis setelah order lolos inspeksi.</p>
                    </td>
                </tr>
            @endforelse
        </x-ui.table>

        @if ($invoices->hasPages())
            <div class="border-t border-outline-hairline px-6 py-4">
                {{ $invoices->links() }}
            </div>
        @endif
    </x-ui.card>
@endsection

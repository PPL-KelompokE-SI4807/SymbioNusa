@extends('layouts.app')

@section('title', 'Invoice & Pembayaran')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="hover:underline">Dashboard</a>
    <span class="mx-2 text-on-surface-muted" aria-hidden="true">/</span>
    <span class="text-on-surface" aria-current="page">Invoice & Pembayaran</span>
@endsection

@section('content')
    <x-ui.page-header title="Invoice & Pembayaran" subtitle="Kelola tagihan invoice dan pantau status verifikasi pembayaran transfer Anda." />

    @if (session('success'))
        <x-ui.alert variant="success" class="mb-6">
            {{ session('success') }}
        </x-ui.alert>
    @endif

    <x-ui.card padding="false">
        <x-ui.table label="Daftar Invoice & Pembayaran Buyer">
            <x-slot:head>
                <th scope="col" class="px-4 py-3 text-left">No. Invoice</th>
                <th scope="col" class="px-4 py-3 text-left">Seller</th>
                <th scope="col" class="px-4 py-3 text-left">Total Tagihan</th>
                <th scope="col" class="px-4 py-3 text-left">Jatuh Tempo</th>
                <th scope="col" class="px-4 py-3 text-left">Status Invoice</th>
                <th scope="col" class="px-4 py-3 text-left">Status Pembayaran</th>
                <th scope="col" class="px-4 py-3 text-right">Aksi</th>
            </x-slot:head>

            @forelse ($invoices as $invoice)
                @php
                    $payment = $invoice->latestPayment;
                    $po = $invoice->purchaseOrder;
                @endphp
                <tr class="border-t border-outline hover:bg-surface-alt/50 transition">
                    <td class="px-4 py-3 font-semibold text-on-surface">
                        {{ $invoice->nomor }}
                    </td>
                    <td class="px-4 py-3 text-on-surface">
                        {{ $po?->seller?->nama ?? '–' }}
                    </td>
                    <td class="px-4 py-3 font-medium text-on-surface tabular-nums">
                        {{ \App\Support\Format::rupiah($invoice->total) }}
                    </td>
                    <td class="px-4 py-3 text-on-surface-muted">
                        {{ \App\Support\Format::date($invoice->jatuh_tempo) }}
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
                            <span class="text-xs text-on-surface-muted">Belum bayar</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right">
                        @if ($invoice->status === 'TERBIT')
                            @if (! $payment || $payment->status_verifikasi?->value === 'DITOLAK' || $payment->status_verifikasi === 'DITOLAK')
                                <x-ui.button href="{{ route('buyer.invoices.pay', $invoice) }}" variant="primary" size="sm">
                                    {{ $payment ? 'Unggah Ulang' : 'Bayar Sekarang' }}
                                </x-ui.button>
                            @elseif ($payment->status_verifikasi?->value === 'MENUNGGU' || $payment->status_verifikasi === 'MENUNGGU')
                                <div class="flex items-center justify-end gap-2">
                                    <x-ui.button href="{{ route('buyer.invoices.pay', $invoice) }}" variant="secondary" size="sm">
                                        Ganti Bukti
                                    </x-ui.button>
                                    <x-ui.button href="{{ route('payments.proof', $payment) }}" variant="ghost" size="sm" target="_blank">
                                        Lihat Bukti
                                    </x-ui.button>
                                </div>
                            @endif
                        @elseif ($payment)
                            <x-ui.button href="{{ route('payments.proof', $payment) }}" variant="secondary" size="sm" target="_blank">
                                Lihat Bukti
                            </x-ui.button>
                        @else
                            <span class="text-on-surface-muted text-xs">–</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-4 py-8 text-center text-on-surface-muted">
                        Belum ada invoice yang diterbitkan untuk perusahaan Anda.
                    </td>
                </tr>
            @endforelse
        </x-ui.table>

        @if ($invoices->hasPages())
            <div class="p-4 border-t border-outline">
                {{ $invoices->links() }}
            </div>
        @endif
    </x-ui.card>
@endsection

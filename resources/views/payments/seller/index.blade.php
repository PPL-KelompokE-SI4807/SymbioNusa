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

    {{-- KPI Strip Ringkasan --}}
    @php
        $totalCount = $invoices->total();
        $menungguCount = $invoices->filter(fn($i) => $i->latestPayment && ($i->latestPayment->status_verifikasi?->value === 'MENUNGGU' || $i->latestPayment->status_verifikasi === 'MENUNGGU'))->count();
        $lunasCount = $invoices->filter(fn($i) => $i->status === 'DIBAYAR')->count();
        $ditolakCount = $invoices->filter(fn($i) => $i->latestPayment && ($i->latestPayment->status_verifikasi?->value === 'DITOLAK' || $i->latestPayment->status_verifikasi === 'DITOLAK'))->count();
    @endphp
    <div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
        <x-ui.kpi label="Total Invoice" :value="$totalCount" caption="Invoice yang diterbitkan" />
        <x-ui.kpi label="Perlu Verifikasi" :value="$menungguCount" caption="Bukti bayar masuk" />
        <x-ui.kpi label="Sudah Lunas" :value="$lunasCount" caption="Pembayaran terverifikasi" />
        <x-ui.kpi label="Bukti Ditolak" :value="$ditolakCount" caption="Menunggu kirim ulang" />
    </div>

    <x-ui.card>
        <x-ui.table label="Daftar Invoice dan Pembayaran Seller">
            <thead>
                <tr>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-on-surface-muted">No. Invoice</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-on-surface-muted">Buyer</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-on-surface-muted">Total Tagihan</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-on-surface-muted">Tgl. Transfer</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-on-surface-muted">Status Invoice</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-on-surface-muted">Verifikasi Bayar</th>
                    <th scope="col" class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-on-surface-muted">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-outline">
                @forelse ($invoices as $invoice)
                    @php
                        $payment = $invoice->latestPayment;
                        $po = $invoice->purchaseOrder;
                        $isMenunggu = $payment && ($payment->status_verifikasi?->value === 'MENUNGGU' || $payment->status_verifikasi === 'MENUNGGU');
                    @endphp
                    <tr class="hover:bg-surface-alt/50 transition {{ $isMenunggu ? 'bg-warning/5' : '' }}">
                        <td class="px-4 py-3 font-semibold text-on-surface">
                            {{ $invoice->nomor }}
                        </td>
                        <td class="px-4 py-3 text-on-surface">
                            {{ $po?->buyer?->nama ?? '–' }}
                        </td>
                        <td class="px-4 py-3 font-medium text-on-surface">
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
                                <x-ui.button href="{{ route('seller.payments.verify', $payment) }}" variant="primary" size="sm">
                                    Verifikasi Pembayaran
                                </x-ui.button>
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
                            Belum ada invoice yang diterbitkan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </x-ui.table>

        @if ($invoices->hasPages())
            <div class="mt-4 border-t border-outline pt-4">
                {{ $invoices->links() }}
            </div>
        @endif
    </x-ui.card>
@endsection

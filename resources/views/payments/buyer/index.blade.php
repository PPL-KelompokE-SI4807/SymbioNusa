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

    <x-ui.card :padding="false">
        {{-- Di bawah 1024 px tabel menjadi baris ledger (DESIGN.md › Responsive); tabel tetap untuk layar lebar. --}}
        @if ($invoices->count() > 0)
            <ul class="divide-y divide-outline-hairline lg:hidden" aria-label="Daftar invoice dan pembayaran">
                @foreach ($invoices as $invoice)
                    @php
                        $payment = $invoice->latestPayment;
                        $po = $invoice->purchaseOrder;
                        $ditolak = $payment && ($payment->status_verifikasi?->value === 'DITOLAK' || $payment->status_verifikasi === 'DITOLAK');
                        $menunggu = $payment && ($payment->status_verifikasi?->value === 'MENUNGGU' || $payment->status_verifikasi === 'MENUNGGU');
                    @endphp
                    <li class="flex items-start gap-3 px-[18px] py-4 sm:px-6">
                        <div class="min-w-0 flex-1">
                            <p class="flex flex-wrap items-baseline gap-x-2">
                                <span class="text-dash-row tabular-nums text-on-surface">{{ $invoice->nomor }}</span>
                                <span class="font-semibold tabular-nums text-on-surface">{{ \App\Support\Format::rupiah($invoice->total) }}</span>
                            </p>
                            <p class="text-body-sm text-on-surface-muted">
                                {{ $po?->seller?->nama ?? '–' }} · jatuh tempo {{ \App\Support\Format::date($invoice->jatuh_tempo) }}
                            </p>
                            <div class="mt-1.5 flex flex-wrap gap-x-4 gap-y-1">
                                <x-ui.badge :variant="\App\Support\StatusMap::badge('invoices.status', $invoice->status)">
                                    {{ \App\Support\StatusMap::label('invoices.status', $invoice->status) }}
                                </x-ui.badge>
                                @if ($payment)
                                    <x-ui.badge :variant="$payment->status_verifikasi instanceof \App\Enums\PaymentVerificationStatus ? $payment->status_verifikasi->badge() : \App\Support\StatusMap::badge('payments.status_verifikasi', $payment->status_verifikasi)">
                                        {{ $payment->status_verifikasi instanceof \App\Enums\PaymentVerificationStatus ? $payment->status_verifikasi->label() : \App\Support\StatusMap::label('payments.status_verifikasi', $payment->status_verifikasi) }}
                                    </x-ui.badge>
                                @else
                                    <span class="text-body-sm text-on-surface-muted">Belum bayar</span>
                                @endif
                            </div>
                        </div>
                        <div class="flex shrink-0 flex-col items-end gap-1">
                            @if ($invoice->status === 'TERBIT' && (! $payment || $ditolak))
                                <x-ui.button href="{{ route('buyer.invoices.pay', $invoice) }}" variant="secondary" size="lg">{{ $payment ? 'Unggah Ulang' : 'Bayar Sekarang' }}</x-ui.button>
                            @elseif ($invoice->status === 'TERBIT' && $menunggu)
                                <x-ui.button href="{{ route('buyer.invoices.pay', $invoice) }}" variant="secondary" size="lg">Ganti Bukti</x-ui.button>
                                <x-ui.button href="{{ route('payments.proof', $payment) }}" variant="text" target="_blank" class="min-h-11">Lihat Bukti</x-ui.button>
                            @elseif ($invoice->status !== 'TERBIT' && $payment)
                                <x-ui.button href="{{ route('payments.proof', $payment) }}" variant="text" target="_blank" class="min-h-11">Lihat Bukti</x-ui.button>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif

        <x-ui.table label="Daftar Invoice & Pembayaran Buyer" class="{{ $invoices->count() > 0 ? 'hidden lg:block' : '' }}">
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
                <tr class="h-14 border-t border-outline-hairline hover:bg-surface-muted/60">
                    <td class="px-4 py-3 text-dash-row tabular-nums text-on-surface">
                        {{ $invoice->nomor }}
                    </td>
                    <td class="px-4 py-3 text-on-surface">
                        {{ $po?->seller?->nama ?? '–' }}
                    </td>
                    <td class="px-4 py-3 font-semibold tabular-nums text-on-surface">
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
                                <x-ui.button href="{{ route('buyer.invoices.pay', $invoice) }}" variant="secondary">
                                    {{ $payment ? 'Unggah Ulang' : 'Bayar Sekarang' }}
                                </x-ui.button>
                            @elseif ($payment->status_verifikasi?->value === 'MENUNGGU' || $payment->status_verifikasi === 'MENUNGGU')
                                <div class="flex items-center justify-end gap-2">
                                    <x-ui.button href="{{ route('buyer.invoices.pay', $invoice) }}" variant="secondary">
                                        Ganti Bukti
                                    </x-ui.button>
                                    <x-ui.button href="{{ route('payments.proof', $payment) }}" variant="text" target="_blank" class="min-h-10">
                                        Lihat Bukti
                                    </x-ui.button>
                                </div>
                            @endif
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
                        <p class="mt-1 text-body-sm text-on-surface-muted">Invoice dari Seller muncul di sini setelah order Anda lolos inspeksi.</p>
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

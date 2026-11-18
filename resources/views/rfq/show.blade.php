@extends('layouts.app')

@section('title', 'Detail RFQ-' . str_pad((string) $rfq->id_rfq_contract, 4, '0', STR_PAD_LEFT))

@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="hover:underline">Dashboard</a>
    <span class="mx-2 text-on-surface-muted" aria-hidden="true">/</span>
    <a href="{{ route('rfq.index') }}" class="hover:underline">RFQ & Negosiasi</a>
    <span class="mx-2 text-on-surface-muted" aria-hidden="true">/</span>
    <span class="text-on-surface" aria-current="page">RFQ-{{ str_pad((string) $rfq->id_rfq_contract, 4, '0', STR_PAD_LEFT) }}</span>
@endsection

@section('content')
    @php
        $statusValue = $rfq->status instanceof \App\Enums\RfqStatus ? $rfq->status->value : $rfq->status;
        $latestOffer = $rfq->latestOffer;
        $user = auth()->user();
        $isMyOffer = $latestOffer && $user && $latestOffer->companies_id_company === $user->id_company;
        $canRespond = $user && $user->can('respond', $rfq);
        $canCounter = $user && $user->can('counter', $rfq);
        $canWithdraw = $user && $user->can('withdraw', $rfq);
    @endphp

    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-headline-lg text-on-surface">
                    RFQ-{{ str_pad((string) $rfq->id_rfq_contract, 4, '0', STR_PAD_LEFT) }}
                </h1>
                <x-ui.badge :variant="\App\Support\StatusMap::badge('rfq_contracts.status', $statusValue)">
                    {{ \App\Support\StatusMap::label('rfq_contracts.status', $statusValue) }}
                </x-ui.badge>
            </div>
            <p class="mt-1 text-body-md text-on-surface-muted">
                Material: <strong class="text-on-surface">{{ $rfq->listing?->judul ?? '–' }}</strong> &bull;
                Seller: {{ $rfq->listing?->company?->nama ?? '–' }} &bull;
                Buyer: {{ $rfq->company?->nama ?? '–' }}
            </p>
        </div>

        <div class="flex items-center gap-2">
            <x-ui.button href="{{ route('rfq.index') }}" variant="secondary">
                &larr; Kembali ke Daftar
            </x-ui.button>
        </div>
    </div>

    @if (session('success'))
        <x-ui.alert variant="success" class="mb-6">
            {{ session('success') }}
        </x-ui.alert>
    @endif

    @if ($errors->any())
        <x-ui.alert variant="danger" class="mb-6">
            {{ $errors->first() }}
        </x-ui.alert>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Kolom Kiri: Utas Negosiasi & Riwayat Tawaran --}}
        <div class="space-y-6 lg:col-span-2">
            {{-- Banner Kesepakatan bila status DISEPAKATI --}}
            @if ($statusValue === 'DISEPAKATI')
                <div class="rounded-md border border-success bg-success-container p-5 text-on-success-container">
                    <h3 class="text-headline-sm font-semibold">Kontrak RFQ Telah Disepakati</h3>
                    <p class="mt-1 text-body-sm">
                        Kedua belah pihak telah menyepakati volume 
                        <strong>{{ \App\Support\Format::number($rfq->volume_kg_disepakati) }} kg</strong> dengan harga 
                        <strong>{{ \App\Support\Format::rupiah($rfq->harga_disepakati) }}/kg</strong> 
                        (Total: <strong>{{ \App\Support\Format::rupiah((float)$rfq->volume_kg_disepakati * (float)$rfq->harga_disepakati) }}</strong>).
                    </p>
                    <p class="mt-2 text-body-xs text-on-success-container">
                        Volume dan harga terkunci untuk pembuatan Purchase Order resmi.
                    </p>
                </div>
            @endif

            {{-- Banner Ditolak --}}
            @if ($statusValue === 'DITOLAK')
                <div class="rounded-md border border-error bg-error-container p-5 text-on-error-container">
                    <h3 class="text-headline-sm font-semibold">Penawaran RFQ Ditolak</h3>
                    <p class="mt-1 text-body-sm">
                        Negosiasi RFQ ini telah dihentikan karena salah satu pihak menolak tawaran.
                    </p>
                </div>
            @endif

            {{-- Banner Kedaluwarsa --}}
            @if ($statusValue === 'KEDALUWARSA' || ($rfq->isExpired() && in_array($statusValue, ['TERBUKA', 'NEGOSIASI'])))
                <div class="rounded-md border border-outline bg-surface-container-lowest p-5 text-on-surface">
                    <h3 class="text-headline-sm font-semibold text-on-surface-muted">RFQ Kedaluwarsa</h3>
                    <p class="mt-1 text-body-sm text-on-surface-muted">
                        Batas tenggat waktu negosiasi telah berakhir. Tidak ada penawaran lebih lanjut yang dapat diproses.
                    </p>
                </div>
            @endif

            {{-- Utas Negosiasi Kronologis --}}
            <x-ui.card title="Utas Negosiasi Penawaran ({{ $rfq->offers->count() }}/5 Putaran)">
                <div class="space-y-4">
                    @forelse ($rfq->offers as $offer)
                        @php
                            $isCurrentCompany = $user && $offer->companies_id_company === $user->id_company;
                        @endphp
                        <div class="rounded-md border p-4 {{ $isCurrentCompany ? 'border-primary/40 bg-surface-container-lowest' : 'border-outline bg-surface' }}">
                            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-outline pb-2">
                                <div class="flex items-center gap-2">
                                    <span class="rounded bg-primary/10 px-2 py-0.5 text-label-xs font-semibold text-primary">
                                        Putaran #{{ $offer->urutan }}
                                    </span>
                                    <strong class="text-label-md text-on-surface">
                                        {{ $offer->company?->nama ?? 'Perusahaan' }}
                                        @if ($isCurrentCompany)
                                            <span class="text-body-xs text-primary font-normal">(Perusahaan Anda)</span>
                                        @endif
                                    </strong>
                                </div>
                                <span class="text-body-xs text-on-surface-muted">
                                    {{ $offer->company?->tipe ?? '' }}
                                </span>
                            </div>

                            <div class="mt-3 grid grid-cols-2 gap-4 sm:grid-cols-3">
                                <div>
                                    <span class="block text-body-xs text-on-surface-muted">Volume Diajukan</span>
                                    <span class="text-headline-sm font-semibold tabular-nums text-on-surface">
                                        {{ \App\Support\Format::number($offer->volume_kg) }} kg
                                    </span>
                                </div>
                                <div>
                                    <span class="block text-body-xs text-on-surface-muted">Harga Satuan</span>
                                    <span class="text-headline-sm font-semibold tabular-nums text-on-surface">
                                        {{ \App\Support\Format::rupiah($offer->harga_per_kg) }}/kg
                                    </span>
                                </div>
                                <div>
                                    <span class="block text-body-xs text-on-surface-muted">Estimasi Total</span>
                                    <span class="text-headline-sm font-semibold tabular-nums text-primary">
                                        {{ \App\Support\Format::rupiah((float)$offer->volume_kg * (float)$offer->harga_per_kg) }}
                                    </span>
                                </div>
                            </div>

                            @if ($offer->catatan)
                                <div class="mt-3 border-t border-outline/50 pt-2 text-body-sm text-on-surface">
                                    <span class="text-on-surface-muted">Catatan:</span> {{ $offer->catatan }}
                                </div>
                            @endif
                        </div>
                    @empty
                        <p class="text-body-sm text-on-surface-muted">Belum ada riwayat tawaran.</p>
                    @endforelse
                </div>
            </x-ui.card>
        </div>

        {{-- Kolom Kanan: Ringkasan & Kartu Tindakan --}}
        <div class="space-y-6">
            {{-- Kartu Info Kontrak --}}
            <x-ui.card title="Informasi RFQ">
                <div class="space-y-3 text-body-sm">
                    <div class="flex justify-between border-b border-outline pb-2">
                        <span class="text-on-surface-muted">Status</span>
                        <x-ui.badge :variant="\App\Support\StatusMap::badge('rfq_contracts.status', $statusValue)">
                            {{ \App\Support\StatusMap::label('rfq_contracts.status', $statusValue) }}
                        </x-ui.badge>
                    </div>
                    <div class="flex justify-between border-b border-outline pb-2">
                        <span class="text-on-surface-muted">Tenggat Negosiasi</span>
                        <span class="font-medium text-on-surface tabular-nums">
                            {{ $rfq->tenggat ? \App\Support\Format::date($rfq->tenggat) : '–' }}
                        </span>
                    </div>
                    <div class="flex justify-between border-b border-outline pb-2">
                        <span class="text-on-surface-muted">Stok Listing Tersedia</span>
                        <span class="font-medium text-on-surface tabular-nums">
                            {{ \App\Support\Format::number($rfq->listing?->stok_kg ?? 0) }} kg
                        </span>
                    </div>
                    <div class="flex justify-between border-b border-outline pb-2">
                        <span class="text-on-surface-muted">Harga Listing Patokan</span>
                        <span class="font-medium text-on-surface tabular-nums">
                            {{ \App\Support\Format::rupiah($rfq->listing?->harga_per_kg ?? 0) }}/kg
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-on-surface-muted">Putaran Berjalan</span>
                        <span class="font-medium text-on-surface tabular-nums">
                            {{ $latestOffer?->urutan ?? 1 }} dari 5
                        </span>
                    </div>
                </div>
            </x-ui.card>

            {{-- Kartu Aksi Tanggapan --}}
            <x-ui.card title="Tanggapi Penawaran">
                @if ($canRespond)
                    <div class="space-y-6" x-data="{ mode: 'counter' }">
                        <p class="text-body-sm text-on-surface">
                            Giliran Anda menanggapi penawaran terakhir dari <strong>{{ $latestOffer?->company?->nama }}</strong> 
                            ({{ \App\Support\Format::number($latestOffer?->volume_kg) }} kg @ {{ \App\Support\Format::rupiah($latestOffer?->harga_per_kg) }}/kg).
                        </p>

                        {{-- Tombol Terima Penawaran --}}
                        <form method="POST" action="{{ route('rfq.accept', $rfq) }}" class="border-b border-outline pb-5">
                            @csrf
                            <x-ui.button type="submit" variant="primary" class="w-full justify-center">
                                &check; Terima Penawaran Ini (Disepakati)
                            </x-ui.button>
                            <p class="mt-1 text-center text-body-xs text-on-surface-muted">
                                Menerima tawaran akan mengunci volume & harga menjadi kontrak kesepakatan.
                            </p>
                        </form>

                        {{-- Form Penawaran Balik --}}
                        @if ($canCounter)
                            <div>
                                <h4 class="mb-3 text-label-md font-semibold text-on-surface">Ajukan Penawaran Balik</h4>
                                <form method="POST" action="{{ route('rfq.counter', $rfq) }}" class="space-y-4">
                                    @csrf
                                    <div>
                                        <x-ui.field label="Volume Tawar Balik (kg)" for="counter_volume" name="volume_kg" required>
                                            <x-ui.input id="counter_volume" name="volume_kg" type="number" step="0.01" min="1" :value="old('volume_kg', $latestOffer?->volume_kg)" required />
                                        </x-ui.field>
                                    </div>
                                    <div>
                                        <x-ui.field label="Harga Tawar Balik (Rp/kg)" for="counter_harga" name="harga_per_kg" required>
                                            <x-ui.input id="counter_harga" name="harga_per_kg" type="number" step="0.01" min="1" :value="old('harga_per_kg', $latestOffer?->harga_per_kg)" required />
                                        </x-ui.field>
                                    </div>
                                    <div>
                                        <x-ui.field label="Catatan Balasan (Opsional)" for="counter_catatan" name="catatan">
                                            <x-ui.textarea id="counter_catatan" name="catatan" rows="2" placeholder="Alasan penyesuaian harga/volume...">{{ old('catatan') }}</x-ui.textarea>
                                        </x-ui.field>
                                    </div>
                                    <x-ui.button type="submit" variant="secondary" class="w-full justify-center">
                                        Kirim Penawaran Balik (Putaran #{{ ($latestOffer?->urutan ?? 0) + 1 }})
                                    </x-ui.button>
                                </form>
                            </div>
                        @else
                            <div class="rounded bg-neutral-container p-3 text-body-xs text-on-neutral-container">
                                Batas maksimal negosiasi (5 putaran) telah tercapai. Anda hanya dapat menerima atau menolak tawaran.
                            </div>
                        @endif

                        {{-- Form Tolak Penawaran --}}
                        <div class="border-t border-outline pt-4">
                            <form method="POST" action="{{ route('rfq.reject', $rfq) }}" class="space-y-3">
                                @csrf
                                <x-ui.field label="Alasan Penolakan" for="alasan_penolakan" name="alasan_penolakan" required>
                                    <x-ui.input id="alasan_penolakan" name="alasan_penolakan" placeholder="Mengapa tawaran ditolak?" required />
                                </x-ui.field>
                                <x-ui.button type="submit" variant="danger" class="w-full justify-center">
                                    Tolak Penawaran RFQ
                                </x-ui.button>
                            </form>
                        </div>
                    </div>
                @elseif ($isMyOffer && in_array($statusValue, ['TERBUKA', 'NEGOSIASI']) && ! $rfq->isExpired())
                    <div class="space-y-4">
                        <div class="rounded bg-info-container p-4 text-body-sm text-on-info-container">
                            <strong class="block font-semibold">Menunggu Tanggapan Lawan</strong>
                            Penawaran terakhir dikirim oleh pihak Anda. Menunggu pihak lawan untuk menerima, menawar balik, atau menolak.
                        </div>

                        @if ($canWithdraw)
                            <form method="POST" action="{{ route('rfq.withdraw', $rfq) }}">
                                @csrf
                                <x-ui.button type="submit" variant="secondary" class="w-full justify-center text-error border-error/30 hover:bg-error-container" onclick="return confirm('Apakah Anda yakin ingin menarik pengajuan RFQ ini?')">
                                    Tarik Pengajuan RFQ
                                </x-ui.button>
                            </form>
                        @endif
                    </div>
                @else
                    <p class="text-body-sm text-on-surface-muted">
                        Tidak ada tindakan tanggapan yang tersedia untuk akun Anda pada status ini.
                    </p>
                @endif
            </x-ui.card>
        </div>
    </div>
@endsection

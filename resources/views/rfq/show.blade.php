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
                <h1 class="page-title text-on-surface">
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
                <x-ui.icon name="arrow-left" class="size-4" />
                Kembali ke daftar
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
                <x-ui.alert variant="success" title="Kontrak RFQ telah disepakati">
                    <p class="mt-1">
                        Kedua belah pihak telah menyepakati volume 
                        <strong>{{ \App\Support\Format::number($rfq->volume_kg_disepakati) }} kg</strong> dengan harga 
                        <strong>{{ \App\Support\Format::rupiah($rfq->harga_disepakati) }}/kg</strong> 
                        (Total: <strong>{{ \App\Support\Format::rupiah((float)$rfq->volume_kg_disepakati * (float)$rfq->harga_disepakati) }}</strong>).
                    </p>
                    <p class="mt-2 text-body-sm">
                        Volume dan harga terkunci untuk pembuatan Purchase Order resmi.
                    </p>
                </x-ui.alert>
            @endif

            {{-- Banner Ditolak --}}
            @if ($statusValue === 'DITOLAK')
                <x-ui.alert variant="danger" title="Penawaran RFQ ditolak">
                    Negosiasi RFQ ini telah dihentikan karena salah satu pihak menolak tawaran.
                </x-ui.alert>
            @endif

            {{-- Banner Kedaluwarsa --}}
            @if ($statusValue === 'KEDALUWARSA' || ($rfq->isExpired() && in_array($statusValue, ['TERBUKA', 'NEGOSIASI'])))
                <x-ui.alert variant="warning" title="RFQ kedaluwarsa">
                    Batas tenggat waktu negosiasi telah berakhir. Tidak ada penawaran lebih lanjut yang dapat diproses.
                </x-ui.alert>
            @endif

            {{-- Utas Negosiasi Kronologis --}}
            <x-ui.card title="Utas negosiasi · {{ $rfq->offers->count() }}/5 putaran">
                <ol class="divide-y divide-outline-hairline border-t border-outline-hairline">
                    @forelse ($rfq->offers as $offer)
                        @php
                            $isCurrentCompany = $user && $offer->companies_id_company === $user->id_company;
                        @endphp
                        <li class="py-4">
                            <div class="flex flex-wrap items-baseline justify-between gap-2">
                                <div class="flex flex-wrap items-baseline gap-x-3 gap-y-1">
                                    <span class="text-label-sm font-bold tabular-nums text-on-surface-muted">
                                        Putaran {{ $offer->urutan }}
                                    </span>
                                    <strong class="text-dash-row text-on-surface">
                                        {{ $offer->company?->nama ?? 'Perusahaan' }}
                                        @if ($isCurrentCompany)
                                            <span class="text-body-sm font-semibold text-primary">· Perusahaan Anda</span>
                                        @endif
                                    </strong>
                                </div>
                                <span class="text-body-sm text-on-surface-muted">
                                    {{ $offer->company?->tipe ?? '' }}
                                </span>
                            </div>

                            <div class="mt-3 grid grid-cols-2 gap-4 sm:grid-cols-3">
                                <div>
                                    <span class="block text-body-sm text-on-surface-muted">Volume Diajukan</span>
                                    <span class="text-dash-sub tabular-nums text-on-surface">
                                        {{ \App\Support\Format::number($offer->volume_kg) }} kg
                                    </span>
                                </div>
                                <div>
                                    <span class="block text-body-sm text-on-surface-muted">Harga Satuan</span>
                                    <span class="text-dash-sub tabular-nums text-on-surface">
                                        {{ \App\Support\Format::rupiah($offer->harga_per_kg) }}/kg
                                    </span>
                                </div>
                                <div>
                                    <span class="block text-body-sm text-on-surface-muted">Estimasi Total</span>
                                    <span class="text-dash-sub tabular-nums text-on-surface">
                                        {{ \App\Support\Format::rupiah((float)$offer->volume_kg * (float)$offer->harga_per_kg) }}
                                    </span>
                                </div>
                            </div>

                            @if ($offer->catatan)
                                <p class="mt-3 text-body-sm text-on-surface">
                                    <span class="text-on-surface-muted">Catatan:</span> {{ $offer->catatan }}
                                </p>
                            @endif
                        </li>
                    @empty
                        <li class="py-4 text-body-sm text-on-surface-muted">Belum ada riwayat tawaran.</li>
                    @endforelse
                </ol>
            </x-ui.card>
        </div>

        {{-- Kolom Kanan: Ringkasan & Kartu Tindakan --}}
        <div class="space-y-6">
            {{-- Kartu Info Kontrak --}}
            <x-ui.card title="Informasi RFQ">
                <div class="space-y-3 text-body-sm">
                    <div class="flex justify-between border-b border-outline-hairline pb-2">
                        <span class="text-on-surface-muted">Status</span>
                        <x-ui.badge :variant="\App\Support\StatusMap::badge('rfq_contracts.status', $statusValue)">
                            {{ \App\Support\StatusMap::label('rfq_contracts.status', $statusValue) }}
                        </x-ui.badge>
                    </div>
                    <div class="flex justify-between border-b border-outline-hairline pb-2">
                        <span class="text-on-surface-muted">Tenggat Negosiasi</span>
                        <span class="font-medium text-on-surface tabular-nums">
                            {{ $rfq->tenggat ? \App\Support\Format::date($rfq->tenggat) : '–' }}
                        </span>
                    </div>
                    <div class="flex justify-between border-b border-outline-hairline pb-2">
                        <span class="text-on-surface-muted">Stok Listing Tersedia</span>
                        <span class="font-medium text-on-surface tabular-nums">
                            {{ \App\Support\Format::number($rfq->listing?->stok_kg ?? 0) }} kg
                        </span>
                    </div>
                    <div class="flex justify-between border-b border-outline-hairline pb-2">
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
                        <form method="POST" action="{{ route('rfq.accept', $rfq) }}" class="border-b border-outline-hairline pb-5">
                            @csrf
                            <x-ui.button type="submit" variant="primary" class="w-full justify-center">
                                <x-ui.icon name="check" class="size-[18px]" />
                                Terima Penawaran Ini
                            </x-ui.button>
                            <p class="mt-1 text-center text-body-sm text-on-surface-muted">
                                Menerima tawaran akan mengunci volume & harga menjadi kontrak kesepakatan.
                            </p>
                        </form>

                        {{-- Form Penawaran Balik --}}
                        @if ($canCounter)
                            <div>
                                <h3 class="mb-3 text-dash-sub text-on-surface">Ajukan penawaran balik</h3>
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
                            <x-ui.alert variant="info">
                                Batas maksimal negosiasi (5 putaran) telah tercapai. Anda hanya dapat menerima atau menolak tawaran.
                            </x-ui.alert>
                        @endif

                        {{-- Form Tolak Penawaran --}}
                        <div class="border-t border-outline-hairline pt-4">
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
                        <x-ui.alert variant="info" title="Menunggu tanggapan lawan">
                            Penawaran terakhir dikirim oleh pihak Anda. Menunggu pihak lawan untuk menerima, menawar balik, atau menolak.
                        </x-ui.alert>

                        @if ($canWithdraw)
                            <form method="POST" action="{{ route('rfq.withdraw', $rfq) }}">
                                @csrf
                                <x-ui.button type="submit" variant="danger" class="w-full justify-center" onclick="return confirm('Apakah Anda yakin ingin menarik pengajuan RFQ ini?')">
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

@php
    use App\Support\Format;

    $listing = $order->rfqContract?->listing;
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manifes Angkut {{ $shipment->no_manifes }} · {{ config('app.name', 'SymbioNusa') }}</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css'])
    @endif
    <style>
        @page { size: A4; margin: 16mm; }
        @media print { .no-print { display: none !important; } }
    </style>
</head>
{{--
    Manifes Angkut PBI-11 (SYM-15): dokumen cetak, bukan layar Kaca. "Cetak / Simpan PDF" memakai dialog cetak peramban
    (barryvdh/laravel-dompdf belum terpasang di proyek; menambah dependensi menunggu keputusan tim).
--}}
<body class="bg-background text-on-surface antialiased print:bg-surface">
    <main class="mx-auto max-w-[800px] bg-surface p-6 sm:p-10 print:max-w-none print:p-0">
        <div class="no-print mb-6 flex flex-wrap items-center justify-between gap-3">
            <a href="{{ route('shipments.show', $shipment) }}" class="text-label-md font-semibold text-primary hover:underline">&larr; Kembali ke pengiriman</a>
            <x-ui.button onclick="window.print()">
                <x-ui.icon name="download" class="size-[18px]" />
                Cetak / Simpan PDF
            </x-ui.button>
        </div>

        <header class="flex flex-wrap items-start justify-between gap-4 border-b-2 border-on-surface pb-4">
            <div>
                <p class="text-label-md font-semibold text-on-surface-muted">SymbioNusa · Platform B2B Ekonomi Sirkular</p>
                <h1 class="mt-1 text-[28px] font-black tracking-tight">Manifes Angkut</h1>
            </div>
            <div class="text-right">
                <p class="mono text-[18px] font-black">{{ $shipment->no_manifes }}</p>
                <p class="text-body-sm text-on-surface-muted">Terbit {{ $issuedAt ? Format::datetime($issuedAt) : '–' }}</p>
            </div>
        </header>

        @php
            $sections = [
                'Para pihak' => [
                    'Pengirim (Seller)' => trim(($order->seller?->nama ?? '–').($order->seller?->nib ? ' · NIB '.$order->seller->nib : '')),
                    'Alamat pengirim' => $order->seller?->alamat ?? '–',
                    'Penerima (Buyer)' => trim(($order->buyer?->nama ?? '–').($order->buyer?->nib ? ' · NIB '.$order->buyer->nib : '')),
                    'Alamat penerima' => $order->buyer?->alamat ?? '–',
                ],
                'Material' => [
                    'Purchase Order' => $order->formattedNumber(),
                    'Material (non-B3)' => trim(($listing?->judul ?? '–').($listing?->category ? ' · '.$listing->category->nama : '')),
                    'Volume PO' => Format::kg($order->volume_kg).' (toleransi '.Format::number($order->toleransi_persen, 2).'%, rentang '.Format::number($order->minVolumeKg()).'–'.Format::kg($order->maxVolumeKg()).')',
                    'Berat dikirim' => Format::kg($shipment->berat_dikirim_kg),
                ],
                'Kendaraan dan rute' => [
                    'Armada' => $shipment->armada ?? '–',
                    'Nomor pelat' => $shipment->plat ?? '–',
                    'Sopir' => $shipment->sopir ?? '–',
                    'Jadwal berangkat' => $shipment->jadwal ? Format::datetime($shipment->jadwal) : '–',
                    'Rute' => $shipment->rute ?? '–',
                ],
                'Rujukan verifikasi' => [
                    'Verifikasi dokumen non-B3' => $verification ? 'Disetujui'.($verification->inspector ? ' oleh '.$verification->inspector->nama : '') : 'Tidak tercatat',
                    'Status pembayaran order' => 'Dibayar',
                ],
            ];
        @endphp

        @foreach ($sections as $heading => $rows)
            <section class="mt-6 break-inside-avoid">
                <h2 class="text-label-md font-bold">{{ $heading }}</h2>
                <dl class="mt-2 border-t border-outline">
                    @foreach ($rows as $label => $value)
                        <div class="grid grid-cols-[180px_1fr] gap-4 border-b border-outline py-2 text-body-md max-sm:grid-cols-1 max-sm:gap-0.5">
                            <dt class="text-on-surface-muted">{{ $label }}</dt>
                            <dd class="font-semibold">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>
            </section>
        @endforeach

        <p class="mt-6 text-body-sm text-on-surface-muted">
            Dokumen ini diterbitkan sistem sebagai catatan perpindahan material. Verifikasi platform bukan pengganti penetapan atau persetujuan instansi berwenang.
        </p>

        <section class="mt-10 grid grid-cols-3 gap-6 text-center text-body-md break-inside-avoid" aria-label="Tanda tangan">
            @foreach (['Seller' => $order->seller?->nama, 'Sopir' => $shipment->sopir, 'Buyer (penerima)' => $order->buyer?->nama] as $role => $name)
                <div>
                    <p>{{ $role }}</p>
                    <div class="mt-16 border-t border-on-surface pt-1 font-semibold">{{ $name ?? '–' }}</div>
                </div>
            @endforeach
        </section>
    </main>
</body>
</html>

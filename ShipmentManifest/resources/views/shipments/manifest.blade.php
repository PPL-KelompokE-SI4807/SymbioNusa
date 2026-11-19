<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Manifes Angkut {{ $shipment->no_manifes }}</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 11px; color: #18181b; margin: 0; padding: 24px; }
        h1 { font-size: 18px; margin: 0 0 2px; }
        .muted { color: #71717a; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #d4d4d8; padding: 6px 8px; text-align: left; vertical-align: top; }
        th { background: #f4f4f5; width: 30%; font-weight: 600; }
        h2 { font-size: 12px; margin: 16px 0 0; text-transform: uppercase; letter-spacing: .04em; }
        .head td { border: none; padding: 0; }
        .sign td { height: 70px; text-align: center; vertical-align: bottom; }
        .noprint { margin-bottom: 16px; }
        @media print { .noprint { display: none; } }
    </style>
</head>
<body>
    @unless ($asPdf)
        <div class="noprint"><button onclick="window.print()">Cetak / Simpan PDF</button></div>
    @endunless

    @php $fmt = fn ($n) => number_format((float) $n, 2, ',', '.'); @endphp

    <table class="head">
        <tr>
            <td>
                <h1>MANIFES ANGKUT</h1>
                <div class="muted">SymbioNusa - Platform B2B Ekonomi Sirkular</div>
            </td>
            <td style="text-align:right">
                <strong style="font-size:14px">{{ $shipment->no_manifes }}</strong><br>
                <span class="muted">Terbit: {{ optional($shipment->berangkat_at)->format('d M Y H:i') }} WIB</span>
            </td>
        </tr>
    </table>

    <h2>Para Pihak</h2>
    <table>
        <tr><th>Pengirim (Seller)</th><td>{{ $order->seller?->nama }}<br><span class="muted">NIB {{ $order->seller?->nib }} &middot; {{ $order->seller?->alamat }}</span></td></tr>
        <tr><th>Penerima (Buyer)</th><td>{{ $order->buyer?->nama }}<br><span class="muted">NIB {{ $order->buyer?->nib }} &middot; {{ $order->buyer?->alamat }}</span></td></tr>
    </table>

    <h2>Material</h2>
    <table>
        <tr><th>Nomor PO</th><td>{{ $order->nomor_po }}</td></tr>
        <tr><th>Material (non-B3)</th><td>{{ $order->material_name }}</td></tr>
        <tr><th>Volume nominal PO</th><td>{{ $fmt($order->volume_kg) }} kg (toleransi {{ $fmt($order->toleransi_tonase_persen) }}%, rentang {{ $fmt($tolerance->min()) }} - {{ $fmt($tolerance->max()) }} kg)</td></tr>
        <tr><th>Berat dikirim</th><td><strong>{{ $fmt($shipment->berat_dikirim_kg) }} kg</strong></td></tr>
    </table>

    <h2>Kendaraan &amp; Rute</h2>
    <table>
        <tr><th>Armada / Plat nomor</th><td>{{ $shipment->armada }} / {{ $shipment->plat }}</td></tr>
        <tr><th>Sopir</th><td>{{ $shipment->sopir }} {{ $shipment->kontak_sopir ? '('.$shipment->kontak_sopir.')' : '' }}</td></tr>
        <tr><th>Jadwal berangkat</th><td>{{ $shipment->jadwal_berangkat->format('d M Y H:i') }} WIB</td></tr>
        <tr><th>Estimasi tiba</th><td>{{ $shipment->estimasi_tiba->format('d M Y H:i') }} WIB</td></tr>
        <tr><th>Rute</th><td>{{ $shipment->rute_asal }} &rarr; {{ $shipment->rute_tujuan }}{{ $shipment->jarak_km ? ' ('.$fmt($shipment->jarak_km).' km)' : '' }}</td></tr>
    </table>

    <h2>Rujukan Verifikasi</h2>
    <table>
        <tr><th>Verifikasi dokumen non-B3</th><td>{{ $verification ? 'Disetujui' : 'Tidak tercatat' }}{{ $verification?->inspector ? ' oleh '.$verification->inspector->name : '' }}{{ $verification?->verified_at ? ' pada '.$verification->verified_at->format('d M Y') : '' }}</td></tr>
        <tr><th>Status pembayaran order</th><td>Dibayar</td></tr>
    </table>

    <p class="muted" style="margin-top:14px">
        Dokumen ini diterbitkan sistem sebagai catatan perpindahan material. Verifikasi platform bukan pengganti penetapan atau persetujuan instansi berwenang.
    </p>

    <table class="sign" style="margin-top:18px">
        <tr>
            <td>Seller<br><br><br>( {{ $order->seller?->nama }} )</td>
            <td>Sopir<br><br><br>( {{ $shipment->sopir }} )</td>
            <td>Buyer (penerima)<br><br><br>( {{ $order->buyer?->nama }} )</td>
        </tr>
    </table>
</body>
</html>

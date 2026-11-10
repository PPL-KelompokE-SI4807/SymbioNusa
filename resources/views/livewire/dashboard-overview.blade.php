<div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
    @if($errorMessage)
        <div class="rounded-xl bg-amber-50 border border-amber-200 p-4 text-sm text-amber-800 flex items-center justify-between shadow-xs">
            <div class="flex items-center space-x-3">
                <svg class="w-5 h-5 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span>{{ $errorMessage }}</span>
            </div>
        </div>
    @endif

    <!-- Hero Welcome Banner -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-emerald-800 via-emerald-700 to-teal-800 p-6 sm:p-8 text-white shadow-lg shadow-emerald-900/10">
        <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
            <div class="space-y-2 max-w-2xl">
                <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full bg-emerald-600/50 backdrop-blur-md text-emerald-100 text-xs font-semibold tracking-wide border border-emerald-400/30">
                    <span class="w-2 h-2 rounded-full bg-emerald-300 animate-pulse"></span>
                    <span>Hak Akses: {{ $role }}</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white">
                    Selamat Datang, {{ $user->nama }}!
                </h1>
                <p class="text-emerald-100/90 text-sm sm:text-base leading-relaxed">
                    @if($role === 'ADMIN')
                        Panel Manajemen & Pengawasan DLH SymbioNusa. Pantau rekam jejak dampak ekologis, verifikasi entitas mitra, dan kelola sirkularitas material.
                    @elseif($role === 'SELLER')
                        Dasbor Pengelolaan Sisa Produksi Industri. Kelola penawaran material sekunder, pantau pesanan aktif, dan terbitkan faktur resmi.
                    @elseif($role === 'BUYER')
                        Dasbor Pengadaan Bahan Baku Sekunder. Temukan pasokan sisa industri terverifikasi non-B3 dan kelola transaksi pemesanan.
                    @elseif($role === 'INSPECTOR')
                        Dasbor Verifikasi Mutu & Kepatuhan Dokumen Non-B3. Lakukan inspeksi independen mode Desk dan Lapangan sesuai penugasan.
                    @else
                        Platform B2B Ekonomi Sirkular Sisa Produksi Industri Non-B3 SymbioNusa.
                    @endif
                </p>
            </div>

            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 shrink-0">
                <div class="px-4 py-2.5 rounded-xl bg-white/10 backdrop-blur-md border border-white/20 text-xs font-medium flex items-center space-x-2.5 text-white">
                    <svg class="w-4 h-4 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>{{ now()->translatedFormat('l, d F Y') }}</span>
                </div>
            </div>
        </div>

        <!-- Decorative background graphics -->
        <div class="absolute -right-12 -bottom-12 w-64 h-64 bg-emerald-500/10 rounded-full blur-2xl pointer-events-none"></div>
    </div>

    <!-- ADMIN ONLY: Impact Engine Highlights (Proposal P426) -->
    @if($role === 'ADMIN' && count($impactMetrics) > 0)
        <div id="kategori" class="bg-gradient-to-br from-slate-900 to-slate-800 rounded-2xl p-6 sm:p-7 text-white shadow-md">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-2">
                <div>
                    <h2 class="text-base sm:text-lg font-bold text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Akumulasi Dampak Lingkungan & Penghematan Ekonomi (Impact Engine)
                    </h2>
                    <p class="text-xs text-slate-400 mt-1">
                        Kalkulasi terpadu berdasarkan order berstatus selesai via ImpactCalculator (PBI-03 / SDG 12).
                    </p>
                </div>
                <span class="px-2.5 py-1 rounded-md text-2xs font-semibold uppercase bg-emerald-950 text-emerald-400 border border-emerald-800/80">
                    Real-time Logs
                </span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                <div class="bg-slate-800/70 border border-slate-700/80 rounded-xl p-5">
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Tonase Terdaur Ulang</span>
                    <span class="text-2xl sm:text-3xl font-extrabold text-emerald-400 mt-1.5 block tracking-tight">{{ $impactMetrics['tonase'] }}</span>
                    <span class="text-2xs text-slate-400 mt-1 block">Material sisa industri dialihkan dari landfill</span>
                </div>
                <div class="bg-slate-800/70 border border-slate-700/80 rounded-xl p-5">
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Efisiensi Biaya Total</span>
                    <span class="text-2xl sm:text-3xl font-extrabold text-teal-400 mt-1.5 block tracking-tight">{{ $impactMetrics['penghematan'] }}</span>
                    <span class="text-2xs text-slate-400 mt-1 block">Akumulasi penghematan Seller & Buyer</span>
                </div>
                <div class="bg-slate-800/70 border border-slate-700/80 rounded-xl p-5">
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Emisi CO₂e Dihindarkan</span>
                    <span class="text-2xl sm:text-3xl font-extrabold text-cyan-400 mt-1.5 block tracking-tight">{{ $impactMetrics['co2e'] }}</span>
                    <span class="text-2xs text-slate-400 mt-1 block">Pengurangan jejak karbon ekuivalen net</span>
                </div>
            </div>
        </div>
    @endif

    <!-- Metric Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($metrics as $metric)
            @php
                $colorTheme = match($metric['color']) {
                    'amber' => 'border-amber-200 bg-amber-50/40 text-amber-900 focus-within:ring-amber-500',
                    'blue' => 'border-blue-200 bg-blue-50/40 text-blue-900 focus-within:ring-blue-500',
                    'purple' => 'border-purple-200 bg-purple-50/40 text-purple-900 focus-within:ring-purple-500',
                    'teal' => 'border-teal-200 bg-teal-50/40 text-teal-900 focus-within:ring-teal-500',
                    'rose' => 'border-rose-200 bg-rose-50/40 text-rose-900 focus-within:ring-rose-500',
                    default => 'border-emerald-200 bg-emerald-50/40 text-emerald-900 focus-within:ring-emerald-500',
                };
            @endphp
            <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-2xs hover:shadow-sm hover:border-slate-300 transition flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-slate-500">{{ $metric['label'] }}</span>
                        <span class="text-2xs font-semibold px-2 py-0.5 rounded-md border {{ $colorTheme }}">
                            {{ $metric['badge'] }}
                        </span>
                    </div>
                    <div class="mt-3">
                        <h3 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                            {{ $metric['value'] }}
                        </h3>
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-2xs text-slate-500">
                    <span>Status: Terverifikasi</span>
                    <span class="font-medium text-emerald-600">Aktif &bull; SymbioNusa</span>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Data Tables & Operational Queues -->
    @if($role === 'ADMIN')
        <!-- Section: Perusahaan Menunggu Verifikasi -->
        <div id="perusahaan" class="bg-white rounded-xl border border-slate-200 shadow-2xs overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Antrean Verifikasi Perusahaan (PBI-08)</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Entitas bisnis yang memerlukan persetujuan legalitas untuk bertransaksi.</p>
                </div>
                <span class="text-xs font-semibold px-2.5 py-1 rounded-md bg-amber-100 text-amber-800 border border-amber-200">
                    Status: PENDING
                </span>
            </div>
            @if(isset($tables['pending_companies']) && count($tables['pending_companies']) > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 text-2xs font-semibold text-slate-500 uppercase tracking-wider bg-slate-50">
                                <th class="px-5 py-3">Nama Perusahaan</th>
                                <th class="px-5 py-3">NIB</th>
                                <th class="px-5 py-3">Tipe</th>
                                <th class="px-5 py-3">Kontak / Alamat</th>
                                <th class="px-5 py-3 text-right">Aksi Otoritas</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($tables['pending_companies'] as $c)
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="px-5 py-3.5 font-semibold text-slate-900">{{ $c->nama }}</td>
                                    <td class="px-5 py-3.5 text-slate-600 font-mono text-xs">{{ $c->nib ?? '-' }}</td>
                                    <td class="px-5 py-3.5">
                                        <span class="inline-flex px-2 py-0.5 rounded-md text-2xs font-semibold bg-slate-100 text-slate-800 border border-slate-200">
                                            {{ $c->tipe }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3.5 text-slate-600 text-xs">{{ $c->kontak ?? '-' }} &middot; {{ $c->alamat ?? '-' }}</td>
                                    <td class="px-5 py-3.5 text-right">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            Periksa Dokumen
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-8 text-center">
                    <svg class="mx-auto h-10 w-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="mt-2 text-sm font-medium text-slate-600">Tidak ada antrean verifikasi perusahaan saat ini.</p>
                    <p class="text-xs text-slate-400">Seluruh entitas terdaftar telah terverifikasi atau belum ada pendaftaran baru.</p>
                </div>
            @endif
        </div>

        <!-- Section: Sengketa Terbuka -->
        <div id="sengketa" class="bg-white rounded-xl border border-slate-200 shadow-2xs overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Sengketa & Tiket Ketidaksesuaian (PBI-13)</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Perselisihan mutu, tonase, atau kadar air yang memerlukan putusan admin.</p>
                </div>
                <span class="text-xs font-semibold px-2.5 py-1 rounded-md bg-rose-100 text-rose-800 border border-rose-200">
                    Prioritas Mediasi
                </span>
            </div>
            @if(isset($tables['active_disputes']) && count($tables['active_disputes']) > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 text-2xs font-semibold text-slate-500 uppercase tracking-wider bg-slate-50">
                                <th class="px-5 py-3">ID / Jenis</th>
                                <th class="px-5 py-3">PO Terkait</th>
                                <th class="px-5 py-3">Perusahaan Penggugat</th>
                                <th class="px-5 py-3">Deskripsi Singkat</th>
                                <th class="px-5 py-3 text-right">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($tables['active_disputes'] as $d)
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="px-5 py-3.5 font-semibold text-slate-900">
                                        #{{ $d->id_dispute }} &middot; {{ $d->jenis }}
                                    </td>
                                    <td class="px-5 py-3.5 text-slate-600 font-mono text-xs">PO-{{ $d->purchase_orders_id_purchase_order }}</td>
                                    <td class="px-5 py-3.5 text-slate-700">{{ $d->company->nama ?? 'ID #'.$d->companies_id_company }}</td>
                                    <td class="px-5 py-3.5 text-slate-600 text-xs max-w-xs truncate">{{ $d->deskripsi ?? '-' }}</td>
                                    <td class="px-5 py-3.5 text-right">
                                        <span class="inline-flex px-2 py-0.5 rounded-md text-2xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                            {{ $d->status }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-8 text-center">
                    <svg class="mx-auto h-10 w-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 13l4 4L19 7"/></svg>
                    <p class="mt-2 text-sm font-medium text-slate-600">Tidak ada sengketa transaksi yang terbuka.</p>
                    <p class="text-xs text-slate-400">Seluruh transaksi berjalan tertib dalam koridor toleransi material.</p>
                </div>
            @endif
        </div>

    @elseif($role === 'SELLER')
        <!-- Section: Listing Sisa Produksi Saya -->
        <div id="katalog" class="bg-white rounded-xl border border-slate-200 shadow-2xs overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Katalog Listing Sisa Produksi Saya (PBI-09)</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Daftar material non-B3 aktif yang ditawarkan ke pasar industri.</p>
                </div>
            </div>
            @if(isset($tables['my_listings']) && count($tables['my_listings']) > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 text-2xs font-semibold text-slate-500 uppercase tracking-wider bg-slate-50">
                                <th class="px-5 py-3">Nama Listing</th>
                                <th class="px-5 py-3">Stok Tersedia</th>
                                <th class="px-5 py-3">Harga / kg</th>
                                <th class="px-5 py-3">Lokasi</th>
                                <th class="px-5 py-3 text-right">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($tables['my_listings'] as $l)
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="px-5 py-3.5 font-semibold text-slate-900">{{ $l->judul }}</td>
                                    <td class="px-5 py-3.5 text-slate-700 font-mono text-xs">{{ number_format($l->stok_kg, 0, ',', '.') }} kg</td>
                                    <td class="px-5 py-3.5 text-slate-700 font-semibold text-xs">Rp {{ number_format($l->harga_per_kg, 0, ',', '.') }}</td>
                                    <td class="px-5 py-3.5 text-slate-600 text-xs">{{ $l->lokasi ?? '-' }}</td>
                                    <td class="px-5 py-3.5 text-right">
                                        <span class="inline-flex px-2 py-0.5 rounded-md text-2xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            {{ $l->status }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-8 text-center">
                    <svg class="mx-auto h-10 w-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    <p class="mt-2 text-sm font-medium text-slate-600">Belum ada listing material yang dipublikasikan.</p>
                    <p class="text-xs text-slate-400">Buat listing sisa produksi baru untuk mulai menawarkan ke pembeli industri.</p>
                </div>
            @endif
        </div>

        <!-- Section: Pesanan Masuk (PO) -->
        <div id="pesanan" class="bg-white rounded-xl border border-slate-200 shadow-2xs overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Pesanan Masuk Aktif (PBI-05)</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Purchase Order dari buyer yang perlu ditindaklanjuti.</p>
                </div>
            </div>
            @if(isset($tables['my_orders']) && count($tables['my_orders']) > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 text-2xs font-semibold text-slate-500 uppercase tracking-wider bg-slate-50">
                                <th class="px-5 py-3">ID PO</th>
                                <th class="px-5 py-3">Perusahaan Pembeli</th>
                                <th class="px-5 py-3">Volume Tonase</th>
                                <th class="px-5 py-3">Nilai Transaksi</th>
                                <th class="px-5 py-3 text-right">Status Transaksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($tables['my_orders'] as $po)
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="px-5 py-3.5 font-mono text-xs font-bold text-slate-900">PO-{{ $po->id_purchase_order }}</td>
                                    <td class="px-5 py-3.5 text-slate-700">{{ $po->buyer->nama ?? 'Buyer #'.$po->companies_id_buyer }}</td>
                                    <td class="px-5 py-3.5 text-slate-700 font-mono text-xs">{{ number_format($po->volume_kg, 0, ',', '.') }} kg</td>
                                    <td class="px-5 py-3.5 text-slate-800 font-semibold text-xs">Rp {{ number_format($po->volume_kg * $po->harga_per_kg, 0, ',', '.') }}</td>
                                    <td class="px-5 py-3.5 text-right">
                                        <span class="inline-flex px-2 py-0.5 rounded-md text-2xs font-bold bg-purple-50 text-purple-700 border border-purple-200">
                                            {{ $po->status }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-8 text-center">
                    <svg class="mx-auto h-10 w-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    <p class="mt-2 text-sm font-medium text-slate-600">Belum ada pesanan masuk aktif saat ini.</p>
                </div>
            @endif
        </div>

    @elseif($role === 'BUYER')
        <!-- Section: Kebutuhan Bahan Baku Saya -->
        <div id="kebutuhan" class="bg-white rounded-xl border border-slate-200 shadow-2xs overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Permintaan Kebutuhan Bahan Baku Saya (PBI-14)</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Spesifikasi material yang dipublikasikan ke papan kebutuhan industri.</p>
                </div>
            </div>
            @if(isset($tables['my_demands']) && count($tables['my_demands']) > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 text-2xs font-semibold text-slate-500 uppercase tracking-wider bg-slate-50">
                                <th class="px-5 py-3">ID Kebutuhan</th>
                                <th class="px-5 py-3">Volume Diperlukan</th>
                                <th class="px-5 py-3">Harga Maksimal</th>
                                <th class="px-5 py-3">Frekuensi</th>
                                <th class="px-5 py-3 text-right">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($tables['my_demands'] as $dm)
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="px-5 py-3.5 font-mono text-xs font-bold text-slate-900">REQ-{{ $dm->id_demand_request }}</td>
                                    <td class="px-5 py-3.5 text-slate-700 font-mono text-xs">{{ number_format($dm->volume_kg, 0, ',', '.') }} kg</td>
                                    <td class="px-5 py-3.5 text-slate-700 font-semibold text-xs">Rp {{ number_format($dm->harga_maks ?? 0, 0, ',', '.') }}/kg</td>
                                    <td class="px-5 py-3.5 text-slate-600 text-xs">{{ $dm->frekuensi }}</td>
                                    <td class="px-5 py-3.5 text-right">
                                        <span class="inline-flex px-2 py-0.5 rounded-md text-2xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                            {{ $dm->status }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-8 text-center">
                    <svg class="mx-auto h-10 w-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    <p class="mt-2 text-sm font-medium text-slate-600">Belum ada permintaan kebutuhan bahan baku yang aktif.</p>
                </div>
            @endif
        </div>

        <!-- Section: Pesanan Pembelian Saya -->
        <div id="pesanan" class="bg-white rounded-xl border border-slate-200 shadow-2xs overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Pesanan Pembelian Saya (PBI-05)</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Riwayat purchase order bahan baku sekunder kepada seller.</p>
                </div>
            </div>
            @if(isset($tables['my_orders']) && count($tables['my_orders']) > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 text-2xs font-semibold text-slate-500 uppercase tracking-wider bg-slate-50">
                                <th class="px-5 py-3">ID PO</th>
                                <th class="px-5 py-3">Perusahaan Penjual</th>
                                <th class="px-5 py-3">Volume Tonase</th>
                                <th class="px-5 py-3">Total Tagihan</th>
                                <th class="px-5 py-3 text-right">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($tables['my_orders'] as $po)
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="px-5 py-3.5 font-mono text-xs font-bold text-slate-900">PO-{{ $po->id_purchase_order }}</td>
                                    <td class="px-5 py-3.5 text-slate-700">{{ $po->seller->nama ?? 'Seller #'.$po->companies_id_seller }}</td>
                                    <td class="px-5 py-3.5 text-slate-700 font-mono text-xs">{{ number_format($po->volume_kg, 0, ',', '.') }} kg</td>
                                    <td class="px-5 py-3.5 text-slate-800 font-semibold text-xs">Rp {{ number_format($po->volume_kg * $po->harga_per_kg, 0, ',', '.') }}</td>
                                    <td class="px-5 py-3.5 text-right">
                                        <span class="inline-flex px-2 py-0.5 rounded-md text-2xs font-bold bg-purple-50 text-purple-700 border border-purple-200">
                                            {{ $po->status }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-8 text-center">
                    <svg class="mx-auto h-10 w-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    <p class="mt-2 text-sm font-medium text-slate-600">Belum ada pesanan pembelian yang terbit.</p>
                </div>
            @endif
        </div>

    @elseif($role === 'INSPECTOR')
        <!-- Section: Jadwal & Tugas Inspeksi Aktif -->
        <div id="tugas" class="bg-white rounded-xl border border-slate-200 shadow-2xs overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Penugasan Verifikasi Mutu & Dokumen (PBI-17 & 18)</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Antrean inspeksi independen yang telah ditugaskan oleh admin platform.</p>
                </div>
            </div>
            @if(isset($tables['assigned_inspections']) && count($tables['assigned_inspections']) > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 text-2xs font-semibold text-slate-500 uppercase tracking-wider bg-slate-50">
                                <th class="px-5 py-3">ID Inspeksi</th>
                                <th class="px-5 py-3">Nomor PO Terkait</th>
                                <th class="px-5 py-3">Mode Verifikasi</th>
                                <th class="px-5 py-3">Jadwal Tugas</th>
                                <th class="px-5 py-3 text-right">Status Penugasan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($tables['assigned_inspections'] as $ins)
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="px-5 py-3.5 font-mono text-xs font-bold text-slate-900">#INS-{{ $ins->id_inspection }}</td>
                                    <td class="px-5 py-3.5 text-slate-700 font-mono text-xs">PO-{{ $ins->purchase_orders_id_purchase_order }}</td>
                                    <td class="px-5 py-3.5">
                                        @if($ins->mode_verifikasi === 'LAPANGAN')
                                            <span class="inline-flex px-2 py-0.5 rounded-md text-2xs font-semibold bg-amber-100 text-amber-800 border border-amber-200">
                                                Lapangan (Pemeriksaan Fisik)
                                            </span>
                                        @else
                                            <span class="inline-flex px-2 py-0.5 rounded-md text-2xs font-semibold bg-blue-100 text-blue-800 border border-blue-200">
                                                Desk (Bukti Digital)
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5 text-slate-600 text-xs">
                                        {{ $ins->jadwal ? $ins->jadwal->translatedFormat('d M Y, H:i') : '-' }}
                                    </td>
                                    <td class="px-5 py-3.5 text-right">
                                        <span class="inline-flex px-2 py-0.5 rounded-md text-2xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                            {{ $ins->status }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-8 text-center">
                    <svg class="mx-auto h-10 w-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    <p class="mt-2 text-sm font-medium text-slate-600">Tidak ada penugasan inspeksi aktif saat ini.</p>
                </div>
            @endif
        </div>
    @endif
</div>

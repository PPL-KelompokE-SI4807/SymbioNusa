<div>
    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Welcome Banner -->
        <div class="bg-gradient-to-r from-emerald-600 to-teal-700 rounded-2xl shadow-xl p-6 sm:p-8 text-white mb-8 flex flex-col md:flex-row justify-between items-start md:items-center">
            <div>
                <span class="inline-block px-3 py-1 rounded-full bg-emerald-500/30 text-emerald-100 text-xs font-semibold tracking-wide uppercase mb-2">
                    Peran: {{ $role }}
                </span>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Selamat Datang, {{ $user->nama }}!</h1>
                <p class="mt-2 text-emerald-100 text-sm sm:text-base max-w-2xl">
                    Platform Ekonomi Sirkular Sisa Produksi Industri Non-B3 SymbioNusa. Pantau metrik, transaksi, dan aktivitas operasional Anda secara real-time.
                </p>
            </div>
            <div class="mt-6 md:mt-0 flex gap-3">
                <span class="inline-flex items-center px-4 py-2 bg-white/10 backdrop-blur-md rounded-xl text-sm font-medium border border-white/20">
                    <svg class="w-4 h-4 mr-2 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    {{ now()->translatedFormat('l, d F Y') }}
                </span>
            </div>
        </div>

        <!-- Metric Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            @foreach($metrics as $metric)
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition duration-200 flex flex-col justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">{{ $metric['label'] }}</p>
                        <h3 class="text-2xl sm:text-3xl font-bold text-gray-900 mt-2 tracking-tight">{{ $metric['value'] }}</h3>
                    </div>
                    <div class="mt-4 flex items-center text-xs font-semibold text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-lg w-fit">
                        <span>Aktif & Terverifikasi</span>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Recent Data Section -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sm:p-8">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-bold text-gray-900">Aktivitas & Data Terbaru</h3>
                <span class="text-xs text-gray-500 uppercase tracking-wider font-semibold">SymbioNusa Engine</span>
            </div>

            @if(count($recentData) > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider rounded-l-xl">ID / Nama</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Detail / Informasi</th>
                                <th class="px-6 py-3 bg-gray-50 text-right text-xs font-medium text-gray-500 uppercase tracking-wider rounded-r-xl">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($recentData as $item)
                                <tr class="hover:bg-gray-50/50 transition">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        @if($role === 'ADMIN')
                                            {{ $item->nama }} <span class="text-xs text-gray-400">({{ $item->tipe }})</span>
                                        @elseif($role === 'SELLER')
                                            {{ $item->judul }} <span class="text-xs text-gray-400">Rp {{ number_format($item->harga_per_kg, 0, ',', '.') }}/kg</span>
                                        @elseif($role === 'BUYER')
                                            Permintaan #{{ $item->id_demand_request }} <span class="text-xs text-gray-400">{{ number_format($item->volume_kg, 0, ',', '.') }} kg</span>
                                        @elseif($role === 'INSPECTOR')
                                            Inspeksi #{{ $item->id_inspection }} <span class="text-xs text-gray-400">PO #{{ $item->purchase_orders_id_purchase_order ?? '-' }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        @if($role === 'ADMIN')
                                            NIB: {{ $item->nib ?? '-' }} | Alamat: {{ $item->alamat ?? '-' }}
                                        @elseif($role === 'SELLER')
                                            Stok: {{ number_format($item->stok_kg, 0, ',', '.') }} kg | Lokasi: {{ $item->lokasi ?? '-' }}
                                        @elseif($role === 'BUYER')
                                            Volume: {{ number_format($item->volume_kg, 0, ',', '.') }} kg | Berlaku: {{ $item->berlaku_sampai ?? '-' }}
                                        @elseif($role === 'INSPECTOR')
                                            Jadwal: {{ $item->tgl_inspeksi ?? '-' }} | Lokasi: {{ $item->lokasi ?? '-' }}
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                        @php
                                            $st = $item->status_verifikasi ?? $item->status ?? 'AKTIF';
                                        @endphp
                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-emerald-100 text-emerald-800">
                                            {{ $st }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                    <p class="mt-2 text-sm font-medium text-gray-600">Belum ada data aktivitas untuk ditampilkan.</p>
                </div>
            @endif
        </div>
    </div>
</div>

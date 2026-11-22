<!DOCTYPE html>
{{--
    Landing page SymbioNusa (redesign 19 Nov 2026).
    Mengikuti docs/design/DESIGN.md (FND v8) untuk token, tipografi, dan aturan konten,
    dengan skala display yang lebih besar khusus halaman publik (bukan layar aplikasi):
    foto material asli ukuran besar, satu tombol filled per region, status sebagai glif + teks,
    angka demo selalu berlabel "data demo". Tanpa hex mentah, tanpa dark mode (palet aplikasi terang).
    Filter papan material memakai JavaScript vanilla kecil di bawah (app.js repo kosong;
    Alpine hanya tersedia lewat Livewire di layar aplikasi).
--}}
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>SymbioNusa — Sisa produksi satu industri, bahan baku industri lain</title>
    <meta name="description" content="SymbioNusa adalah platform B2B ekonomi sirkular untuk sisa produksi industri non-B3 di Indonesia: diperdagangkan antara Seller dan Buyer, diinspeksi Inspector independen, dan diawasi Dinas Lingkungan Hidup.">

    @fonts
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="bg-background font-sans text-body-md text-on-surface antialiased">

    {{-- ================= HERO ================= --}}
    <header class="relative overflow-hidden bg-secondary text-on-secondary">
        <img src="{{ asset('images/material/kardus-besar.jpg') }}" alt="Bongkahan kardus bekas terkompresi di fasilitas daur ulang"
             class="absolute inset-0 h-full w-full object-cover" fetchpriority="high">
        <div class="absolute inset-0 bg-[linear-gradient(168deg,rgb(71_88_28/0.86),rgb(30_42_8/0.95)_72%)]" aria-hidden="true"></div>

        <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            {{-- Bar atas --}}
            <div class="flex h-16 items-center justify-between gap-4">
                <a href="{{ url('/') }}" class="flex items-center gap-2.5 rounded-sm focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-on-secondary">
                    <span data-brand-mark class="flex size-9 items-center justify-center" aria-hidden="true">
                        <x-ui.icon name="loop" class="size-5" />
                    </span>
                    <span class="text-dash-title">SymbioNusa</span>
                </a>
                <nav class="hidden items-center gap-7 text-label-md md:flex" aria-label="Navigasi halaman">
                    <a href="#papan" class="rounded-sm hover:underline hover:underline-offset-4 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-on-secondary">Papan material</a>
                    <a href="#cara-kerja" class="rounded-sm hover:underline hover:underline-offset-4 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-on-secondary">Cara kerja</a>
                    <a href="#aturan" class="rounded-sm hover:underline hover:underline-offset-4 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-on-secondary">Aturan main</a>
                    <a href="#peran" class="rounded-sm hover:underline hover:underline-offset-4 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-on-secondary">Peran</a>
                </nav>
                @auth
                    <a href="{{ route('dashboard') }}" class="rounded-sm text-label-md font-semibold hover:underline hover:underline-offset-4 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-on-secondary">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="rounded-sm text-label-md font-semibold hover:underline hover:underline-offset-4 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-on-secondary">Masuk</a>
                @endauth
            </div>

            {{-- Pernyataan utama + kartu foto --}}
            <div class="grid items-end gap-10 pt-14 pb-16 lg:grid-cols-12 lg:pt-20 lg:pb-24">
                <div class="lg:col-span-7">
                    <h1 class="text-[clamp(2.875rem,6vw,5.75rem)] leading-[1.02] font-black text-balance">
                        Sisa produksi satu industri, bahan baku industri lain.
                    </h1>
                    <p class="mt-6 max-w-xl text-body-lg text-on-secondary-muted">
                        Pertukaran residu industri non-B3 antara Seller dan Buyer — dengan inspeksi mutu
                        oleh Inspector independen dan pengawasan Dinas Lingkungan Hidup.
                    </p>
                    <div class="mt-8 flex flex-wrap items-center gap-3">
                        @auth
                            <x-ui.button size="lg" :href="route('dashboard')">Ke dashboard</x-ui.button>
                        @else
                            <x-ui.button size="lg" :href="route('login')">Masuk</x-ui.button>
                        @endauth
                        <x-ui.button variant="secondary" size="lg" href="#akses">Ajukan verifikasi perusahaan</x-ui.button>
                    </div>
                    <p class="mt-8 text-body-sm text-on-secondary-muted">
                        Non-B3 saja &middot; Setiap order diinspeksi &middot; Bukti transfer diverifikasi Seller
                    </p>
                </div>

                <figure class="lg:col-span-5">
                    <img src="{{ asset('images/material/plastik.jpg') }}" alt="Pelet plastik hasil daur ulang"
                         class="aspect-[4/3] w-full rounded-lg object-cover shadow-overlay">
                    <figcaption class="mt-3 flex items-baseline justify-between gap-3 text-body-sm text-on-secondary-muted">
                        <span>Pelet plastik PP &middot; Cikarang</span>
                        <span class="shrink-0 tabular-nums">5.000 kg &middot; Rp 12.500/kg</span>
                    </figcaption>
                    <p class="mt-1 text-label-sm text-secondary-label">Contoh listing &middot; data demo</p>
                </figure>
            </div>
        </div>
    </header>

    <main>
        {{-- ================= KATALOG FOTO MATERIAL ================= --}}
        <section class="mx-auto max-w-7xl px-4 pt-16 sm:px-6 lg:px-8 lg:pt-24" aria-labelledby="katalog-judul">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <h2 id="katalog-judul" class="max-w-md text-headline-lg font-black">Material yang benar-benar diperdagangkan</h2>
                <p class="max-w-sm text-body-md text-on-surface-muted">
                    Bukan ilustrasi stok: ini foto residu yang kategorinya terdaftar di platform,
                    lengkap dengan harga yang terbentuk lewat RFQ.
                </p>
            </div>

            <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <figure>
                    <img src="{{ asset('images/material/kain.jpg') }}" alt="Tumpukan perca kain sisa produksi garmen"
                         class="aspect-[3/4] w-full rounded-lg object-cover" loading="lazy">
                    <figcaption class="mt-3">
                        <p class="text-label-md font-semibold">Kain perca katun</p>
                        <p class="text-body-sm text-on-surface-muted tabular-nums">RFQ disepakati Rp 3.500/kg &middot; 2.000 kg</p>
                    </figcaption>
                </figure>
                <figure class="lg:translate-y-8">
                    <img src="{{ asset('images/material/ampas-tahu.jpg') }}" alt="Ampas tahu segar hasil samping produksi tahu"
                         class="aspect-[3/4] w-full rounded-lg object-cover" loading="lazy">
                    <figcaption class="mt-3">
                        <p class="text-label-md font-semibold">Ampas tahu</p>
                        <p class="text-body-sm text-on-surface-muted tabular-nums">RFQ disepakati Rp 600/kg &middot; 5.000 kg</p>
                    </figcaption>
                </figure>
                <figure>
                    <img src="{{ asset('images/material/serbuk-kayu.jpg') }}" alt="Serbuk kayu hasil penggergajian"
                         class="aspect-[3/4] w-full rounded-lg object-cover" loading="lazy">
                    <figcaption class="mt-3">
                        <p class="text-label-md font-semibold">Serbuk kayu</p>
                        <p class="text-body-sm text-on-surface-muted">Kategori Kayu &middot; kadar air maks. per templat kategori</p>
                    </figcaption>
                </figure>
                <figure class="lg:translate-y-8">
                    <img src="{{ asset('images/material/kardus.jpg') }}" alt="Kardus bekas terkompresi siap diolah kembali"
                         class="aspect-[3/4] w-full rounded-lg object-cover" loading="lazy">
                    <figcaption class="mt-3">
                        <p class="text-label-md font-semibold">Kardus bekas</p>
                        <p class="text-body-sm text-on-surface-muted">Kategori Kertas &middot; ditimbang ulang saat penerimaan</p>
                    </figcaption>
                </figure>
            </div>
        </section>

        {{-- ================= PAPAN MATERIAL ================= --}}
        <section id="papan" class="mx-auto max-w-7xl scroll-mt-8 px-4 pt-20 sm:px-6 lg:px-8 lg:pt-28" aria-labelledby="papan-judul">
            <div class="rounded-lg bg-surface p-6 shadow-card sm:p-8">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <h2 id="papan-judul" class="text-headline-lg font-black">Papan material</h2>
                        <p class="mt-1 text-body-md text-on-surface-muted">Listing Seller dan kebutuhan Buyer yang sedang terbuka.</p>
                    </div>
                    <span class="inline-flex h-8 items-center rounded-full border border-outline bg-surface px-3 text-body-sm font-medium text-on-surface-muted">Data demo</span>
                </div>

                {{-- Filter kategori --}}
                <div class="mt-6 flex flex-wrap gap-2" role="group" aria-label="Filter kategori material">
                    @foreach (['semua' => 'Semua', 'plastik' => 'Plastik', 'tekstil' => 'Tekstil', 'pangan' => 'Pangan', 'kayu' => 'Kayu', 'kertas' => 'Kertas'] as $value => $label)
                        <button type="button" data-filter="{{ $value }}" aria-pressed="{{ $value === 'semua' ? 'true' : 'false' }}"
                                class="filter-chip flex h-10 items-center rounded-sm border px-4 text-label-md transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus-ring {{ $value === 'semua' ? 'border-primary bg-primary font-semibold text-on-primary' : 'border-outline-control bg-surface text-on-surface hover:bg-surface-muted' }}">
                            {{ $label }}
                        </button>
                    @endforeach
                </div>

                {{-- Wadah scroll bisa difokus agar tabel lebar tetap bisa digeser dengan keyboard (WCAG 2.1.1). --}}
                <div class="mt-4 overflow-x-auto rounded-sm focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus-ring"
                     tabindex="0" role="region" aria-label="Tabel papan material, geser ke samping untuk kolom lain">
                    <table class="w-full border-collapse text-left sm:min-w-[640px]">
                        <thead>
                            <tr class="border-b border-outline-hairline text-body-sm text-on-surface-subtle">
                                <th scope="col" class="py-3 pr-4 font-medium">Material</th>
                                <th scope="col" class="hidden py-3 pr-4 font-medium sm:table-cell">Perusahaan</th>
                                <th scope="col" class="hidden py-3 pr-4 text-right font-medium sm:table-cell">Volume</th>
                                <th scope="col" class="hidden py-3 pr-4 text-right font-medium sm:table-cell">Harga</th>
                                <th scope="col" class="hidden py-3 font-medium sm:table-cell">Status</th>
                            </tr>
                        </thead>
                        <tbody class="text-data-tabular tabular-nums">
                            {{-- Baris papan: pada implementasi penuh, render dari Listing & DemandRequest.
                                 Status memakai label listings.status / demand_requests.status di 03_STATUS_MAP.md. --}}
                            @foreach ([
                                ['plastik', 'plastik.jpg', 'Sisa Potongan Plastik PP Murni', 'Listing Seller', 'PT Plastik Makmur Sejahtera', '5.000 kg', 'Rp 12.500/kg', 'Dipublikasikan', 'success'],
                                ['plastik', 'plastik-2.jpg', 'Kebutuhan Plastik PP/PE Non-B3', 'Kebutuhan Buyer', 'PT Daur Ulang Nusantara', '3.000 kg', 'maks. Rp 13.000/kg', 'Terbuka', 'warning'],
                                ['tekstil', 'kain.jpg', 'Kain perca katun', 'Listing Seller', 'CV Tekstil Jaya', '2.000 kg', 'Rp 3.500/kg', 'Dipublikasikan', 'success'],
                                ['pangan', 'ampas-tahu.jpg', 'Ampas tahu', 'Listing Seller', 'UD Tahu Sari', '5.000 kg', 'Rp 600/kg', 'Dipublikasikan', 'success'],
                            ] as [$category, $photo, $name, $kind, $company, $volume, $price, $status, $variant])
                                <tr data-category="{{ $category }}" class="{{ $loop->last ? '' : 'border-b border-outline-hairline' }}">
                                    <td class="py-4 pr-4">
                                        <span class="flex items-center gap-3">
                                            <img src="{{ asset('images/material/'.$photo) }}" alt="" class="size-12 shrink-0 rounded-sm object-cover">
                                            <span>
                                                <span class="block font-semibold sm:whitespace-nowrap">{{ $name }}</span>
                                                <span class="block text-body-sm text-on-surface-subtle">{{ $kind }}<span class="sm:hidden"> · {{ $company }}</span></span>
                                                {{-- Di bawah 640 px kolom lain dilipat ke dalam sel ini, bukan disembunyikan di area gulir. --}}
                                                <span class="mt-1 block text-body-sm text-on-surface sm:hidden">{{ $volume }} · {{ $price }}</span>
                                                <span class="mt-1 block sm:hidden"><x-ui.badge :variant="$variant">{{ $status }}</x-ui.badge></span>
                                            </span>
                                        </span>
                                    </td>
                                    <td class="hidden py-4 pr-4 sm:table-cell">{{ $company }}</td>
                                    <td class="hidden py-4 pr-4 text-right whitespace-nowrap sm:table-cell">{{ $volume }}</td>
                                    <td class="hidden py-4 pr-4 text-right whitespace-nowrap sm:table-cell">{{ $price }}</td>
                                    <td class="hidden py-4 sm:table-cell"><x-ui.badge :variant="$variant">{{ $status }}</x-ui.badge></td>
                                </tr>
                            @endforeach
                            <tr data-empty class="hidden">
                                <td colspan="5" class="py-10 text-center">
                                    <p class="font-semibold">Belum ada entri pada kategori ini</p>
                                    <p class="mt-1 text-body-sm text-on-surface-muted">Pada data demo, kategori Kayu dan Kertas belum memiliki listing atau kebutuhan terbuka.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p class="mt-4 text-body-sm text-on-surface-subtle" aria-live="polite">
                    Menampilkan <span data-count>4</span> dari 4 entri &middot; harga per kilogram, volume dalam kilogram
                </p>
            </div>
        </section>

        {{-- ================= CARA KERJA ================= --}}
        <section id="cara-kerja" class="mx-auto max-w-7xl scroll-mt-8 px-4 pt-20 sm:px-6 lg:px-8 lg:pt-28" aria-labelledby="cara-judul">
            <h2 id="cara-judul" class="max-w-md text-headline-lg font-black">Satu order, dari RFQ sampai selesai</h2>
            <p class="mt-2 max-w-xl text-body-md text-on-surface-muted">
                Alur yang sama untuk semua transaksi — status berpindah hanya lewat evaluasi sistem, bukan diubah manual.
            </p>

            {{-- Tahap dan label persis dari docs/design/prompts/03_STATUS_MAP.md (purchase_orders.status),
                 dikelompokkan seperti DESIGN.md › Order flow. --}}
            <div class="mt-8 grid gap-6 lg:grid-cols-3">
                @foreach ([
                    ['Kesepakatan', ['Draf', 'Menunggu Konfirmasi', 'Dikonfirmasi'], 'PO dibuat dari RFQ yang disepakati; Seller mengonfirmasi atau menolaknya.'],
                    ['Mutu & pembayaran', ['Inspeksi', 'Lolos Inspeksi', 'Menunggu Pembayaran', 'Dibayar'], 'Inspector memeriksa mutu sebelum invoice terbit. Buyer mentransfer dan mengunggah bukti, Seller memverifikasinya.'],
                    ['Pengiriman', ['Dalam Pengiriman', 'Diterima', 'Selesai'], null],
                ] as [$group, $steps, $note])
                    <div class="rounded-lg bg-surface p-6 shadow-card">
                        <p class="text-label-md font-bold text-on-surface-muted">{{ $group }}</p>
                        <ol class="mt-4 space-y-3">
                            @foreach ($steps as $step)
                                <li class="border-t border-outline-hairline pt-3 text-label-md font-semibold">{{ $step }}</li>
                            @endforeach
                        </ol>
                        @if ($note)
                            <p class="mt-4 text-body-sm text-on-surface-muted">{{ $note }}</p>
                        @else
                            <p class="mt-4 text-body-sm text-on-surface-muted">
                                Barang berangkat setelah order dibayar dan dokumen non-B3 disetujui. Saat diterima, tonase dan kadar air
                                dicek terhadap toleransi yang tercatat di PO; di luar toleransi, order berpindah ke
                                <span class="font-semibold text-error">Sengketa</span> untuk mediasi Admin/DLH.
                            </p>
                        @endif
                    </div>
                @endforeach
            </div>
        </section>

        {{-- ================= ATURAN MAIN + PERAN ================= --}}
        <section id="aturan" class="mx-auto max-w-7xl scroll-mt-8 px-4 pt-20 sm:px-6 lg:px-8 lg:pt-28">
            <div class="grid gap-10 lg:grid-cols-12">
                <div class="lg:col-span-5">
                    <h2 class="text-headline-lg font-black">Aturan main</h2>
                    <p class="mt-2 max-w-sm text-body-md text-on-surface-muted">
                        Empat aturan ini ditegakkan sistem, bukan sekadar himbauan — dan berlaku sama untuk semua perusahaan.
                    </p>
                </div>
                <ol class="lg:col-span-7">
                    @foreach ([
                        ['Residu B3 dilarang keras', 'Kategori beracun dan berbahaya (B3) tidak dapat didaftarkan dalam bentuk apa pun. Setiap kategori material terikat templat toleransi resmi.'],
                        ['Mutu dibuktikan, bukan diklaim', 'Inspector bersertifikat memeriksa mutu sebelum invoice terbit, lewat dokumen atau di lapangan. Order yang gagal inspeksi tidak berlanjut ke pembayaran.'],
                        ['Pembayaran meninggalkan jejak', 'Platform tidak memegang uang. Buyer mentransfer ke rekening Seller dan mengunggah bukti; Seller memverifikasinya terhadap invoice.'],
                        ['Sengketa diputus dari data', 'Bila tonase atau kadar air saat penerimaan di luar toleransi, order berpindah ke Sengketa dan tiket sengketa terbuka otomatis. Admin/DLH memediasi berdasarkan hasil inspeksi, data penerimaan, dan riwayat status yang tercatat.'],
                    ] as $i => [$title, $desc])
                        <li class="flex gap-5 border-t border-outline-hairline py-6 {{ $loop->last ? 'border-b' : '' }}">
                            <span class="text-headline-md font-black text-on-surface-subtle tabular-nums">{{ $i + 1 }}</span>
                            <div>
                                <h3 class="text-dash-title">{{ $title }}</h3>
                                <p class="mt-1 max-w-xl text-body-md text-on-surface-muted">{{ $desc }}</p>
                            </div>
                        </li>
                    @endforeach
                </ol>
            </div>

            <div id="peran" class="mt-16 scroll-mt-8 overflow-x-auto rounded-lg bg-surface shadow-card focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus-ring"
                 tabindex="0" role="region" aria-labelledby="peran-judul">
                <table class="w-full border-collapse text-left sm:min-w-[640px]">
                    <caption id="peran-judul" class="px-[18px] pt-6 pb-2 text-left text-dash-title sm:px-6">Empat peran, satu rantai</caption>
                    <thead>
                        <tr class="border-b border-outline-hairline text-body-sm text-on-surface-subtle">
                            <th scope="col" class="px-[18px] py-3 font-medium sm:px-6">Peran</th>
                            <th scope="col" class="hidden px-6 py-3 font-medium sm:table-cell">Tanggung jawab</th>
                            <th scope="col" class="hidden px-6 py-3 font-medium sm:table-cell">Pekerjaan pertama di dashboard</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ([
                            ['Seller', 'Menjual sisa produksi dan memverifikasi pembayaran', 'Menjawab RFQ yang masuk'],
                            ['Buyer', 'Membeli residu sebagai bahan baku', 'Memantau papan kebutuhan'],
                            ['Inspector', 'Menguji mutu dan mencatat hasil inspeksi', 'Mengerjakan tugas inspeksi'],
                            ['Admin/DLH', 'Memverifikasi mitra dan mengawasi kepatuhan', 'Meninjau antrean verifikasi'],
                        ] as [$role, $duty, $first])
                            <tr class="border-b border-outline-hairline last:border-0">
                                <td class="px-[18px] py-4 sm:px-6">
                                    <span class="block font-semibold">{{ $role }}</span>
                                    {{-- Di bawah 640 px kolom lain dilipat ke sel ini, bukan disembunyikan di area gulir. --}}
                                    <span class="mt-1 block sm:hidden">{{ $duty }}</span>
                                    <span class="block text-body-sm text-on-surface-muted sm:hidden">Pertama di dashboard: {{ $first }}</span>
                                </td>
                                <td class="hidden px-6 py-4 sm:table-cell">{{ $duty }}</td>
                                <td class="hidden px-6 py-4 text-on-surface-muted sm:table-cell">{{ $first }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        {{-- ================= BAND DAMPAK ================= --}}
        <section class="mt-20 bg-linear-to-br from-band-forest-from to-band-forest-to text-on-secondary lg:mt-28" aria-labelledby="dampak-judul">
            <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8 lg:py-20">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <h2 id="dampak-judul" class="text-headline-lg font-black">Dampak yang tercatat</h2>
                    <span class="inline-flex h-8 items-center rounded-full border border-on-secondary/25 px-3 text-body-sm font-medium text-on-secondary-muted">Data demo &middot; bukan capaian produksi</span>
                </div>
                <div class="mt-10 grid gap-10 sm:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <p class="text-[clamp(2.5rem,4vw,4.5rem)] leading-none font-black whitespace-nowrap tabular-nums">128,4<span class="ml-2 text-headline-lg font-medium">ton</span></p>
                        <p class="mt-2 text-body-md text-on-secondary-muted">Residu teralihkan dari pembuangan</p>
                    </div>
                    <div>
                        <p class="text-[clamp(2.5rem,4vw,4.5rem)] leading-none font-black whitespace-nowrap tabular-nums"><span class="mr-2 text-headline-lg font-medium">Rp</span>214,6<span class="ml-2 text-headline-lg font-medium">jt</span></p>
                        <p class="mt-2 text-body-md text-on-secondary-muted">Penghematan bahan baku Buyer</p>
                    </div>
                    <div>
                        <p class="text-[clamp(2.5rem,4vw,4.5rem)] leading-none font-black whitespace-nowrap tabular-nums">96,3<span class="ml-2 text-headline-lg font-medium">ton</span></p>
                        <p class="mt-2 text-body-md text-on-secondary-muted">CO<sub class="text-[12px]">2</sub>e bersih terhindarkan</p>
                    </div>
                    <div>
                        <p class="text-[clamp(2.5rem,4vw,4.5rem)] leading-none font-black whitespace-nowrap tabular-nums">19<span class="ml-2 text-headline-lg font-medium">/ 24</span></p>
                        <p class="mt-2 text-body-md text-on-secondary-muted">Inspector aktif dari total terdaftar</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- ================= AKSES ================= --}}
        <section id="akses" class="mx-auto max-w-7xl scroll-mt-8 px-4 py-16 sm:px-6 lg:px-8 lg:py-24" aria-labelledby="akses-judul">
            <div class="grid items-start gap-10 lg:grid-cols-12">
                <div class="lg:col-span-7">
                    <h2 id="akses-judul" class="max-w-lg text-headline-lg font-black">Akun diterbitkan setelah perusahaan Anda terverifikasi</h2>
                    <p class="mt-3 max-w-xl text-body-md text-on-surface-muted">
                        Tidak ada pendaftaran mandiri. Admin/DLH memeriksa legalitas perusahaan terlebih dahulu,
                        sehingga setiap Seller dan Buyer di papan material adalah perusahaan yang nyata dan dapat ditelusur.
                    </p>
                    @auth
                        <x-ui.button size="lg" :href="route('dashboard')" class="mt-8">Ke dashboard</x-ui.button>
                    @else
                        <x-ui.button size="lg" :href="route('login')" class="mt-8">Masuk dengan akun terbitan</x-ui.button>
                    @endauth
                </div>
                <ol class="space-y-6 lg:col-span-5">
                    @foreach ([
                        ['Ajukan verifikasi', 'Sampaikan profil dan legalitas perusahaan kepada Admin/DLH.'],
                        ['Akun diterbitkan', 'Perusahaan yang lolos menerima akun dengan peran Seller atau Buyer.'],
                        ['Mulai bertransaksi', 'Pasang listing, kirim RFQ, dan ikuti alur order sampai selesai.'],
                    ] as $i => [$title, $desc])
                        <li class="flex gap-4">
                            <span class="flex size-9 shrink-0 items-center justify-center rounded-sm bg-primary-container font-black text-on-primary-container tabular-nums">{{ $i + 1 }}</span>
                            <div>
                                <h3 class="text-dash-title">{{ $title }}</h3>
                                <p class="mt-0.5 text-body-md text-on-surface-muted">{{ $desc }}</p>
                            </div>
                        </li>
                    @endforeach
                </ol>
            </div>
        </section>
    </main>

    {{-- ================= FOOTER ================= --}}
    <footer class="border-t border-outline">
        <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
            <div class="flex flex-wrap items-start justify-between gap-6">
                <div class="flex items-center gap-2.5">
                    <span data-brand-mark class="flex size-9 items-center justify-center" aria-hidden="true">
                        <x-ui.icon name="loop" class="size-5" />
                    </span>
                    <div>
                        <p class="text-headline-md">SymbioNusa</p>
                        <p class="text-body-sm text-on-surface-muted">Platform ekonomi sirkular untuk residu industri non-B3 di Indonesia.</p>
                    </div>
                </div>
                <nav class="flex flex-wrap gap-x-7 gap-y-2 text-label-md" aria-label="Navigasi kaki halaman">
                    <a href="#papan" class="rounded-sm hover:underline hover:underline-offset-4 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus-ring">Papan material</a>
                    <a href="#cara-kerja" class="rounded-sm hover:underline hover:underline-offset-4 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus-ring">Cara kerja</a>
                    <a href="#aturan" class="rounded-sm hover:underline hover:underline-offset-4 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus-ring">Aturan main</a>
                    @auth
                        <a href="{{ route('dashboard') }}" class="rounded-sm hover:underline hover:underline-offset-4 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus-ring">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="rounded-sm hover:underline hover:underline-offset-4 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus-ring">Masuk</a>
                    @endauth
                </nav>
            </div>
            <p class="mt-8 border-t border-outline pt-6 text-body-sm font-medium text-error">
                Residu kategori B3 (beracun dan berbahaya) dilarang keras diperdagangkan di platform ini.
            </p>
            <p class="mt-3 text-body-sm text-on-surface-subtle">
                Foto material: SuSanA Secretariat, Elinkorn, Pax Ahimsa Gethen, Noslenlou, Romy2702 &middot; CC BY / CC BY-SA &middot; Wikimedia Commons.
                Angka dan entri bertanda data demo bersifat contoh untuk keperluan pengembangan.
            </p>
        </div>
    </footer>

    {{-- Filter papan material (vanilla JS; tidak bergantung pada Livewire/Alpine) --}}
    <script>
        document.querySelectorAll('[data-filter]').forEach(function (chip) {
            chip.addEventListener('click', function () {
                var value = chip.getAttribute('data-filter');

                document.querySelectorAll('[data-filter]').forEach(function (other) {
                    var active = other === chip;
                    other.setAttribute('aria-pressed', active ? 'true' : 'false');
                    other.className = 'filter-chip flex h-10 items-center rounded-sm border px-4 text-label-md transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus-ring ' + (active
                        ? 'border-primary bg-primary font-semibold text-on-primary'
                        : 'border-outline-control bg-surface text-on-surface hover:bg-surface-muted');
                });

                var shown = 0;
                document.querySelectorAll('[data-category]').forEach(function (row) {
                    var match = value === 'semua' || row.getAttribute('data-category') === value;
                    row.classList.toggle('hidden', !match);
                    if (match) shown++;
                });

                var emptyRow = document.querySelector('[data-empty]');
                if (emptyRow) emptyRow.classList.toggle('hidden', shown !== 0);

                var counter = document.querySelector('[data-count]');
                if (counter) counter.textContent = shown;
            });
        });
    </script>
</body>
</html>

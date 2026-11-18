{{--
    Sidebar app shell (DESIGN.md › Layout; STITCH_BRIEF.md › Roles and their sidebar navigation).
    Nama menu WAJIB persis seperti STITCH_BRIEF.md. Sidebar hijau tua (secondary) dipakai untuk semua peran.

    Menambah halaman baru: isi 'route' dengan nama route-nya (dan 'active' dengan pola routeIs).
    Item dengan 'route' => null belum punya halaman dan ditampilkan non-aktif ("Belum tersedia").
--}}
@php
    $user = auth()->user();
    $role = strtoupper($user->role ?? '');

    $menus = [
        'ADMIN' => [
            'UTAMA' => [
                ['label' => 'Dashboard', 'route' => 'dashboard'],
            ],
            'MASTER DATA' => [
                ['label' => 'Registri Inspector', 'route' => 'admin.inspectors.index', 'active' => 'admin.inspectors.*'],
                ['label' => 'Kategori Material', 'route' => 'admin.categories.index', 'active' => 'admin.categories.*'],
                ['label' => 'Parameter Dampak', 'route' => null],
            ],
            'VERIFIKASI & TRANSAKSI' => [
                ['label' => 'Verifikasi Mitra', 'route' => null],
                ['label' => 'Moderasi Listing', 'route' => null],
                ['label' => 'Penugasan Inspeksi', 'route' => null],
                ['label' => 'Sengketa & Klaim', 'route' => null],
            ],
            'PENGAWASAN' => [
                ['label' => 'Pengawasan DLH', 'route' => null],
                ['label' => 'Moderasi Ulasan', 'route' => null],
            ],
        ],
        'SELLER' => [
            'UTAMA' => [
                ['label' => 'Dashboard', 'route' => 'dashboard'],
            ],
            'KATALOG' => [
                ['label' => 'Listing Saya', 'route' => null],
                ['label' => 'Jadwal Pasokan', 'route' => null],
                ['label' => 'Papan Kebutuhan', 'route' => null],
            ],
            'TRANSAKSI' => [
                ['label' => 'RFQ Masuk', 'route' => 'rfq.index', 'active' => 'rfq.*'],
                ['label' => 'Purchase Order', 'route' => null],
                ['label' => 'Invoice', 'route' => null],
                ['label' => 'Pengiriman', 'route' => null],
                ['label' => 'Sengketa', 'route' => null],
            ],
            'AKUN' => [
                ['label' => 'Profil Perusahaan', 'route' => null],
                ['label' => 'Ulasan', 'route' => null],
            ],
        ],
        'BUYER' => [
            'UTAMA' => [
                ['label' => 'Dashboard', 'route' => 'dashboard'],
            ],
            'PASAR' => [
                ['label' => 'Katalog Material', 'route' => null],
                ['label' => 'Papan Kebutuhan', 'route' => null],
                ['label' => 'Daftar Pantau', 'route' => null],
            ],
            'TRANSAKSI' => [
                ['label' => 'RFQ & Negosiasi', 'route' => 'rfq.index', 'active' => 'rfq.*'],
                ['label' => 'Purchase Order', 'route' => null],
                ['label' => 'Invoice & Pembayaran', 'route' => null],
                ['label' => 'Penerimaan Barang', 'route' => null],
                ['label' => 'Sengketa', 'route' => null],
            ],
            'AKUN' => [
                ['label' => 'Profil Perusahaan', 'route' => null],
                ['label' => 'Ulasan', 'route' => null],
            ],
        ],
        'INSPECTOR' => [
            'UTAMA' => [
                ['label' => 'Dashboard', 'route' => 'dashboard'],
            ],
            'INSPEKSI' => [
                ['label' => 'Tugas Inspeksi', 'route' => null],
                ['label' => 'Verifikasi Dokumen', 'route' => null],
                ['label' => 'Laporan Mutu', 'route' => null],
            ],
            'AKUN' => [
                ['label' => 'Profil Kompetensi', 'route' => 'inspector.profile', 'active' => 'inspector.profile*'],
            ],
        ],
    ];

    $subtitles = [
        'ADMIN' => 'Admin Platform / DLH',
        'SELLER' => 'Seller / Perusahaan Penghasil',
        'BUYER' => 'Buyer / Perusahaan Pengolah',
        'INSPECTOR' => 'Inspector / Verifikator',
    ];

    $groups = $menus[$role] ?? ['UTAMA' => [['label' => 'Dashboard', 'route' => 'dashboard']]];
    $initials = collect(preg_split('/\s+/', trim($user->nama ?? '')))
        ->filter()->take(2)->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->implode('') ?: '?';
@endphp

<div class="flex h-full flex-col bg-secondary text-on-secondary">
    {{-- Logo --}}
    <a href="{{ route('dashboard') }}"
       class="mx-3 mt-4 flex items-center gap-3 rounded-sm px-2 py-2 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">
        <span class="flex size-9 shrink-0 items-center justify-center rounded-sm bg-primary text-headline-md text-on-primary" aria-hidden="true">S</span>
        <span class="min-w-0">
            <span class="block text-label-md font-semibold">SymbioNusa</span>
            <span class="block truncate text-label-sm text-on-secondary-muted">{{ $subtitles[$role] ?? 'Pengguna' }}</span>
        </span>
    </a>

    {{-- Navigasi --}}
    <nav class="mt-4 flex-1 overflow-y-auto px-3 pb-4" aria-label="Navigasi utama">
        @foreach ($groups as $group => $items)
            <p class="mt-4 mb-1 px-3 text-label-caps uppercase text-on-secondary-muted">{{ $group }}</p>
            <ul class="space-y-0.5">
                @foreach ($items as $item)
                    @php
                        $available = $item['route'] && Route::has($item['route']);
                        $active = $available && request()->routeIs($item['active'] ?? $item['route']);
                    @endphp
                    <li>
                        @if ($available)
                            <a href="{{ route($item['route']) }}"
                               @if ($active) aria-current="page" @endif
                               @class([
                                   'flex h-10 items-center rounded-sm px-3 transition-colors',
                                   'focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white',
                                   'bg-primary-container text-label-md text-on-primary-container' => $active,
                                   'text-body-md text-on-secondary hover:bg-secondary-hover' => ! $active,
                               ])>
                                {{ $item['label'] }}
                            </a>
                        @else
                            <span class="flex h-10 cursor-not-allowed items-center rounded-sm px-3 text-body-md text-on-secondary-muted opacity-70"
                                  aria-disabled="true" title="Belum tersedia">
                                {{ $item['label'] }}
                            </span>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endforeach
    </nav>

    {{-- Kartu pengguna --}}
    <div class="m-3 flex items-center gap-3 rounded-md bg-secondary-hover px-3 py-3">
        <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-primary-container text-label-sm text-on-primary-container" aria-hidden="true">{{ $initials }}</span>
        <div class="min-w-0 flex-1">
            <p class="truncate text-label-md">{{ $user->nama }}</p>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="text-label-sm text-on-secondary-muted underline-offset-2 hover:text-on-secondary hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">
                    Keluar
                </button>
            </form>
        </div>
    </div>
</div>

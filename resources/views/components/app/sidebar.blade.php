{{--
    Sidebar app shell (DESIGN.md › Layout, amandemen FND v8; STITCH_BRIEF.md › Roles and their sidebar navigation).
    Panel hijau hutan mengambang (radius 20). Nama menu WAJIB persis seperti STITCH_BRIEF.md.

    Menambah halaman baru: isi 'route' dengan nama route-nya (dan 'active' dengan pola routeIs).
    Item dengan 'route' => null belum punya halaman: tidak ditampilkan sebagai menu mati, tetapi dikumpulkan
    di satu baris "Segera hadir (n)" yang bisa dibuka untuk melihat namanya.
--}}
@php
    $user = auth()->user();
    // PBI-08 (SYM-12): perusahaan KEDUANYA bisa berganti peran aktif Seller/Buyer.
    $role = $user ? $user->activeRole() : '';
    $nextRole = $user?->company?->tipe === \App\Models\Company::TYPE_BOTH
        ? ($role === 'SELLER' ? 'BUYER' : 'SELLER')
        : null;

    $menus = [
        'ADMIN' => [
            'UTAMA' => [
                ['label' => 'Dashboard', 'icon' => 'grid', 'route' => 'dashboard'],
            ],
            'MASTER DATA' => [
                ['label' => 'Registri Inspector', 'icon' => 'shield', 'route' => 'admin.inspectors.index', 'active' => 'admin.inspectors.*'],
                ['label' => 'Kategori Material', 'icon' => 'layers', 'route' => 'admin.categories.index', 'active' => 'admin.categories.*'],
                ['label' => 'Parameter Dampak', 'icon' => 'leaf', 'route' => null],
            ],
            'VERIFIKASI & TRANSAKSI' => [
                ['label' => 'Verifikasi Mitra', 'icon' => 'building', 'route' => 'admin.companies.index', 'active' => 'admin.companies.*'],
                ['label' => 'Moderasi Listing', 'icon' => 'clipboard', 'route' => null],
                ['label' => 'Penugasan Inspeksi', 'icon' => 'calendar', 'route' => null],
                ['label' => 'Sengketa & Klaim', 'icon' => 'scale', 'route' => null],
            ],
            'PENGAWASAN' => [
                ['label' => 'Pengawasan DLH', 'icon' => 'eye', 'route' => null],
                ['label' => 'Moderasi Ulasan', 'icon' => 'star', 'route' => null],
            ],
        ],
        'SELLER' => [
            'UTAMA' => [
                ['label' => 'Dashboard', 'icon' => 'grid', 'route' => 'dashboard'],
            ],
            'KATALOG' => [
                ['label' => 'Listing Saya', 'icon' => 'layers', 'route' => null],
                ['label' => 'Jadwal Pasokan', 'icon' => 'calendar', 'route' => null],
                ['label' => 'Papan Kebutuhan', 'icon' => 'clipboard', 'route' => 'seller.demands.index', 'active' => 'seller.demands.*'],
            ],
            'TRANSAKSI' => [
                ['label' => 'RFQ Masuk', 'icon' => 'chat', 'route' => 'rfq.index', 'active' => 'rfq.*'],
                ['label' => 'Purchase Order', 'icon' => 'file', 'route' => null],
                ['label' => 'Invoice', 'icon' => 'receipt', 'route' => 'seller.invoices.index', 'active' => ['seller.invoices.*', 'seller.payments.*']],
                ['label' => 'Pengiriman', 'icon' => 'truck', 'route' => null],
                ['label' => 'Sengketa', 'icon' => 'scale', 'route' => null],
            ],
            'AKUN' => [
                ['label' => 'Profil Perusahaan', 'icon' => 'building', 'route' => 'companies.edit', 'active' => 'companies.*'],
                ['label' => 'Ulasan', 'icon' => 'star', 'route' => null],
            ],
        ],
        'BUYER' => [
            'UTAMA' => [
                ['label' => 'Dashboard', 'icon' => 'grid', 'route' => 'dashboard'],
            ],
            'PASAR' => [
                ['label' => 'Katalog Material', 'icon' => 'search', 'route' => null],
                ['label' => 'Kebutuhan Saya', 'icon' => 'clipboard', 'route' => 'buyer.demands.index', 'active' => 'buyer.demands.*'],
                ['label' => 'Daftar Pantau', 'icon' => 'eye', 'route' => null],
            ],
            'TRANSAKSI' => [
                ['label' => 'RFQ & Negosiasi', 'icon' => 'chat', 'route' => 'rfq.index', 'active' => 'rfq.*'],
                ['label' => 'Purchase Order', 'icon' => 'file', 'route' => null],
                ['label' => 'Invoice & Pembayaran', 'icon' => 'receipt', 'route' => 'buyer.invoices.index', 'active' => 'buyer.invoices.*'],
                ['label' => 'Penerimaan Barang', 'icon' => 'truck', 'route' => null],
                ['label' => 'Sengketa', 'icon' => 'scale', 'route' => null],
            ],
            'AKUN' => [
                ['label' => 'Profil Perusahaan', 'icon' => 'building', 'route' => 'companies.edit', 'active' => 'companies.*'],
                ['label' => 'Ulasan', 'icon' => 'star', 'route' => null],
            ],
        ],
        'INSPECTOR' => [
            'UTAMA' => [
                ['label' => 'Dashboard', 'icon' => 'grid', 'route' => 'dashboard'],
            ],
            'INSPEKSI' => [
                ['label' => 'Tugas Inspeksi', 'icon' => 'calendar', 'route' => null],
                ['label' => 'Verifikasi Dokumen', 'icon' => 'file', 'route' => null],
                ['label' => 'Laporan Mutu', 'icon' => 'flask', 'route' => null],
            ],
            'AKUN' => [
                ['label' => 'Profil Kompetensi', 'icon' => 'user', 'route' => 'inspector.profile', 'active' => 'inspector.profile*'],
            ],
        ],
    ];

    $roleLabels = [
        'ADMIN' => 'Admin / DLH',
        'SELLER' => 'Seller',
        'BUYER' => 'Buyer',
        'INSPECTOR' => 'Inspector',
    ];

    $groups = [];
    $upcoming = [];
    foreach ($menus[$role] ?? ['UTAMA' => [['label' => 'Dashboard', 'icon' => 'grid', 'route' => 'dashboard']]] as $group => $items) {
        foreach ($items as $item) {
            if ($item['route'] && Route::has($item['route'])) {
                $groups[$group][] = $item;
            } else {
                $upcoming[] = $item['label'];
            }
        }
    }
    $initials = collect(preg_split('/\s+/', trim($user->nama ?? '')))
        ->filter()->take(2)->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->implode('') ?: '?';
    $ring = 'focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-on-secondary';
@endphp

{{-- data-sidebar*: kait gaya sidebar Senja di Sawah (resources/css/sidebar-senja.css), berlaku di semua halaman. --}}
<div data-sidebar class="flex h-full flex-col overflow-hidden rounded-lg bg-secondary text-on-secondary">
    {{-- Logo --}}
    <a href="{{ route('dashboard') }}" class="mx-3.5 mt-5 flex items-center gap-2.5 rounded-sm px-2 py-1 {{ $ring }}">
        <span data-brand-mark class="flex size-9 shrink-0 items-center justify-center rounded-sm bg-primary-container text-on-primary-container" aria-hidden="true">
            <x-ui.icon name="loop" class="size-5" />
        </span>
        <span class="min-w-0">
            <span class="block text-headline-md">SymbioNusa</span>
            <span data-brand-tagline class="block text-body-sm text-on-secondary-muted">Sirkular industri Nusantara</span>
        </span>
    </a>

    {{-- Navigasi --}}
    <nav class="mt-6 flex-1 space-y-5 overflow-y-auto px-3.5 pb-4" aria-label="Navigasi utama">
        @foreach ($groups as $group => $items)
            <div>
                <p data-nav-label class="mb-1.5 px-2.5 text-label-group text-secondary-label">{{ \Illuminate\Support\Str::ucfirst(mb_strtolower($group)) }}</p>
                <ul class="space-y-0.5">
                    @foreach ($items as $item)
                        @php($active = request()->routeIs($item['active'] ?? $item['route']))
                        <li>
                            <a href="{{ route($item['route']) }}" @if ($active) aria-current="page" @endif
                               @class([
                                   'flex h-10 items-center gap-3 rounded-sm px-3 text-label-md transition-colors',
                                   $ring,
                                   'bg-primary-container font-semibold text-on-primary-container' => $active,
                                   'text-on-secondary hover:bg-secondary-hover' => ! $active,
                               ])>
                                <x-ui.icon :name="$item['icon']" />
                                <span class="min-w-0 flex-1 truncate">{{ $item['label'] }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach

        @if ($upcoming !== [])
            <div x-data="{ open: false }" x-id="['segera-hadir']">
                <button type="button" @click="open = ! open" :aria-expanded="open.toString()" :aria-controls="$id('segera-hadir')"
                        class="flex h-10 w-full items-center gap-3 rounded-sm bg-on-secondary/5 px-3 text-label-md text-on-secondary-muted hover:bg-secondary-hover {{ $ring }}">
                    <x-ui.icon name="clock" class="text-secondary-label" />
                    <span class="flex-1 text-left">Segera hadir</span>
                    <span data-nav-count class="text-body-sm text-secondary-label">{{ count($upcoming) }}</span>
                    <x-ui.icon name="chevron-down" class="size-4 text-secondary-label transition-transform" ::class="open && 'rotate-180'" />
                </button>
                <ul :id="$id('segera-hadir')" x-show="open" x-cloak class="mt-1 space-y-0.5 pl-11">
                    @foreach ($upcoming as $label)
                        <li class="py-1.5 text-body-sm text-on-secondary-muted">{{ $label }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </nav>

    {{-- Kartu pengguna --}}
    <div data-user-card class="m-3.5 space-y-2 rounded-md bg-on-secondary/5 p-2.5">
        <div class="flex items-center gap-2.5">
            <span data-avatar class="flex size-9 shrink-0 items-center justify-center rounded-full bg-primary-container text-body-sm font-semibold text-on-primary-container" aria-hidden="true">{{ $initials }}</span>
            <div class="min-w-0 flex-1">
                <p class="truncate text-label-md font-semibold">{{ $user->nama }}</p>
                <p class="truncate text-body-sm text-on-secondary-muted">{{ $roleLabels[$role] ?? 'Pengguna' }}</p>
            </div>
        </div>
        @if ($nextRole)
            <form method="POST" action="{{ route('companies.switch-role') }}">
                @csrf
                <input type="hidden" name="role" value="{{ $nextRole }}">
                <button type="submit" class="flex h-9 w-full items-center gap-2 rounded-sm px-2.5 text-label-md text-on-secondary hover:bg-secondary-hover {{ $ring }}">
                    <x-ui.icon name="loop" class="size-4" />
                    Beralih ke mode {{ ucfirst(strtolower($nextRole)) }}
                </button>
            </form>
        @endif
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="flex h-9 w-full items-center gap-2 rounded-sm bg-on-secondary/10 px-2.5 text-label-md text-on-secondary hover:bg-secondary-hover {{ $ring }}">
                <x-ui.icon name="logout" class="size-4" />
                Keluar
            </button>
        </form>
    </div>
</div>

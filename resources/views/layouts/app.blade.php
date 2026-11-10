<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'SymbioNusa').' - Platform Ekonomi Sirkular Non-B3')</title>

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <!-- Fallback bila aset Vite belum di-build -->
        <script src="https://cdn.tailwindcss.com"></script>
    @endif
    <style>
        [x-cloak] { display: none !important; }
        .sr-only { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; border-width: 0; }
        .focus\:not-sr-only:focus { position: static; width: auto; height: auto; padding: 0.5rem 1rem; margin: 0; overflow: visible; clip: auto; white-space: normal; }
    </style>
    @livewireStyles
</head>
<body class="h-full font-sans antialiased text-slate-800 bg-slate-50">
    <!-- Lewati navigasi (aksesibilitas) -->
    <a href="#main-content" class="sr-only focus:not-sr-only bg-emerald-700 text-white z-50 rounded-br-lg shadow-lg">
        Lewati ke konten utama
    </a>

    @php
        $user = Auth::user();
        $role = strtoupper($user->role ?? '');
        $dashboardUrl = route('dashboard');

        // Navigasi modular per peran. Anchor (#...) menunjuk ke bagian di dasbor utama.
        $navItems = match ($role) {
            'ADMIN' => [
                ['label' => 'Perusahaan', 'href' => $dashboardUrl.'#perusahaan', 'active' => false],
                ['label' => 'Master Kategori', 'href' => route('admin.categories.index'), 'active' => request()->routeIs('admin.categories.*')],
                ['label' => 'Sengketa', 'href' => $dashboardUrl.'#sengketa', 'active' => false],
            ],
            'SELLER' => [
                ['label' => 'Listing Saya', 'href' => $dashboardUrl.'#katalog', 'active' => false],
                ['label' => 'Pesanan Masuk', 'href' => $dashboardUrl.'#pesanan', 'active' => false],
            ],
            'BUYER' => [
                ['label' => 'Kebutuhan Bahan', 'href' => $dashboardUrl.'#kebutuhan', 'active' => false],
                ['label' => 'Pesanan Pembelian', 'href' => $dashboardUrl.'#pesanan', 'active' => false],
            ],
            'INSPECTOR' => [
                ['label' => 'Tugas Inspeksi', 'href' => $dashboardUrl.'#tugas', 'active' => false],
            ],
            default => [],
        };

        $roleBadgeClasses = match ($role) {
            'ADMIN' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            'SELLER' => 'bg-blue-100 text-blue-800 border-blue-200',
            'BUYER' => 'bg-purple-100 text-purple-800 border-purple-200',
            'INSPECTOR' => 'bg-amber-100 text-amber-800 border-amber-200',
            default => 'bg-slate-100 text-slate-800 border-slate-200',
        };
    @endphp

    <div class="min-h-full flex flex-col" x-data="{ mobileMenuOpen: false }">
        <!-- App Shell Header -->
        <header class="bg-white border-b border-slate-200 sticky top-0 z-40 shadow-xs">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <!-- Brand & Navigasi Utama -->
                    <div class="flex items-center space-x-6 lg:space-x-8">
                        <a href="{{ $dashboardUrl }}" class="flex items-center space-x-3 group rounded-lg p-1 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <div class="h-10 w-10 rounded-xl bg-emerald-700 flex items-center justify-center text-white font-black text-xl shadow-md group-hover:bg-emerald-800 transition">
                                SN
                            </div>
                            <div class="flex flex-col">
                                <span class="font-extrabold text-slate-900 text-lg leading-tight tracking-tight">SymbioNusa</span>
                                <span class="text-xs font-medium text-slate-600">Ekonomi Sirkular Industri Non-B3</span>
                            </div>
                        </a>

                        @auth
                            <nav class="hidden md:flex items-center space-x-1" aria-label="Navigasi Utama">
                                <a href="{{ $dashboardUrl }}" class="px-3 py-2 rounded-lg text-sm font-semibold transition focus:outline-none focus:ring-2 focus:ring-emerald-500 {{ request()->routeIs('dashboard') ? 'bg-emerald-50 text-emerald-800' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}"
                                   @if(request()->routeIs('dashboard')) aria-current="page" @endif>
                                    Dasbor
                                </a>
                                @foreach ($navItems as $item)
                                    <a href="{{ $item['href'] }}" class="px-3 py-2 rounded-lg text-sm font-semibold transition focus:outline-none focus:ring-2 focus:ring-emerald-500 {{ $item['active'] ? 'bg-emerald-50 text-emerald-800' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}"
                                       @if($item['active']) aria-current="page" @endif>
                                        {{ $item['label'] }}
                                    </a>
                                @endforeach
                            </nav>
                        @endauth
                    </div>

                    <!-- Profil Pengguna & Keluar -->
                    <div class="flex items-center space-x-3">
                        @auth
                            <div class="hidden sm:flex flex-col text-right">
                                <span class="text-sm font-bold text-slate-800 leading-tight">{{ $user->nama }}</span>
                                @if ($user->company)
                                    <span class="text-xs text-slate-600">{{ $user->company->nama }}</span>
                                @endif
                            </div>
                            <span class="hidden sm:inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $roleBadgeClasses }}">
                                {{ $role }}
                            </span>

                            <form method="POST" action="{{ route('logout') }}" class="inline">
                                @csrf
                                <button type="submit" class="text-sm font-semibold text-rose-700 hover:text-rose-900 bg-rose-50 hover:bg-rose-100 border border-rose-200 px-3.5 py-1.5 rounded-lg transition focus:outline-none focus:ring-2 focus:ring-rose-500" aria-label="Keluar dari akun">
                                    Keluar
                                </button>
                            </form>

                            <!-- Tombol menu mobile -->
                            <button @click="mobileMenuOpen = !mobileMenuOpen" type="button" class="md:hidden p-2 rounded-md text-slate-600 hover:text-slate-800 hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                    aria-label="Menu navigasi" aria-controls="mobile-menu" :aria-expanded="mobileMenuOpen.toString()">
                                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                                    <path x-show="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                    <path x-show="mobileMenuOpen" x-cloak stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        @else
                            <a href="{{ route('login') }}" class="text-sm font-semibold text-emerald-800 hover:text-emerald-900 bg-emerald-50 px-3.5 py-1.5 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">Masuk</a>
                        @endauth
                    </div>
                </div>
            </div>

            @auth
                <!-- Drawer navigasi mobile -->
                <nav id="mobile-menu" x-show="mobileMenuOpen" x-cloak class="md:hidden border-t border-slate-200 bg-white px-4 pt-2 pb-4 space-y-1" aria-label="Navigasi Mobile">
                    <a href="{{ $dashboardUrl }}" class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('dashboard') ? 'text-emerald-800 bg-emerald-50' : 'text-slate-700 hover:bg-slate-50' }}">
                        Dasbor
                    </a>
                    @foreach ($navItems as $item)
                        <a href="{{ $item['href'] }}" class="block px-3 py-2 rounded-md text-base font-medium {{ $item['active'] ? 'text-emerald-800 bg-emerald-50' : 'text-slate-700 hover:bg-slate-50' }}">
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                    <div class="sm:hidden px-3 pt-3 mt-2 border-t border-slate-100 text-sm text-slate-600">
                        <span class="font-bold text-slate-800">{{ $user->nama }}</span>
                        <span class="ml-1 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold border {{ $roleBadgeClasses }}">{{ $role }}</span>
                    </div>
                </nav>
            @endauth
        </header>

        <!-- Banner notifikasi global -->
        @if (session('success') || session('error') || $errors->any())
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
                @if (session('success'))
                    <div class="p-4 mb-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center space-x-2 shadow-xs" role="alert">
                        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span class="text-sm font-medium">{{ session('success') }}</span>
                    </div>
                @endif

                @if (session('error'))
                    <div class="p-4 mb-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 flex items-center space-x-2 shadow-xs" role="alert">
                        <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        <span class="text-sm font-medium">{{ session('error') }}</span>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="p-4 mb-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 shadow-xs" role="alert">
                        <div class="flex items-start space-x-2">
                            <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <div>
                                <span class="text-sm font-bold block mb-1">Terdapat kesalahan validasi:</span>
                                <ul class="list-disc list-inside text-xs space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        <!-- Konten Utama -->
        <main id="main-content" class="flex-1 pb-12" tabindex="-1">
            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t border-slate-200 mt-auto py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row justify-between items-center text-xs text-slate-600 gap-2">
                <div>
                    <span class="font-bold text-slate-700">SymbioNusa</span> &middot; Platform B2B Ekonomi Sirkular Sisa Produksi Industri Non-B3.
                </div>
                <div>
                    Kelompok SI-48-07 B-A &copy; {{ date('Y') }}. Hak Cipta Dilindungi.
                </div>
            </div>
        </footer>
    </div>

    @livewireScripts
</body>
</html>

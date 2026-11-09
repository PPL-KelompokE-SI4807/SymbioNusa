<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'SymbioNusa - Platform Ekonomi Sirkular Non-B3')</title>
    <!-- Tailwind CSS CDN for styling -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Fallback core styling ensuring clean presentation even without external CSS */
        body { font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; }
        .sr-only { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; border-width: 0; }
        .focus\:not-sr-only:focus { position: static; width: auto; height: auto; padding: 0.5rem 1rem; margin: 0; overflow: visible; clip: auto; white-space: normal; }
    </style>
    @livewireStyles
</head>
<body class="bg-gray-50 text-gray-900 font-sans antialiased min-h-screen flex flex-col">
    <!-- Skip to main content link for accessibility -->
    <a href="#main-content" class="sr-only focus:not-sr-only bg-emerald-700 text-white z-50 rounded-br-lg shadow-lg">
        Lewati ke konten utama
    </a>

    <!-- Navigation Header -->
    <header class="bg-white border-b border-gray-200 sticky top-0 z-40 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Brand Logo & Name -->
                <div class="flex items-center space-x-6">
                    <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 group focus:outline-none focus:ring-2 focus:ring-emerald-500 rounded-lg p-1" >
                        <div class="h-10 w-10 rounded-xl bg-emerald-600 flex items-center justify-center text-white font-extrabold text-xl shadow-md group-hover:bg-emerald-700 transition">
                            SN
                        </div>
                        <div>
                            <span class="font-extrabold text-gray-900 text-lg tracking-tight block">SymbioNusa</span>
                            <span class="block text-xs text-gray-500 font-medium">Ekonomi Sirkular Industri Non-B3</span>
                        </div>
                    </a>

                    <!-- Navigation Links for Desktop -->
                    <nav class="hidden md:flex items-center space-x-2" aria-label="Navigasi Utama">
                        <a href="{{ route('dashboard') }}" class="px-3 py-2 rounded-lg text-sm font-semibold {{ request()->routeIs('dashboard') ? 'bg-emerald-50 text-emerald-700' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100' }} transition">
                            Dashboard
                        </a>
                        @if(Auth::check() && Auth::user()->role === 'ADMIN')
                            <a href="{{ route('admin.categories.index') }}" class="px-3 py-2 rounded-lg text-sm font-semibold {{ request()->routeIs('admin.categories.*') ? 'bg-emerald-50 text-emerald-700' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100' }} transition">
                                Master Kategori
                            </a>
                        @endif
                    </nav>
                </div>

                <!-- User Profile & Logout -->
                <div class="flex items-center space-x-3">
                    @auth
                        <div class="hidden sm:flex flex-col text-right">
                            <span class="text-sm font-bold text-gray-800 leading-tight">{{ Auth::user()->nama }}</span>
                            <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 inline-block w-fit ml-auto mt-0.5">
                                {{ Auth::user()->role }}
                            </span>
                        </div>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-sm font-semibold text-rose-700 hover:text-rose-900 bg-rose-50 hover:bg-rose-100 border border-rose-200 px-3.5 py-1.5 rounded-lg transition focus:outline-none focus:ring-2 focus:ring-rose-500" aria-label="Keluar dari akun">
                                Keluar
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="text-sm font-semibold text-emerald-700 hover:text-emerald-900 bg-emerald-50 px-3.5 py-1.5 rounded-lg">Masuk</a>
                    @endauth
                </div>
            </div>

            <!-- Mobile Navigation Bar -->
            <nav class="md:hidden flex items-center space-x-2 py-2 border-t border-gray-100" aria-label="Navigasi Mobile">
                <a href="{{ route('dashboard') }}" class="px-3 py-1.5 rounded-md text-xs font-semibold {{ request()->routeIs('dashboard') ? 'bg-emerald-50 text-emerald-700' : 'text-gray-600 hover:bg-gray-100' }}">
                    Dashboard
                </a>
                @if(Auth::check() && Auth::user()->role === 'ADMIN')
                    <a href="{{ route('admin.categories.index') }}" class="px-3 py-1.5 rounded-md text-xs font-semibold {{ request()->routeIs('admin.categories.*') ? 'bg-emerald-50 text-emerald-700' : 'text-gray-600 hover:bg-gray-100' }}">
                        Master Kategori
                    </a>
                @endif
            </nav>
        </div>
    </header>

    <!-- Global Alert Banners -->
    @if(session('success') || session('error') || $errors->any())
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
            @if(session('success'))
                <div class="p-4 mb-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between shadow-xs" role="alert">
                    <div class="flex items-center space-x-2">
                        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span class="text-sm font-medium">{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="p-4 mb-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 flex items-center justify-between shadow-xs" role="alert">
                    <div class="flex items-center space-x-2">
                        <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        <span class="text-sm font-medium">{{ session('error') }}</span>
                    </div>
                </div>
            @endif

            @if($errors->any())
                <div class="p-4 mb-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 shadow-xs" role="alert">
                    <div class="flex items-start space-x-2">
                        <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <div>
                            <span class="text-sm font-bold block mb-1">Terdapat kesalahan validasi:</span>
                            <ul class="list-disc list-inside text-xs space-y-1">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @endif

    <!-- Main Content Area -->
    <main id="main-content" class="flex-1 pb-12" tabindex="-1">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 py-6 text-center text-xs text-gray-500 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row justify-between items-center gap-2">
            <span>&copy; {{ date('Y') }} SymbioNusa Kelompok SI-48-07 B-A. Hak Cipta Dilindungi.</span>
            <span>Platform Ekonomi Sirkular Terpercaya Industri Non-B3 Indonesia.</span>
        </div>
    </footer>

    @livewireScripts
</body>
</html>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SymbioNusa - Dashboard Utama</title>
    @livewireStyles
</head>
<body class="bg-gray-50 font-sans antialiased">
    <div class="min-h-screen flex flex-col">
        <!-- Navigation Header -->
        <header class="bg-white border-b border-gray-100 shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex justify-between items-center">
                <div class="flex items-center space-x-3">
                    <div class="h-10 w-10 rounded-xl bg-emerald-600 flex items-center justify-center text-white font-extrabold text-xl shadow-md">
                        SN
                    </div>
                    <div>
                        <span class="font-extrabold text-gray-900 text-lg tracking-tight">SymbioNusa</span>
                        <span class="block text-xs text-gray-500 font-medium">Ekonomi Sirkular Industri Non-B3</span>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-sm font-medium text-gray-700">{{ Auth::user()->nama ?? 'Pengguna' }} ({{ Auth::user()->role ?? '-' }})</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-sm font-medium text-rose-600 hover:text-rose-800 bg-rose-50 hover:bg-rose-100 px-3 py-1.5 rounded-lg transition">
                            Keluar
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1">
            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t border-gray-100 py-6 text-center text-xs text-gray-500">
            &copy; {{ date('Y') }} SymbioNusa Kelompok SI-48-07 B-A. Hak Cipta Dilindungi.
        </footer>
    </div>
    @livewireScripts
</body>
</html>

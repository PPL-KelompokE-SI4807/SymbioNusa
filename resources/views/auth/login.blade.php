<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk - {{ config('app.name', 'SymbioNusa') }}</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <!-- Fallback bila aset Vite belum di-build -->
        <script src="https://cdn.tailwindcss.com"></script>
    @endif
</head>
<body class="min-h-full font-sans antialiased text-slate-800 bg-slate-50 flex items-center justify-center px-4 py-8">
    <main class="max-w-md w-full bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
        <div class="p-6 sm:p-8">
            <a href="{{ url('/') }}" class="flex items-center space-x-3 mb-6 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 w-fit">
                <div class="h-10 w-10 rounded-xl bg-emerald-700 flex items-center justify-center text-white font-extrabold text-xl shadow-md">
                    SN
                </div>
                <div>
                    <span class="block font-extrabold text-slate-900 text-xl tracking-tight">SymbioNusa</span>
                    <span class="block text-xs text-slate-600 font-medium">Ekonomi Sirkular Industri Non-B3</span>
                </div>
            </a>

            <h1 class="text-lg font-bold text-slate-900 mb-1">Masuk ke Platform</h1>
            <p class="text-sm text-slate-600 mb-6">Gunakan email dan kata sandi akun terdaftar Anda.</p>

            @if (session('success'))
                <div class="mb-4 p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-sm font-medium text-emerald-800" role="status">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 p-3 rounded-lg bg-rose-50 border border-rose-200 text-sm font-medium text-rose-800" role="alert">
                    {{ session('error') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login.store') }}" class="space-y-4" novalidate>
                @csrf
                <div>
                    <label for="email" class="block text-sm font-semibold text-slate-700 mb-1">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                           @error('email') aria-invalid="true" aria-describedby="email-error" @enderror
                           class="w-full px-3.5 py-2.5 text-sm border rounded-lg outline-none transition focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 @error('email') border-rose-400 @else border-slate-300 @enderror">
                    @error('email')
                        <p id="email-error" class="mt-1 text-sm text-rose-700">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-semibold text-slate-700 mb-1">Kata Sandi</label>
                    <input type="password" id="password" name="password" required autocomplete="current-password"
                           @error('password') aria-invalid="true" aria-describedby="password-error" @enderror
                           class="w-full px-3.5 py-2.5 text-sm border rounded-lg outline-none transition focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 @error('password') border-rose-400 @else border-slate-300 @enderror">
                    @error('password')
                        <p id="password-error" class="mt-1 text-sm text-rose-700">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="w-full py-2.5 px-4 rounded-lg bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-sm shadow-md transition focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-600">
                    Masuk
                </button>
            </form>

            @if (Route::has('login.demo'))
                <section class="mt-8 pt-6 border-t border-slate-200" aria-labelledby="demo-login-title">
                    <h2 id="demo-login-title" class="text-xs font-bold uppercase tracking-wider text-slate-600 mb-1 text-center">
                        Akses cepat akun demo
                    </h2>
                    <p class="text-xs text-slate-600 mb-3 text-center">Hanya tampil di lingkungan lokal (APP_ENV=local).</p>
                    <div class="grid grid-cols-2 gap-2 text-sm">
                        @foreach ([
                            'admin' => ['Admin / DLH', 'border-emerald-200 bg-emerald-50 hover:bg-emerald-100 text-emerald-900'],
                            'seller' => ['Seller', 'border-blue-200 bg-blue-50 hover:bg-blue-100 text-blue-900'],
                            'buyer' => ['Buyer', 'border-purple-200 bg-purple-50 hover:bg-purple-100 text-purple-900'],
                            'inspector' => ['Inspector', 'border-amber-200 bg-amber-50 hover:bg-amber-100 text-amber-900'],
                        ] as $demoRole => [$demoLabel, $demoClasses])
                            <form method="POST" action="{{ route('login.demo', $demoRole) }}">
                                @csrf
                                <button type="submit" class="w-full p-2.5 rounded-lg border font-semibold text-center transition focus:outline-none focus:ring-2 focus:ring-emerald-500 {{ $demoClasses }}">
                                    {{ $demoLabel }}
                                </button>
                            </form>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>
    </main>
</body>
</html>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Pengiriman & Manifes') - SymbioNusa</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f4f4f5] font-sans text-[#18181b] antialiased">
    @include('partials.sidebar')

    <main class="md:pl-60">
        {{-- Top bar mobile --}}
        <div class="sticky top-0 z-20 flex items-center gap-3 border-b border-zinc-200 bg-white px-4 py-3 md:hidden">
            <button type="button" data-sidebar-toggle class="btn-secondary px-3 py-1.5" aria-label="Buka menu">Menu</button>
            <span class="text-sm font-semibold">SymbioNusa</span>
        </div>

        <div class="mx-auto max-w-6xl px-4 py-6 sm:px-8 sm:py-8">
            @if (session('success'))
                <div class="mb-6 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
                    <p class="font-semibold">Terdapat {{ $errors->count() }} kesalahan:</p>
                    <ul class="mt-1 list-inside list-disc">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    @stack('scripts')
</body>
</html>

<!DOCTYPE html>
<html lang="id" class="h-full bg-background">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk · {{ config('app.name', 'SymbioNusa') }}</title>
    @fonts
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="flex min-h-full items-center justify-center bg-background px-4 py-8 font-sans text-body-md text-on-surface antialiased">
    <main class="w-full max-w-md rounded-md border border-outline bg-surface p-6 sm:p-8">
        <a href="{{ url('/') }}"
           class="mb-6 flex w-fit items-center gap-3 rounded-sm focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus-ring">
            <span class="flex size-10 items-center justify-center rounded-sm bg-primary text-headline-md text-on-primary" aria-hidden="true">S</span>
            <span>
                <span class="block text-headline-md">SymbioNusa</span>
                <span class="block text-body-sm text-on-surface-muted">Ekonomi Sirkular Industri Non-B3</span>
            </span>
        </a>

        <h1 class="text-headline-md">Masuk ke Platform</h1>
        <p class="mt-1 mb-6 text-body-md text-on-surface-muted">Gunakan email dan kata sandi akun terdaftar Anda.</p>

        @if (session('success'))
            <x-ui.alert variant="success" class="mb-4">{{ session('success') }}</x-ui.alert>
        @endif
        @if (session('error'))
            <x-ui.alert variant="danger" class="mb-4">{{ session('error') }}</x-ui.alert>
        @endif

        <form method="POST" action="{{ route('login.store') }}" class="space-y-4" novalidate>
            @csrf
            <x-ui.field label="Email" for="email" name="email">
                <x-ui.input type="email" id="email" name="email" :value="old('email')" required autofocus autocomplete="username"
                            :aria-describedby="$errors->has('email') ? 'email-error' : null" />
            </x-ui.field>

            <x-ui.field label="Kata Sandi" for="password" name="password">
                <x-ui.input type="password" id="password" name="password" required autocomplete="current-password"
                            :aria-describedby="$errors->has('password') ? 'password-error' : null" />
            </x-ui.field>

            <x-ui.button type="submit" class="w-full">Masuk</x-ui.button>
        </form>

        @if (Route::has('login.demo'))
            <section class="mt-8 border-t border-outline pt-6" aria-labelledby="demo-login-title">
                <h2 id="demo-login-title" class="text-center text-label-caps uppercase text-on-surface-muted">Akses cepat akun demo</h2>
                <p class="mt-1 mb-3 text-center text-body-sm text-on-surface-muted">Hanya tampil di lingkungan lokal (APP_ENV=local).</p>
                <div class="grid grid-cols-2 gap-2">
                    @foreach (['admin' => 'Admin / DLH', 'seller' => 'Seller', 'buyer' => 'Buyer', 'inspector' => 'Inspector'] as $demoRole => $demoLabel)
                        <form method="POST" action="{{ route('login.demo', $demoRole) }}">
                            @csrf
                            <x-ui.button type="submit" variant="secondary" class="w-full">{{ $demoLabel }}</x-ui.button>
                        </form>
                    @endforeach
                </div>
            </section>
        @endif
    </main>
</body>
</html>

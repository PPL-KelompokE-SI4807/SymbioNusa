@extends('layouts.guest')

@section('title', 'Masuk')

@section('content')
    <h1 class="text-headline-md">Masuk ke Platform</h1>
    <p class="mt-1 mb-6 text-body-md text-on-surface-muted">Gunakan email dan kata sandi akun terdaftar Anda.</p>

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

        <div class="flex justify-end">
            <a href="{{ route('password.request') }}"
               class="text-label-md text-primary hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus-ring">
                Lupa kata sandi?
            </a>
        </div>

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
@endsection

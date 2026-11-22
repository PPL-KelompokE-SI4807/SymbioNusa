@extends('layouts.guest')

@section('title', 'Masuk')

@section('content')
    @php
        // Galat kredensial dan pembatasan percobaan dipasang LoginRequest di kunci "email"; tampilkan sebagai
        // satu alert di atas form (DESIGN.md › Forms), bukan sebagai galat field Email.
        $emailError = $errors->first('email');
        $credentialError = $emailError !== '' && (str_starts_with($emailError, 'Email atau kata sandi salah') || str_starts_with($emailError, 'Terlalu banyak percobaan'));
    @endphp

    <h1 class="text-headline-lg">Masuk</h1>
    <p class="mt-2 text-body-lg text-on-surface-muted">Gunakan email dan kata sandi akun perusahaan Anda.</p>

    @if ($credentialError)
        <x-ui.alert variant="danger" class="mt-6" title="{{ str_starts_with($emailError, 'Terlalu') ? 'Terlalu banyak percobaan masuk.' : 'Email atau kata sandi salah.' }}">
            @if (str_starts_with($emailError, 'Terlalu'))
                {{ $emailError }}
            @else
                Periksa kembali isian Anda. Akun yang belum diverifikasi Admin juga belum bisa masuk.
            @endif
        </x-ui.alert>
    @endif

    <form method="POST" action="{{ route('login.store') }}" class="mt-7 space-y-5" novalidate>
        @csrf
        <x-ui.field label="Email" for="email" :error="$credentialError ? '' : null" :name="$credentialError ? null : 'email'">
            <x-ui.input type="email" id="email" name="email" icon="mail" :value="old('email')" required
                        :autofocus="! $credentialError" autocomplete="username" placeholder="nama@perusahaan.co.id"
                        :invalid="$credentialError ? false : null"
                        :aria-describedby="$errors->has('email') && ! $credentialError ? 'email-error' : null" />
        </x-ui.field>

        <x-ui.field label="Kata sandi" for="password" name="password">
            <x-slot:aside>
                <a href="{{ route('password.request') }}"
                   class="rounded-sm text-label-md font-semibold text-primary hover:underline underline-offset-4 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus-ring">
                    Lupa kata sandi?
                </a>
            </x-slot:aside>
            <x-ui.password-input id="password" name="password" required autocomplete="current-password"
                                 placeholder="Masukkan kata sandi" :autofocus="$credentialError"
                                 :aria-describedby="$errors->has('password') ? 'password-error' : null" />
        </x-ui.field>

        <x-ui.button type="submit" size="xl" class="w-full">Masuk</x-ui.button>
    </form>

    @if (config('app.demo_login'))
        <section class="mt-8" aria-labelledby="demo-login-title">
            <div class="flex items-center gap-3">
                <span class="h-px flex-1 bg-outline" aria-hidden="true"></span>
                <h2 id="demo-login-title" class="text-body-sm text-on-surface-subtle">Akun demo · masuk tanpa sandi</h2>
                <span class="h-px flex-1 bg-outline" aria-hidden="true"></span>
            </div>
            <div class="mt-4 grid grid-cols-2 gap-2.5">
                @foreach (['admin' => ['Admin / DLH', 'AD'], 'seller' => ['Seller', 'SL'], 'buyer' => ['Buyer', 'BY'], 'inspector' => ['Inspector', 'IN']] as $demoRole => [$demoLabel, $demoInitials])
                    <form method="POST" action="{{ route('login.demo', $demoRole) }}">
                        @csrf
                        <button type="submit"
                                class="flex h-12 w-full items-center gap-2.5 rounded-sm bg-surface-muted px-2 text-label-md font-semibold text-on-surface hover:bg-outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus-ring">
                            <x-ui.monogram :name="$demoLabel" :initials="$demoInitials" size="sm" />
                            {{ $demoLabel }}
                        </button>
                    </form>
                @endforeach
            </div>
        </section>
    @endif
@endsection

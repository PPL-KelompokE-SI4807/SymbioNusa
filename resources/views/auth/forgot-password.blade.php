@extends('layouts.guest')

@section('title', 'Lupa Kata Sandi')

@section('content')
    <h1 class="text-headline-md">Lupa Kata Sandi</h1>
    <p class="mt-1 mb-6 text-body-md text-on-surface-muted">
        Masukkan email akun Anda. Kami akan mengirim tautan untuk membuat kata sandi baru.
    </p>

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4" novalidate>
        @csrf
        <x-ui.field label="Email" for="email" name="email">
            <x-ui.input type="email" id="email" name="email" :value="old('email')" required autofocus autocomplete="username"
                        :aria-describedby="$errors->has('email') ? 'email-error' : null" />
        </x-ui.field>

        <x-ui.button type="submit" class="w-full">Kirim Tautan Reset</x-ui.button>
    </form>

    <p class="mt-6 text-center">
        <a href="{{ route('login') }}"
           class="text-label-md text-primary hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus-ring">
            Kembali ke halaman masuk
        </a>
    </p>
@endsection

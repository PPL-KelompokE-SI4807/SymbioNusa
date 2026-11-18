@extends('layouts.guest')

@section('title', 'Atur Ulang Kata Sandi')

@section('content')
    <h1 class="text-headline-md">Atur Ulang Kata Sandi</h1>
    <p class="mt-1 mb-6 text-body-md text-on-surface-muted">Buat kata sandi baru untuk akun Anda.</p>

    <form method="POST" action="{{ route('password.store') }}" class="space-y-4" novalidate>
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <x-ui.field label="Email" for="email" name="email">
            <x-ui.input type="email" id="email" name="email" :value="old('email', $email)" required autocomplete="username"
                        :aria-describedby="$errors->has('email') ? 'email-error' : null" />
        </x-ui.field>

        <x-ui.field label="Kata sandi baru" for="password" name="password" helper="Minimal 8 karakter." required>
            <x-ui.input type="password" id="password" name="password" required autofocus autocomplete="new-password"
                        aria-describedby="password-helper{{ $errors->has('password') ? ' password-error' : '' }}" />
        </x-ui.field>

        <x-ui.field label="Ulangi kata sandi baru" for="password_confirmation" required>
            <x-ui.input type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password" />
        </x-ui.field>

        <x-ui.button type="submit" class="w-full">Simpan Kata Sandi Baru</x-ui.button>
    </form>
@endsection

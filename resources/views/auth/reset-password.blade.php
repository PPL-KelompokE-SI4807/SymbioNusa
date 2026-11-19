@extends('layouts.guest')

@section('title', 'Atur ulang kata sandi')

@section('content')
    @php
        // Aturan "confirmed" memasang galat di field password; tampilkan di field konfirmasi (tempat salahnya).
        $passwordError = $errors->first('password');
        $mismatch = $passwordError === 'Konfirmasi kata sandi tidak sama.';
    @endphp

    <h1 class="text-headline-lg">Buat kata sandi baru</h1>
    @if ($email)
        <p class="mt-2 text-body-lg text-on-surface-muted">Untuk akun <span class="font-semibold text-on-surface">{{ $email }}</span></p>
    @else
        <p class="mt-2 text-body-lg text-on-surface-muted">Atur ulang kata sandi akun SymbioNusa Anda.</p>
    @endif

    <form method="POST" action="{{ route('password.store') }}" class="mt-7 space-y-5" novalidate>
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        @if ($email && ! $errors->has('email'))
            <input type="hidden" name="email" value="{{ old('email', $email) }}">
        @else
            <x-ui.field label="Email" for="email" name="email">
                <x-ui.input type="email" id="email" name="email" icon="mail" :value="old('email', $email)" required autocomplete="username"
                            :aria-describedby="$errors->has('email') ? 'email-error' : null" />
            </x-ui.field>
        @endif

        <x-ui.field label="Kata sandi baru" for="password" name="password" helper="Minimal 8 karakter." :error="$mismatch ? '' : null">
            <x-ui.password-input id="password" name="password" required :autofocus="! $mismatch" autocomplete="new-password"
                                 :invalid="$mismatch ? false : null"
                                 aria-describedby="{{ $errors->has('password') && ! $mismatch ? 'password-error' : 'password-helper' }}" />
        </x-ui.field>

        <x-ui.field label="Ulangi kata sandi baru" for="password_confirmation" :error="$mismatch ? $passwordError : null">
            <x-ui.password-input id="password_confirmation" name="password_confirmation" required autocomplete="new-password"
                                 :invalid="$mismatch" :autofocus="$mismatch"
                                 :aria-describedby="$mismatch ? 'password_confirmation-error' : null" />
        </x-ui.field>

        <x-ui.button type="submit" size="xl" class="w-full">Simpan kata sandi</x-ui.button>
    </form>
@endsection

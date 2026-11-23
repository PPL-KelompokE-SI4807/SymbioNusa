@extends('layouts.guest')

@section('title', 'Lupa kata sandi')

@section('content')
    <a href="{{ route('login') }}"
       class="inline-flex items-center gap-1.5 rounded-sm text-label-md font-semibold text-primary hover:underline underline-offset-4 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus-ring">
        <x-ui.icon name="arrow-left" class="size-4" />
        Kembali ke halaman masuk
    </a>

    @if (session('success'))
        {{-- Setelah dikirim: form diganti instruksi (pesan generik dari controller, tidak membocorkan akun). --}}
        <div class="mt-7 flex size-14 items-center justify-center rounded-md bg-primary-container text-on-primary-container" aria-hidden="true">
            <x-ui.icon name="mail" class="size-7" />
        </div>
        <h1 class="mt-5 text-headline-lg">Periksa email Anda</h1>
        <p class="mt-2 text-body-lg text-on-surface-muted" role="status">{{ session('success') }} Tautan berlaku 60 menit.</p>

        <x-ui.button variant="secondary" size="xl" :href="route('password.request')" class="mt-7 w-full">Kirim ulang tautan</x-ui.button>
        <p class="mt-3 text-body-sm text-on-surface-subtle">Tidak ada di kotak masuk? Periksa folder spam.</p>
    @else
        <h1 class="mt-7 text-headline-lg">Lupa kata sandi</h1>
        <p class="mt-2 text-body-lg text-on-surface-muted">
            Masukkan email akun Anda. Kami kirim tautan untuk membuat kata sandi baru.
        </p>

        <form method="POST" action="{{ route('password.email') }}" class="mt-7 space-y-5" novalidate>
            @csrf
            <x-ui.field label="Email" for="email" name="email">
                <x-ui.input type="email" id="email" name="email" icon="mail" :value="old('email')" required autofocus
                            autocomplete="username" placeholder="nama@perusahaan.co.id"
                            :aria-describedby="$errors->has('email') ? 'email-error' : null" />
            </x-ui.field>

            <x-ui.button type="submit" size="xl" class="w-full">Kirim tautan</x-ui.button>
        </form>
    @endif
@endsection

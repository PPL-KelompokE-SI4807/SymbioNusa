<!DOCTYPE html>
{{--
    Layout halaman tamu (masuk, lupa kata sandi, atur ulang) — DESIGN.md › Layout › Auth pages (amandemen FND v8).
    Desktop: panel foto material 640 px di kiri (lapisan hijau hutan, pernyataan, chip material), kartu form di kanan.
    Mobile: pita foto di atas, kartu form menumpuk di bawahnya.
    Halaman memakai @extends('layouts.guest'), @section('title', ...), @section('content').
    Foto dan lisensinya: docs/design/CREDITS-FOTO.md.
--}}
<html lang="id" class="h-full bg-background">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Masuk') · {{ config('app.name', 'SymbioNusa') }}</title>
    @fonts
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    {{-- Alpine (lewat Livewire) untuk tombol "Tampilkan kata sandi". --}}
    @livewireStyles
</head>
<body class="min-h-full bg-background font-sans text-body-md text-on-surface antialiased">
    @php
        $chips = [
            ['plastik.jpg', 'Plastik'],
            ['kain.jpg', 'Kain perca'],
            ['serbuk-kayu.jpg', 'Serbuk kayu'],
            ['ampas-tahu.jpg', 'Ampas tahu'],
        ];
    @endphp

    <div class="lg:flex lg:min-h-screen">
        {{-- Panel foto --}}
        <aside class="relative isolate overflow-hidden bg-secondary text-on-secondary lg:w-[44%] lg:max-w-[640px] lg:shrink-0">
            <img src="{{ asset('images/material/kardus-besar.jpg') }}" alt=""
                 class="absolute inset-0 -z-20 size-full object-cover">
            <div class="absolute inset-0 -z-10 bg-linear-to-b from-secondary/60 to-secondary/90" aria-hidden="true"></div>

            <div class="flex h-56 flex-col justify-between px-5 pt-6 pb-14 sm:px-8 lg:h-full lg:min-h-screen lg:p-14">
                <a href="{{ url('/') }}" class="flex w-fit items-center gap-3 rounded-sm focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-on-secondary">
                    <span class="flex size-10 items-center justify-center rounded-sm bg-primary-container text-on-primary-container" aria-hidden="true">
                        <x-ui.icon name="loop" class="size-6" />
                    </span>
                    <span class="text-headline-md">SymbioNusa</span>
                </a>

                <div class="space-y-5">
                    <p class="max-w-md text-hero-sm lg:text-hero">
                        Sisa produksi satu industri, bahan baku industri lain.
                    </p>
                    <p class="hidden max-w-lg text-body-lg text-on-secondary-muted lg:block">
                        Pertukaran residu industri non-B3 antara Seller dan Buyer, dengan inspeksi mutu dan pengawasan Dinas Lingkungan Hidup.
                    </p>
                    <ul class="hidden flex-wrap gap-2 lg:flex" aria-label="Contoh material">
                        @foreach ($chips as [$file, $label])
                            <li class="flex h-10 items-center gap-2 rounded-full bg-on-secondary/15 py-1 pr-3.5 pl-1 text-label-md font-semibold">
                                <img src="{{ asset('images/material/'.$file) }}" alt="" class="size-8 rounded-full object-cover">
                                {{ $label }}
                            </li>
                        @endforeach
                    </ul>
                    <p class="hidden text-label-sm text-on-secondary-muted lg:block">
                        Foto: Romy2702, SuSanA Secretariat, Elinkorn, Noslenlou, Pax Ahimsa Gethen · CC BY/BY-SA · Wikimedia Commons
                    </p>
                </div>
            </div>
        </aside>

        {{-- Form --}}
        <main class="relative -mt-8 flex flex-1 items-start justify-center px-3 pb-10 sm:px-6 lg:mt-0 lg:items-center lg:px-12 lg:py-12">
            <div class="w-full max-w-[480px] rounded-lg bg-surface p-6 shadow-card sm:p-10">
                @if (session('success') && ! request()->routeIs('password.request', 'password.email'))
                    <x-ui.alert variant="success" class="mb-6">{{ session('success') }}</x-ui.alert>
                @endif
                @if (session('error'))
                    <x-ui.alert variant="danger" class="mb-6">{{ session('error') }}</x-ui.alert>
                @endif

                @yield('content')
            </div>
        </main>
    </div>

    @livewireScripts
</body>
</html>

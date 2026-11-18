<!DOCTYPE html>
{{--
    App shell SymbioNusa (docs/design/DESIGN.md › Layout).
    Sidebar kiri 240 px hijau tua + topbar 64 px; konten di atas latar zinc dengan margin 32 px.
    Di bawah 1024 px sidebar menjadi drawer yang dibuka dari tombol menu di topbar.

    Halaman memakai:
      @extends('layouts.app')
      @section('title', 'Judul Halaman')            -> judul tab browser
      @section('breadcrumb', 'Master Data / Kategori Material')  -> opsional, tampil di topbar
      @section('content') ... @endsection
    Halaman tidak perlu menambah padding/max-width sendiri; shell sudah mengaturnya.
--}}
<html lang="id" class="h-full bg-background">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') · {{ config('app.name', 'SymbioNusa') }}</title>

    @fonts
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    @livewireStyles
</head>
<body class="h-full bg-background font-sans text-body-md text-on-surface antialiased">
    <a href="#main-content"
       class="sr-only focus:not-sr-only focus:fixed focus:top-2 focus:left-2 focus:z-50 focus:rounded-sm focus:bg-primary focus:px-4 focus:py-2 focus:text-on-primary">
        Lewati ke konten utama
    </a>

    @php
        $user = auth()->user();
        $initials = collect(preg_split('/\s+/', trim($user->nama ?? '')))
            ->filter()->take(2)->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->implode('') ?: '?';
    @endphp

    <div x-data="{ navOpen: false }" @keydown.escape.window="navOpen = false" class="min-h-full">
        {{-- Sidebar desktop (>= 1024 px) --}}
        <aside class="fixed inset-y-0 left-0 z-30 hidden w-60 lg:block">
            <x-app.sidebar />
        </aside>

        {{-- Drawer sidebar (< 1024 px) --}}
        <div x-show="navOpen" x-cloak class="fixed inset-0 z-40 lg:hidden" role="dialog" aria-modal="true" aria-label="Navigasi">
            <div class="absolute inset-0 bg-on-surface/50" @click="navOpen = false"></div>
            <aside class="absolute inset-y-0 left-0 w-60 max-w-[85vw]">
                <x-app.sidebar />
            </aside>
            <button type="button" @click="navOpen = false"
                    class="absolute top-3 left-[min(15.5rem,calc(85vw+0.5rem))] flex size-10 items-center justify-center rounded-sm bg-surface text-on-surface focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus-ring"
                    aria-label="Tutup navigasi">
                <x-ui.icon name="x" />
            </button>
        </div>

        <div class="flex min-h-full flex-col lg:pl-60">
            {{-- Topbar --}}
            <header class="sticky top-0 z-20 flex h-16 items-center gap-3 border-b border-outline bg-surface px-4 sm:px-6 lg:px-8">
                <button type="button" @click="navOpen = true"
                        class="-ml-2 flex size-10 items-center justify-center rounded-sm text-on-surface hover:bg-background focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus-ring lg:hidden"
                        aria-label="Buka navigasi" :aria-expanded="navOpen.toString()">
                    <x-ui.icon name="menu" />
                </button>

                <nav class="min-w-0 flex-1 truncate text-body-md text-on-surface-muted" aria-label="Breadcrumb">
                    @hasSection('breadcrumb')
                        @yield('breadcrumb')
                    @else
                        <span class="text-on-surface">@yield('title', 'Dashboard')</span>
                    @endif
                </nav>

                <div class="flex shrink-0 items-center gap-3">
                    <span class="hidden items-center gap-2 text-body-md text-on-surface-muted sm:inline-flex" title="Notifikasi belum tersedia">
                        <x-ui.icon name="bell" />
                        Notifikasi
                    </span>
                    <span class="flex size-9 items-center justify-center rounded-full bg-primary-container text-label-sm text-on-primary-container"
                          title="{{ $user->nama }}" role="img" aria-label="{{ $user->nama }}">{{ $initials }}</span>
                </div>
            </header>

            {{-- Konten --}}
            <main id="main-content" tabindex="-1" class="flex-1 px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
                @if (session('success') || session('error') || $errors->any())
                    <div class="mb-6 space-y-3">
                        @if (session('success'))
                            <x-ui.alert variant="success">{{ session('success') }}</x-ui.alert>
                        @endif
                        @if (session('error'))
                            <x-ui.alert variant="danger">{{ session('error') }}</x-ui.alert>
                        @endif
                        @if ($errors->any())
                            <x-ui.alert variant="warning" title="Periksa kembali isian berikut:">
                                <ul class="mt-1 list-inside list-disc space-y-0.5">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </x-ui.alert>
                        @endif
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    @livewireScripts
</body>
</html>

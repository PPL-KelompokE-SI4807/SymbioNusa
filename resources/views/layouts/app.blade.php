<!DOCTYPE html>
{{--
    App shell SymbioNusa (docs/design/DESIGN.md › Layout, amandemen FND v8).
    Desktop (>= 1024 px): sidebar hijau hutan mengambang 276 px di kiri, tanpa top bar; tiap halaman memulai
    dengan x-ui.page-header (judul konteks + subjudul, tanda "Data demo", notifikasi, satu aksi utama).
    Mobile (< 1024 px): top bar 64 px (menu, logo, notifikasi) dan sidebar sebagai drawer 304 px.

    Halaman memakai:
      @extends('layouts.app')
      @section('title', 'Judul Halaman')            -> judul tab browser
      @section('breadcrumb', 'Master Data / Kategori Material')  -> opsional, baris kecil di atas konten
      @section('content') ... @endsection
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

    <div x-data="{ navOpen: false }" @keydown.escape.window="navOpen = false" class="min-h-full">
        {{-- Sidebar desktop (>= 1024 px), mengambang 16 px dari tepi --}}
        <aside class="fixed inset-y-4 left-4 z-30 hidden w-[276px] shadow-card lg:block">
            <x-app.sidebar />
        </aside>

        {{-- Drawer sidebar (< 1024 px) --}}
        <div x-show="navOpen" x-cloak class="fixed inset-0 z-40 lg:hidden" role="dialog" aria-modal="true" aria-label="Navigasi"
             x-trap.inert.noscroll="navOpen">
            <div class="absolute inset-0 bg-on-surface/55" @click="navOpen = false"></div>
            <aside class="absolute inset-y-3 left-3 w-[304px] max-w-[calc(100vw-5rem)]">
                <x-app.sidebar />
            </aside>
            <button type="button" @click="navOpen = false" x-ref="closeNav"
                    class="absolute top-6 right-4 flex size-11 items-center justify-center rounded-sm bg-surface text-on-surface focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-on-primary"
                    aria-label="Tutup navigasi">
                <x-ui.icon name="x" />
            </button>
        </div>

        <div class="flex min-h-full flex-col lg:pl-[316px]">
            {{-- Top bar mobile --}}
            <header class="sticky top-0 z-20 flex h-16 items-center gap-3 bg-background px-4 sm:px-6 lg:hidden">
                <button type="button" @click="navOpen = true; $nextTick(() => $refs.closeNav.focus())"
                        class="flex size-11 items-center justify-center rounded-sm border border-outline bg-surface text-on-surface focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus-ring"
                        aria-label="Buka navigasi" :aria-expanded="navOpen.toString()">
                    <x-ui.icon name="menu" />
                </button>
                <a href="{{ route('dashboard') }}" class="flex min-w-0 flex-1 items-center gap-2 rounded-sm focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus-ring">
                    <span data-brand-mark class="flex size-8 shrink-0 items-center justify-center rounded-sm bg-primary text-on-primary" aria-hidden="true">
                        <x-ui.icon name="loop" class="size-[18px]" />
                    </span>
                    <span class="truncate text-label-md font-semibold">SymbioNusa</span>
                </a>
                <x-app.notification-bell />
            </header>

            {{-- Konten --}}
            <main id="main-content" tabindex="-1" class="flex-1 px-4 pt-2 pb-8 sm:px-6 lg:pt-6 lg:pr-8 lg:pl-2">
                @hasSection('breadcrumb')
                    <nav class="mb-3 truncate text-body-sm text-on-surface-subtle" aria-label="Breadcrumb">@yield('breadcrumb')</nav>
                @endif

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

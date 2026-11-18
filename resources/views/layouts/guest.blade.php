<!DOCTYPE html>
{{--
    Layout halaman tamu (login, lupa kata sandi, reset kata sandi): satu kartu di tengah latar zinc.
    Halaman memakai @extends('layouts.guest'), @section('title', ...), @section('content').
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

        @if (session('success'))
            <x-ui.alert variant="success" class="mb-4">{{ session('success') }}</x-ui.alert>
        @endif
        @if (session('error'))
            <x-ui.alert variant="danger" class="mb-4">{{ session('error') }}</x-ui.alert>
        @endif

        @yield('content')
    </main>
</body>
</html>

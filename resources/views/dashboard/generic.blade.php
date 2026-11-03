@extends('layouts.app')

@section('content')
    <div class="py-12 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-2xl font-bold text-gray-900">Dashboard Pengguna</h2>
        <p class="mt-2 text-gray-600">Halo, {{ $user->nama ?? 'Pengguna' }}. Peran Anda: {{ $user->role ?? '-' }}</p>
    </div>
@endsection

@extends('layouts.app')

@section('content')
    <x-ui.page-header :title="$user->nama ?? 'Pengguna'" subtitle="Dashboard · peran Anda belum punya ringkasan khusus" />

    <x-ui.card>
        <p class="text-body-md text-on-surface-muted">Peran: {{ $user->role ?? '–' }}. Gunakan menu di samping untuk membuka halaman yang tersedia.</p>
    </x-ui.card>
@endsection

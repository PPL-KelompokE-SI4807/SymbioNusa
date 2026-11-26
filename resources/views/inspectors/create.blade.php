@extends('layouts.app')

{{-- SCREENS.md 03 · Registri Inspector — form daftarkan (PBI-01 / SYM-5) --}}

@section('title', 'Daftarkan Inspector')

@section('breadcrumb')
    <span>Master Data</span>
    <span class="mx-2" aria-hidden="true">/</span>
    <a href="{{ route('admin.inspectors.index') }}" class="hover:text-on-surface hover:underline">Registri Inspector</a>
    <span class="mx-2" aria-hidden="true">/</span>
    <span class="text-on-surface" aria-current="page">Daftarkan Inspector</span>
@endsection

@section('content')
    <x-ui.page-header title="Daftarkan Inspector" subtitle="Lengkapi akun, kompetensi, dan sertifikasi Inspector." />

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-[minmax(0,1fr)_20rem] lg:items-start">
        <form method="POST" action="{{ route('admin.inspectors.store') }}" enctype="multipart/form-data" class="min-w-0 space-y-6">
            @csrf

            @include('inspectors.partials.form', ['inspector' => null])

            <div class="flex flex-wrap items-center justify-end gap-3">
                <x-ui.button variant="secondary" href="{{ route('admin.inspectors.index') }}">Batal</x-ui.button>
                <x-ui.button type="submit" :disabled="$candidates->isEmpty()">Simpan Inspector</x-ui.button>
            </div>
        </form>

        <aside class="lg:sticky lg:top-6" aria-label="Aturan dan info registri">

            @include('inspectors.partials.rules-aside')
        </aside>
    </div>
@endsection

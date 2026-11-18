@extends('layouts.app')

@section('title', 'Tambah Kategori Material')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="hover:text-on-surface hover:underline">Dashboard</a>
    <span class="mx-2" aria-hidden="true">/</span>
    <a href="{{ route('admin.categories.index') }}" class="hover:text-on-surface hover:underline">Kategori Material</a>
    <span class="mx-2" aria-hidden="true">/</span>
    <span class="text-on-surface" aria-current="page">Tambah Baru</span>
@endsection

@section('content')
    <div class="max-w-4xl">
        <x-ui.page-header title="Tambah Kategori Material"
            subtitle="Daftarkan kategori atau subkategori baru beserta toleransi, risiko, dan templat spesifikasinya." />

        <form method="POST" action="{{ route('admin.categories.store') }}" id="category-form" class="space-y-6">
            @csrf

            @include('categories.partials.form', ['category' => null])

            <div class="flex flex-wrap items-center justify-end gap-3">
                <x-ui.button variant="secondary" href="{{ route('admin.categories.index') }}">Batal</x-ui.button>
                <x-ui.button type="submit">Simpan Kategori</x-ui.button>
            </div>
        </form>
    </div>
@endsection

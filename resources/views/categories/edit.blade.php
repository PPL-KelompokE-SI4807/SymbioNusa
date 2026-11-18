@extends('layouts.app')

@section('title', 'Edit Kategori ' . $category->nama)

@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="hover:text-on-surface hover:underline">Dashboard</a>
    <span class="mx-2" aria-hidden="true">/</span>
    <a href="{{ route('admin.categories.index') }}" class="hover:text-on-surface hover:underline">Kategori Material</a>
    <span class="mx-2" aria-hidden="true">/</span>
    <a href="{{ route('admin.categories.show', $category) }}" class="hover:text-on-surface hover:underline">{{ $category->nama }}</a>
    <span class="mx-2" aria-hidden="true">/</span>
    <span class="text-on-surface" aria-current="page">Edit</span>
@endsection

@section('content')
    <div class="max-w-4xl">
        <x-ui.page-header title="Edit Kategori Material" :subtitle="'Perbarui toleransi, risiko, templat spesifikasi, atau status kategori '.$category->nama.'.'">
            <x-slot:actions>
                <x-ui.button variant="secondary" href="{{ route('admin.categories.show', $category) }}">Lihat Detail</x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        <x-ui.alert variant="info" class="mb-6">
            Perubahan toleransi hanya berlaku untuk PO baru. PO yang sedang berjalan tetap memakai nilai saat PO dibuat.
        </x-ui.alert>

        <form method="POST" action="{{ route('admin.categories.update', $category) }}" id="category-form" class="space-y-6">
            @csrf
            @method('PUT')

            @include('categories.partials.form', ['category' => $category])

            <div class="flex flex-wrap items-center justify-end gap-3">
                <x-ui.button variant="secondary" href="{{ route('admin.categories.show', $category) }}">Batal</x-ui.button>
                <x-ui.button type="submit">Simpan Perubahan</x-ui.button>
            </div>
        </form>
    </div>
@endsection

@extends('layouts.app')

@section('title', 'Ubah Inspector')

@section('breadcrumb')
    <span>Master Data</span>
    <span class="mx-2" aria-hidden="true">/</span>
    <a href="{{ route('admin.inspectors.index') }}" class="hover:text-on-surface hover:underline">Registri Inspector</a>
    <span class="mx-2" aria-hidden="true">/</span>
    <a href="{{ route('admin.inspectors.show', $inspector) }}" class="hover:text-on-surface hover:underline">{{ $inspector->user->nama }}</a>
    <span class="mx-2" aria-hidden="true">/</span>
    <span class="text-on-surface" aria-current="page">Ubah</span>
@endsection

@section('content')
    <x-ui.page-header title="Ubah Inspector"
        subtitle="Perbarui bidang keahlian, wilayah tugas, dan identitas sertifikat {{ $inspector->user->nama }}." />

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-[minmax(0,1fr)_20rem] lg:items-start">
        <form method="POST" action="{{ route('admin.inspectors.update', $inspector) }}" enctype="multipart/form-data" class="min-w-0 space-y-6">
            @csrf
            @method('PUT')

            @include('inspectors.partials.form', ['inspector' => $inspector, 'candidates' => collect()])

            <div class="flex flex-wrap items-center justify-end gap-3">
                <x-ui.button variant="secondary" href="{{ route('admin.inspectors.show', $inspector) }}">Batal</x-ui.button>
                <x-ui.button type="submit">Simpan Perubahan</x-ui.button>
            </div>
        </form>

        <aside class="lg:sticky lg:top-6" aria-label="Aturan dan info registri">

            @include('inspectors.partials.rules-aside')
        </aside>
    </div>
@endsection

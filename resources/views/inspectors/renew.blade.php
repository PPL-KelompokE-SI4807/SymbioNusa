@extends('layouts.app')
@use('App\Http\Requests\Inspector\InspectorRules')

@section('title', 'Perpanjang Sertifikat')

@section('breadcrumb')
    <span>Master Data</span>
    <span class="mx-2" aria-hidden="true">/</span>
    <a href="{{ route('admin.inspectors.index') }}" class="hover:text-on-surface hover:underline">Registri Inspector</a>
    <span class="mx-2" aria-hidden="true">/</span>
    <a href="{{ route('admin.inspectors.show', $inspector) }}" class="hover:text-on-surface hover:underline">{{ $inspector->user->nama }}</a>
    <span class="mx-2" aria-hidden="true">/</span>
    <span class="text-on-surface" aria-current="page">Perpanjang</span>
@endsection

@php
    $status = $inspector->status($today);
    $maxMb = (int) (InspectorRules::maxUploadKb() / 1024);
@endphp

@section('content')
    <div class="max-w-3xl">
        <x-ui.page-header title="Perpanjang Sertifikat"
            subtitle="Tetapkan masa berlaku baru untuk {{ $inspector->user->nama }}. Inspector langsung aktif kembali setelah disimpan." />

        <x-ui.card title="Sertifikat saat ini" class="mb-6">
            <x-slot:actions>
                <x-ui.badge :variant="$status->badge()">{{ $status->label() }}</x-ui.badge>
            </x-slot:actions>
            @include('inspectors.partials.certificate-summary', ['profile' => $inspector, 'status' => $status, 'today' => $today])
        </x-ui.card>

        <form method="POST" action="{{ route('admin.inspectors.renew', $inspector) }}" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <x-ui.card title="Sertifikat baru">
                <div class="space-y-6">
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <x-ui.field label="Nomor sertifikat" for="no_sertifikat" name="no_sertifikat"
                            helper="Ubah bila sertifikat baru bernomor lain." required>
                            <x-ui.input id="no_sertifikat" name="no_sertifikat" class="tabular-nums" required maxlength="100"
                                :value="old('no_sertifikat', $inspector->no_sertifikat)" aria-describedby="no_sertifikat-helper" />
                        </x-ui.field>

                        <x-ui.field label="Berlaku sampai" for="berlaku_sampai" name="berlaku_sampai"
                            helper="Harus setelah hari ini." required>
                            <x-ui.input id="berlaku_sampai" name="berlaku_sampai" type="date" class="tabular-nums" required
                                min="{{ $today->copy()->addDay()->toDateString() }}" :value="old('berlaku_sampai')"
                                aria-describedby="berlaku_sampai-helper" />
                        </x-ui.field>
                    </div>

                    @include('inspectors.partials.file-field', [
                        'label' => 'Berkas sertifikat baru',
                        'required' => false,
                        'helper' => 'Opsional bila berkas sudah diunggah Inspector. PDF, JPG, atau PNG, maks. '.$maxMb.' MB.',
                    ])
                </div>
            </x-ui.card>

            <div class="flex flex-wrap items-center justify-end gap-3">
                <x-ui.button variant="secondary" href="{{ route('admin.inspectors.show', $inspector) }}">Batal</x-ui.button>
                <x-ui.button type="submit">Simpan & Aktifkan</x-ui.button>
            </div>
        </form>
    </div>
@endsection

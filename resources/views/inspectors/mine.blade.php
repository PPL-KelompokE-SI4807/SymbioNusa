@extends('layouts.app')
@use('App\Enums\InspectorStatus')
@use('App\Http\Requests\Inspector\InspectorRules')
@use('App\Support\Format')

{{-- UC-33 · Profil Kompetensi milik Inspector (PBI-01 / SYM-5) --}}

@section('title', 'Profil Kompetensi')

@section('content')
    <div class="max-w-4xl">
        <x-ui.page-header title="Profil Kompetensi"
            subtitle="Bidang keahlian, wilayah tugas, dan sertifikasi Anda di registri Inspector." />

        @if ($profile === null)
            <x-ui.card>
                <div class="py-6 text-center">
                    <p class="text-label-md text-on-surface">Anda belum terdaftar di registri Inspector.</p>
                    <p class="mt-1 text-body-sm text-on-surface-muted">
                        Hubungi Admin Platform untuk didaftarkan beserta bidang keahlian dan sertifikasi Anda.
                    </p>
                </div>
            </x-ui.card>
        @else
            @php
                $status = $profile->status($today);
                $maxMb = (int) (InspectorRules::maxUploadKb() / 1024);
            @endphp

            <div class="mb-6 flex flex-wrap items-center gap-3">
                <x-ui.badge :variant="$status->badge()">{{ $status->label() }}</x-ui.badge>
                <span class="text-body-md text-on-surface-muted">{{ $profile->wilayah_tugas ?? 'Wilayah belum diatur' }}</span>
            </div>

            @if ($status === InspectorStatus::NONAKTIF)
                <x-ui.alert variant="info" title="Anda sedang nonaktif" class="mb-6">
                    Anda tidak menerima penugasan inspeksi baru. Unggah sertifikat terbaru di bawah, lalu Admin akan memperbarui
                    masa berlakunya.
                </x-ui.alert>
            @elseif ($status === InspectorStatus::SEGERA_BERAKHIR)
                <x-ui.alert variant="warning" title="Sertifikat segera berakhir" class="mb-6">
                    Setelah {{ Format::date($profile->berlaku_sampai) }} Anda otomatis nonaktif. Unggah sertifikat perpanjangan
                    sebelum tanggal itu.
                </x-ui.alert>
            @endif

            <div class="space-y-6">
                <x-ui.card title="Sertifikasi kompetensi">
                    @include('inspectors.partials.certificate-summary', ['profile' => $profile, 'status' => $status, 'today' => $today])
                </x-ui.card>

                <x-ui.card title="Bidang keahlian">
                    @include('inspectors.partials.expertise-chips', ['expertise' => $expertise, 'limit' => null])
                    <p class="mt-3 text-body-sm text-on-surface-muted">Perubahan bidang keahlian dan wilayah tugas dilakukan oleh Admin.</p>
                </x-ui.card>

                <x-ui.card title="Unggah sertifikat perpanjangan">
                    <p class="-mt-2 mb-4 text-body-sm text-on-surface-muted">
                        Berkas baru menggantikan berkas lama. Masa berlaku dan status diperbarui Admin setelah meninjau berkas.
                    </p>
                    <form method="POST" action="{{ route('inspector.profile.certificate') }}" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        @include('inspectors.partials.file-field', [
                            'label' => 'Berkas sertifikat',
                            'required' => true,
                            'helper' => 'PDF, JPG, atau PNG, maks. '.$maxMb.' MB.',
                        ])
                        <div class="flex justify-end">
                            <x-ui.button type="submit">Kirim Berkas</x-ui.button>
                        </div>
                    </form>
                </x-ui.card>
            </div>
        @endif
    </div>
@endsection

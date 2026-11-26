@extends('layouts.app')
@use('App\Enums\InspectorStatus')
@use('App\Support\Format')

@section('title', 'Inspector '.$inspector->user->nama)

@section('breadcrumb')
    <span>Master Data</span>
    <span class="mx-2" aria-hidden="true">/</span>
    <a href="{{ route('admin.inspectors.index') }}" class="hover:text-on-surface hover:underline">Registri Inspector</a>
    <span class="mx-2" aria-hidden="true">/</span>
    <span class="text-on-surface" aria-current="page">{{ $inspector->user->nama }}</span>
@endsection

@php
    $status = $inspector->status($today);
    $actionLabels = [
        'INSPECTOR_DIDAFTARKAN' => 'Didaftarkan ke registri',
        'INSPECTOR_DIUBAH' => 'Data kompetensi diubah',
        'INSPECTOR_DIPERPANJANG' => 'Sertifikat diperpanjang',
        'INSPECTOR_DIKELUARKAN' => 'Dikeluarkan dari registri',
        'INSPECTOR_KEDALUWARSA' => 'Nonaktif otomatis (sertifikat kedaluwarsa)',
        'INSPECTOR_UNGGAH_SERTIFIKAT' => 'Inspector mengunggah berkas sertifikat baru',
    ];
@endphp

@section('content')
    <div class="max-w-5xl">
        <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
            <div class="min-w-0">
                <div class="mb-2">
                    <x-ui.badge :variant="$status->badge()">{{ $status->label() }}</x-ui.badge>
                </div>
                <h1 class="page-title text-on-surface">{{ $inspector->user->nama }}</h1>
                <p class="mt-1 text-body-md text-on-surface-muted">
                    {{ $inspector->user->email }}
                    <span aria-hidden="true">·</span>
                    {{ $inspector->wilayah_tugas ?? 'Wilayah belum diatur' }}
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <x-ui.button variant="secondary" href="{{ route('admin.inspectors.index') }}">Kembali</x-ui.button>
                @if ($status !== InspectorStatus::NONAKTIF)
                    <x-ui.button variant="secondary" href="{{ route('admin.inspectors.edit', $inspector) }}">Ubah</x-ui.button>
                @endif
                <x-ui.button href="{{ route('admin.inspectors.renew.form', $inspector) }}">Perpanjang Sertifikat</x-ui.button>
            </div>
        </div>

        @if ($status === InspectorStatus::NONAKTIF)
            <x-ui.alert variant="info" title="Inspector nonaktif" class="mb-6">
                Inspector ini tidak muncul pada penugasan inspeksi. Perpanjang sertifikat untuk mengaktifkannya kembali.
            </x-ui.alert>
        @elseif ($status === InspectorStatus::SEGERA_BERAKHIR)
            <x-ui.alert variant="warning" title="Sertifikat segera berakhir" class="mb-6">
                Setelah {{ Format::date($inspector->berlaku_sampai) }}, Inspector otomatis nonaktif dan tidak bisa ditugaskan.
            </x-ui.alert>
        @endif

        <div class="space-y-6">
            <x-ui.card title="Sertifikasi kompetensi">
                @include('inspectors.partials.certificate-summary', ['profile' => $inspector, 'status' => $status, 'today' => $today])
            </x-ui.card>

            <x-ui.card title="Bidang keahlian">
                @include('inspectors.partials.expertise-chips', ['expertise' => $expertise, 'limit' => null])
            </x-ui.card>

            <x-ui.card title="Riwayat perubahan">
                @if ($history->isEmpty())
                    <p class="text-body-sm text-on-surface-muted">Belum ada riwayat tercatat.</p>
                @else
                    <ol class="divide-y divide-outline-hairline">
                        @foreach ($history as $log)
                            @php
                                $data = json_decode($log->nilai_baru ?? 'null', true);
                                // audit_logs.waktu disimpan dalam app.timezone (UTC); tampilkan WIB.
                                $waktu = \Illuminate\Support\Carbon::parse($log->waktu, config('app.timezone'))->setTimezone(\App\Models\InspectorProfile::TIMEZONE);
                            @endphp
                            <li class="flex flex-wrap items-start justify-between gap-x-4 gap-y-1 py-3">
                                <div class="min-w-0">
                                    <p class="text-body-md text-on-surface">{{ $actionLabels[$log->aksi] ?? $log->aksi }}</p>
                                    @if (is_array($data) && filled($data['alasan'] ?? null))
                                        <p class="text-body-sm text-on-surface-muted">Alasan: {{ $data['alasan'] }}</p>
                                    @endif
                                    <p class="text-body-sm text-on-surface-muted">{{ $log->pelaku ?? 'Sistem' }}</p>
                                </div>
                                <time datetime="{{ $waktu->toIso8601String() }}" class="text-body-sm tabular-nums text-on-surface-muted">
                                    {{ Format::date($waktu) }}, {{ $waktu->format('H:i') }} WIB
                                </time>
                            </li>
                        @endforeach
                    </ol>
                @endif
            </x-ui.card>

            @can('deactivate', $inspector)
                <x-ui.card title="Keluarkan dari registri" id="keluarkan" class="scroll-mt-6">
                    <p class="-mt-2 mb-4 text-body-sm text-on-surface-muted">
                        Inspector berhenti muncul pada penugasan inspeksi. Profil dan riwayatnya tetap tersimpan, dan bisa
                        diaktifkan kembali lewat Perpanjang Sertifikat.
                    </p>
                    <form method="POST" action="{{ route('admin.inspectors.deactivate', $inspector) }}" class="space-y-4"
                        onsubmit="return confirm(@js('Keluarkan '.$inspector->user->nama.' dari registri Inspector?'))">
                        @csrf
                        <x-ui.field label="Alasan" for="alasan" name="alasan" helper="Dicatat di riwayat. 10–500 karakter." required>
                            <x-ui.textarea id="alasan" name="alasan" rows="3" required minlength="10" maxlength="500"
                                aria-describedby="alasan-helper" placeholder="Mis. Kontrak kerja sama berakhir.">{{ old('alasan') }}</x-ui.textarea>
                        </x-ui.field>
                        <div class="flex justify-end">
                            <x-ui.button type="submit" variant="danger">Keluarkan Inspector</x-ui.button>
                        </div>
                    </form>
                </x-ui.card>
            @endcan
        </div>
    </div>
@endsection

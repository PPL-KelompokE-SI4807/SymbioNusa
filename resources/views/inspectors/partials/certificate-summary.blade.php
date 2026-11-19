{{--
    Ringkasan sertifikat: nomor, masa berlaku, berkas. $profile: InspectorProfile; $status; $today.
--}}
@use('App\Support\Format')

@php
    $days = $profile->daysUntilExpiry($today);
@endphp

<dl class="grid grid-cols-1 gap-4 sm:grid-cols-3">
    <div>
        <dt class="text-label-md text-on-surface-muted">Nomor sertifikat</dt>
        <dd class="mt-1 text-body-md tabular-nums text-on-surface">{{ $profile->no_sertifikat ?? '–' }}</dd>
    </div>
    <div>
        <dt class="text-label-md text-on-surface-muted">Berlaku sampai</dt>
        <dd class="mt-1 text-body-md tabular-nums text-on-surface">
            {{ Format::date($profile->berlaku_sampai) }}
            @include('inspectors.partials.expiry-note', ['days' => $days, 'status' => $status, 'class' => 'block text-body-sm'])
        </dd>
    </div>
    <div>
        <dt class="text-label-md text-on-surface-muted">Berkas</dt>
        <dd class="mt-1 text-body-md">
            @if ($profile->sertifikat_url)
                <a href="{{ route('inspectors.certificate', $profile) }}"
                    class="inline-flex min-h-10 items-center text-primary underline-offset-2 hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus-ring">
                    Unduh sertifikat
                </a>
            @else
                <span class="text-on-surface-muted">Belum diunggah</span>
            @endif
        </dd>
    </div>
</dl>

{{--
    Keterangan sisa masa berlaku. $days: sisa hari (negatif = lewat) atau null; $status: InspectorStatus.
--}}
@use('App\Enums\InspectorStatus')

@if ($days !== null)
    @if ($days < 0)
        <span class="{{ $class ?? '' }} text-error">Lewat {{ abs($days) }} hari</span>
    @elseif ($days === 0)
        <span class="{{ $class ?? '' }} text-warning">Berakhir hari ini</span>
    @elseif ($status === InspectorStatus::SEGERA_BERAKHIR)
        <span class="{{ $class ?? '' }} text-warning">{{ $days }} hari lagi</span>
    @elseif ($status === InspectorStatus::NONAKTIF)
        <span class="{{ $class ?? '' }} text-on-surface-muted">Dikeluarkan dari registri</span>
    @endif
@endif

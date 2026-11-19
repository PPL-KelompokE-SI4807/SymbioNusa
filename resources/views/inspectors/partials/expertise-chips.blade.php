{{--
    Chip bidang keahlian. $expertise: koleksi Category; $limit: jumlah chip sebelum diringkas "+n".
--}}
@php
    $limit = $limit ?? null;
    $shown = $limit ? $expertise->take($limit) : $expertise;
    $rest = $expertise->count() - $shown->count();
@endphp

@if ($expertise->isEmpty())
    <span class="text-body-sm text-on-surface-muted">Belum diatur</span>
@else
    <ul class="flex flex-wrap gap-1.5" aria-label="Bidang keahlian">
        @foreach ($shown as $cat)
            <li>
                <x-ui.badge :variant="$cat->status === 'AKTIF' ? 'success' : 'neutral'"
                    :title="$cat->status === 'AKTIF' ? null : 'Kategori sudah diarsipkan'">
                    {{ $cat->nama }}
                </x-ui.badge>
            </li>
        @endforeach
        @if ($rest > 0)
            <li>
                <x-ui.badge title="{{ $expertise->slice($shown->count())->pluck('nama')->join(', ') }}">+{{ $rest }} lainnya</x-ui.badge>
            </li>
        @endif
    </ul>
@endif

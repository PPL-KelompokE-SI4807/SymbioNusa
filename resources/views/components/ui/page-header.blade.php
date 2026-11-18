{{--
    Kepala halaman (DESIGN.md › Layout): satu judul 26 px, subjudul satu baris, aksi utama di kanan.
    Contoh:
    <x-ui.page-header title="Registri Inspector" subtitle="Kelola Inspector dan sertifikasinya.">
        <x-slot:actions><x-ui.button>+ Daftarkan Inspector</x-ui.button></x-slot:actions>
    </x-ui.page-header>
--}}
@props(['title', 'subtitle' => null])

<div {{ $attributes->class(['mb-6 flex flex-wrap items-start justify-between gap-4']) }}>
    <div class="min-w-0">
        <h1 class="text-headline-lg text-on-surface">{{ $title }}</h1>
        @if ($subtitle)
            <p class="mt-1 text-body-md text-on-surface-muted">{{ $subtitle }}</p>
        @endif
    </div>
    @isset($actions)
        <div class="flex flex-wrap items-center gap-2">{{ $actions }}</div>
    @endisset
</div>

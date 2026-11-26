{{--
    Kepala halaman (DESIGN.md › Layout; panduan Senja §8.2). Judul .page-title clamp(25–34 px) / 900 menamai konteks (organisasi, orang, atau
    nama halaman), subjudul satu baris di bawahnya. Di kanan: tanda "Data demo" (selain produksi), indikator
    notifikasi (desktop; di mobile ada di top bar), lalu slot actions (paling banyak satu aksi utama).
    Contoh:
    <x-ui.page-header title="Registri Inspector" subtitle="Kelola Inspector dan sertifikasinya.">
        <x-slot:actions><x-ui.button>Daftarkan Inspector</x-ui.button></x-slot:actions>
    </x-ui.page-header>
--}}
@props(['title', 'subtitle' => null, 'utilities' => true])

<div {{ $attributes->class(['mb-6 flex flex-wrap items-center justify-between gap-4']) }}>
    <div class="min-w-0">
        <h1 class="page-title text-on-surface">{{ $title }}</h1>
        @if ($subtitle)
            <p class="mt-1 text-body-md text-on-surface-muted">{{ $subtitle }}</p>
        @endif
    </div>
    <div class="flex flex-wrap items-center gap-3">
        @if ($utilities && ! app()->environment('production'))
            <span class="inline-flex h-8 items-center rounded-full border border-outline bg-surface px-3 text-body-sm font-medium text-on-surface-muted">Data demo</span>
        @endif
        @if ($utilities)
            <div class="hidden lg:block"><x-app.notification-bell /></div>
        @endif
        @isset($actions)
            {{ $actions }}
        @endisset
    </div>
</div>

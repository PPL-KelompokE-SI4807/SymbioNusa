{{--
    Tabel data (DESIGN.md › Data table; 02_ATURAN baris 2): header 13 px redup, baris minimal 56 px dengan garis rambut,
    angka rata kanan (beri kelas text-right tabular-nums pada sel angka), kolom status memakai x-ui.badge.
    Di layar sempit tabel digeser di dalam kartu dengan kolom pertama sticky, sehingga identitas baris tetap terlihat.
    Untuk daftar utama di bawah 1024 px, sediakan juga tampilan baris ledger (lihat categories/index).
    label: nama wilayah tabel untuk pembaca layar.
    Di dalam x-app.kaca (data-table): baris di permukaan kaca, kolom pertama sticky hanya buram di bawah 1024 px.
--}}
@props(['label'])

<div {{ $attributes->class(['overflow-x-auto focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus-ring']) }}
     data-table tabindex="0" role="region" aria-label="{{ $label }}">
    <table class="w-full text-left text-data-tabular [&_tr>*:first-child]:sticky [&_tr>*:first-child]:left-0 [&_tr>*:first-child]:z-[1] [&_tr>*:first-child]:bg-surface">
        <thead>
            <tr class="h-10 text-body-sm text-on-surface-subtle [&>th]:font-medium [&>th]:whitespace-nowrap">
                {{ $head }}
            </tr>
        </thead>
        <tbody>
            {{ $slot }}
        </tbody>
    </table>
</div>

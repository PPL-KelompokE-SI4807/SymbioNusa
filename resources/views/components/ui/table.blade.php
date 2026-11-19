{{--
    Tabel data (DESIGN.md › Data table, amandemen FND v8): header 13 px redup, baris minimal 56 px dengan garis tipis,
    angka rata kanan (beri kelas text-right tabular-nums pada sel angka), kolom status memakai x-ui.badge,
    bisa digeser di layar sempit.
    label: nama wilayah tabel untuk pembaca layar.
--}}
@props(['label'])

<div {{ $attributes->class(['overflow-x-auto focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus-ring']) }}
     tabindex="0" role="region" aria-label="{{ $label }}">
    <table class="w-full text-left text-data-tabular">
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

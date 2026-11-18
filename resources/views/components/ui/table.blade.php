{{--
    Tabel data (DESIGN.md › Data table): header 12 px redup, baris minimal 52 px dengan garis tipis,
    angka rata kanan (beri kelas text-right tabular-nums pada sel angka), bisa digeser di layar sempit.
    label: nama wilayah tabel untuk pembaca layar.
    Contoh:
    <x-ui.table label="Order terbaru">
        <x-slot:head><th class="px-4">PO</th><th class="px-4 text-right">Total</th></x-slot:head>
        <tr class="border-t border-outline"><td class="px-4 py-3">PO-21</td><td class="px-4 py-3 text-right tabular-nums">Rp 3.000.000</td></tr>
    </x-ui.table>
--}}
@props(['label'])

<div {{ $attributes->class(['overflow-x-auto focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus-ring']) }}
     tabindex="0" role="region" aria-label="{{ $label }}">
    <table class="w-full text-left text-data-tabular">
        <thead>
            <tr class="h-11 text-label-sm text-on-surface-muted [&>th]:font-medium [&>th]:whitespace-nowrap">
                {{ $head }}
            </tr>
        </thead>
        <tbody>
            {{ $slot }}
        </tbody>
    </table>
</div>

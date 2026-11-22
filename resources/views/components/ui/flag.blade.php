{{--
    Tanda mendesak (DESIGN.md › Components › Urgency, amandemen FND v8): kotak merah 16 px dengan "!" putih.
    Hanya untuk item mendesak; item normal tidak diberi label prioritas. Teks pembaca layar: "Mendesak".
    Contoh: <x-ui.flag /> <span class="text-error">Besok</span>
--}}
<span {{ $attributes->class(['inline-flex size-4 shrink-0 items-center justify-center rounded bg-error text-on-primary']) }}
      role="img" aria-label="Mendesak">
    <svg class="size-3" viewBox="0 0 12 12" aria-hidden="true" focusable="false">
        <path d="M6 2.5 L6 7" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" />
        <circle cx="6" cy="9.4" r="0.95" fill="currentColor" />
    </svg>
</span>

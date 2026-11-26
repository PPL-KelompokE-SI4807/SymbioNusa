{{--
    Paginasi SymbioNusa (FND v11 Senja di Sawah), menggantikan pagination::tailwind bawaan Laravel.
    "Menampilkan x–y dari n" (DESIGN.md › Data honesty: daftar parsial menyebut totalnya), tombol bersudut 9 px,
    target 40 px (44 px di layar sempit), halaman aktif ditandai aria-current, tanpa aria-label pada <span>.
--}}
@if ($paginator->hasPages())
    @php
        $box = 'inline-flex min-h-11 min-w-11 items-center justify-center rounded-sm px-3 text-label-md font-semibold tabular-nums sm:min-h-10 sm:min-w-10';
        $link = $box.' border border-outline-control bg-surface text-on-surface transition-colors hover:bg-surface-muted focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus-ring';
        $idle = $box.' text-on-surface-muted opacity-50';
    @endphp
    <nav role="navigation" aria-label="Navigasi halaman" class="flex flex-wrap items-center justify-between gap-3">
        <p class="text-body-sm text-on-surface-muted">
            Menampilkan <span class="font-semibold tabular-nums text-on-surface">{{ $paginator->firstItem() }}–{{ $paginator->lastItem() }}</span>
            dari <span class="font-semibold tabular-nums text-on-surface">{{ $paginator->total() }}</span>
        </p>

        <ul class="flex flex-wrap items-center gap-1.5">
            <li>
                @if ($paginator->onFirstPage())
                    <span class="{{ $idle }}" aria-hidden="true"><x-ui.icon name="arrow-left" class="size-4" /></span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="{{ $link }}" aria-label="Halaman sebelumnya">
                        <x-ui.icon name="arrow-left" class="size-4" />
                    </a>
                @endif
            </li>

            @foreach ($elements as $element)
                @if (is_string($element))
                    <li class="hidden sm:block"><span class="{{ $idle }} opacity-100">{{ $element }}</span></li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        <li class="hidden sm:block">
                            @if ($page == $paginator->currentPage())
                                <span aria-current="page" class="{{ $box }} bg-primary text-on-primary">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="{{ $link }}" aria-label="Halaman {{ $page }}">{{ $page }}</a>
                            @endif
                        </li>
                    @endforeach
                @endif
            @endforeach

            <li>
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="{{ $link }}" aria-label="Halaman berikutnya">
                        <x-ui.icon name="arrow-right" class="size-4" />
                    </a>
                @else
                    <span class="{{ $idle }}" aria-hidden="true"><x-ui.icon name="arrow-right" class="size-4" /></span>
                @endif
            </li>
        </ul>
    </nav>
@endif

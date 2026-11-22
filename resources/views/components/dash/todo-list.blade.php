{{--
    Daftar "Perlu tindakan" (DESIGN.md › Components › Lists, amandemen FND v8).
    items: list<array{title, meta, party, date, date_caption, urgent, action, url, person?}>
    Hanya aksi teratas yang punya tautan memakai tombol primary; sisanya secondary. Aksi tanpa halaman tidak diberi tombol.
--}}
@props(['title', 'items' => [], 'empty' => 'Tidak ada yang menunggu tindakan Anda.'])

<x-ui.card :padding="false" {{ $attributes }}>
    <div class="flex items-center justify-between gap-3 px-6 pt-5 pb-2">
        <h2 class="text-headline-md">{{ $title }}</h2>
        <p class="text-body-sm text-on-surface-subtle">{{ count($items) }} menunggu</p>
    </div>

    @if ($items === [])
        <p class="px-6 pt-2 pb-6 text-body-md text-on-surface-muted">{{ $empty }}</p>
    @else
        @php($primaryUsed = false)
        <ul class="px-3 pb-3">
            @foreach ($items as $item)
                <li class="flex flex-wrap items-center gap-x-4 gap-y-2 rounded-md p-3 hover:bg-surface-muted sm:flex-nowrap">
                    <x-ui.monogram :name="$item['party']" :person="$item['person'] ?? false" />
                    <div class="min-w-0 flex-1 basis-[calc(100%-3.5rem)] sm:basis-0">
                        <p class="text-label-md font-semibold text-on-surface">{{ $item['title'] }}</p>
                        <p class="mt-0.5 text-body-sm text-on-surface-subtle">{{ $item['meta'] }}</p>
                    </div>
                    <div class="shrink-0 pl-14 sm:w-28 sm:pl-0">
                        <p @class(['flex items-center gap-1.5 text-label-md', 'font-semibold text-error' => $item['urgent'], 'text-on-surface' => ! $item['urgent']])>
                            @if ($item['urgent'])
                                <x-ui.flag />
                            @endif
                            {{ $item['date'] }}
                        </p>
                        <p class="text-body-sm text-on-surface-subtle">{{ $item['date_caption'] }}</p>
                    </div>
                    <div class="ml-auto flex shrink-0 justify-end sm:w-28">
                        @if ($item['url'])
                            <x-ui.button :href="$item['url']" :variant="$primaryUsed ? 'secondary' : 'primary'" class="h-9!">{{ $item['action'] }}</x-ui.button>
                            @php($primaryUsed = true)
                        @else
                            <span class="text-body-sm text-on-surface-subtle" title="Halaman untuk tindakan ini segera hadir">{{ $item['action'] }} segera</span>
                        @endif
                    </div>
                </li>
            @endforeach
        </ul>
    @endif
</x-ui.card>

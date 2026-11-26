{{-- Perlu keputusan Anda (Lab › Queue). Baris dirender di server; saringan dan laci memakai state kacaAdmin. --}}
@php
    $filters = [
        'semua' => ['Semua', fn ($i) => true],
        'sengketa' => ['Sengketa', fn ($i) => $i['kind'] === 'sengketa'],
        'verifikasi' => ['Verifikasi', fn ($i) => $i['kind'] === 'verifikasi'],
        'inspeksi' => ['Inspeksi', fn ($i) => in_array($i['kind'], ['penugasan', 'sertifikat'], true)],
    ];
    $tones = ['danger' => 'text-danger', 'warning' => 'text-warning', 'info' => 'text-info', 'primary' => 'text-primary'];
@endphp
<section class="glass flex h-full flex-col p-4 sm:p-5" aria-labelledby="queue-title">
    <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
        <h2 id="queue-title" class="card-title flex items-center gap-2.5">
            Perlu keputusan Anda
            <span class="grid h-6 min-w-6 place-items-center rounded-full bg-accent px-1.5 text-[13px] font-black text-accent-ink">{{ count($kaca['queue']) }}</span>
        </h2>
        <button type="button" @click="fullOpen = true" class="flex h-10 items-center gap-1.5 rounded-[11px] border border-line-strong px-3 text-[13px] font-bold hover:bg-primary-soft">
            Semua antrean <x-ui.icon name="arrow-right" class="size-4" />
        </button>
    </div>

    <div class="kaca-seg mb-3 flex flex-wrap gap-1" style="--r: 11px" x-kaca-seg role="group" aria-label="Saring antrean">
        <span class="kaca-thumb" aria-hidden="true"></span>
        @foreach ($filters as $key => [$label, $test])
            <button type="button" @click="setFilter('{{ $key }}')" :aria-pressed="(filter === '{{ $key }}').toString()"
                    class="flex h-10 shrink-0 items-center gap-1.5 rounded-[11px] px-3 text-[13px] font-bold">
                {{ $label }}
                <span class="kaca-count rounded-[6px] bg-primary-soft px-1.5 text-[12px] text-meta tabular-nums">{{ count(array_filter($kaca['queue'], $test)) }}</span>
            </button>
        @endforeach
    </div>

    <ul class="relative flex-1 space-y-2" x-ref="queueList" aria-label="Antrean keputusan, urut dari risiko tertinggi">
        @foreach ($kaca['queue'] as $i => $item)
            @php($ratio = isset($item['expiresInDays']) ? 1 - $item['expiresInDays'] / 30 : null)
            <li x-show="matches('{{ $item['kind'] }}')" data-kind="{{ $item['kind'] }}">
                <div tabindex="0" @focus="focusKey = '{{ $item['key'] }}'" @mouseenter="focusKey = '{{ $item['key'] }}'" @keydown.enter.self.prevent="review({{ $i }})" @keydown.space.self.prevent="review({{ $i }})"
                     aria-label="{{ $item['group'] }}: {{ $item['title'] }}. {{ $item['risk'] }}. Enter atau spasi untuk meninjau."
                     :class="focusKey === '{{ $item['key'] }}' ? 'border-accent bg-[var(--panel-strong)] shadow-[0_0_0_3px_var(--accent-soft)]' : 'border-line bg-field hover:border-line-strong'"
                     class="glass-row relative grid grid-cols-[44px_minmax(0,1fr)] gap-x-3 gap-y-2.5 rounded-[16px] border p-3 outline-none transition-[border-color,background-color,box-shadow] duration-200 sm:grid-cols-[48px_minmax(0,1fr)_auto]">
                    <img src="{{ $item['photo'] }}" alt="" class="size-11 rounded-[12px] object-cover sm:row-span-2 sm:size-12" loading="lazy">
                    <div class="min-w-0">
                        <div class="mb-0.5 flex flex-wrap items-center gap-x-2.5 gap-y-1">
                            <span class="mono rounded-[6px] border border-line-strong px-1.5 py-px text-[12px] font-semibold text-meta">{{ $item['id'] }}</span>
                            <span class="text-[12px] font-semibold text-meta">{{ $item['group'] }}</span>
                        </div>
                        <p class="text-[15px] leading-snug font-bold sm:truncate">{{ $item['title'] }}</p>
                        <p class="truncate text-[13px] text-meta">{{ $item['meta'] }}</p>
                        @if ($ratio !== null)
                            <span class="mt-1.5 flex items-center gap-2">
                                <span class="relative h-1.5 w-20 shrink-0 overflow-hidden rounded-full bg-track" aria-hidden="true">
                                    <span class="absolute inset-y-0 left-0 rounded-full" style="width: {{ min(1, max(0, $ratio)) * 100 }}%; background: {{ $ratio >= 1 ? 'var(--danger)' : ($ratio >= 0.6 ? 'var(--accent)' : 'var(--c-done)') }}"></span>
                                </span>
                                <span class="text-[12px] font-semibold text-meta">berakhir {{ $item['expiresInDays'] }} hari lagi</span>
                            </span>
                        @endif
                    </div>
                    <div class="col-span-2 flex flex-wrap items-center justify-between gap-2 sm:col-span-1 sm:col-start-3 sm:row-span-2 sm:row-start-1 sm:flex-col sm:items-end sm:justify-between">
                        <span class="flex items-center gap-3">
                            <span class="inline-flex items-center gap-1.5 text-[13px] font-bold {{ $tones[$item['riskTone']] ?? 'text-meta' }}">
                                <svg viewBox="0 0 12 12" class="size-3" aria-hidden="true">
                                    @if ($item['riskTone'] === 'danger')
                                        <circle cx="6" cy="6" r="6" fill="currentColor" /><path d="M4 4l4 4M8 4l-4 4" stroke="#fff" stroke-width="1.5" stroke-linecap="round" />
                                    @else
                                        <circle cx="6" cy="6" r="4.6" fill="none" stroke="currentColor" stroke-width="1.8" stroke-dasharray="2.4 1.8" />
                                    @endif
                                </svg>
                                {{ $item['risk'] }}
                            </span>
                            @if ($item['age'])
                                <span class="flex items-center gap-1 text-[13px] font-semibold text-meta tabular-nums"><x-ui.icon name="clock" class="size-3.5" />{{ $item['age'] }}</span>
                            @endif
                        </span>
                        <span class="flex w-full items-center gap-1.5 sm:w-auto">
                            <button type="button" tabindex="-1" @click="review({{ $i }})" class="h-11 flex-1 rounded-[11px] border border-line-strong px-3 text-[13px] font-bold text-ink hover:bg-primary-soft sm:h-9 sm:flex-none sm:border-transparent">Tinjau</button>
                            @if ($item['url'])
                                <a href="{{ $item['url'] }}" class="flex h-11 flex-1 items-center justify-center gap-2 rounded-[11px] bg-primary px-3.5 text-[13px] font-bold text-primary-ink shadow-[0_6px_14px_-8px_rgba(30,42,8,.8)] sm:h-9 sm:flex-none">{{ $item['primary'] }}</a>
                            @else
                                <span class="flex h-11 flex-1 cursor-not-allowed items-center justify-center rounded-[11px] bg-primary px-3.5 text-[13px] font-bold text-primary-ink opacity-50 sm:h-9 sm:flex-none" title="{{ $item['pending'] }}">{{ $item['primary'] }}</span>
                            @endif
                        </span>
                    </div>
                </div>
            </li>
        @endforeach
        @if ($kaca['queue'] === [])
            <li class="grid place-items-center rounded-[16px] border border-dashed border-line-strong px-4 py-12 text-center">
                <p class="text-[16px] font-bold">Antrean bersih</p>
                <p class="mt-1 text-[14px] text-meta">Tidak ada keputusan yang menunggu Anda.</p>
            </li>
        @else
            <li x-show="visibleCount() === 0" x-cloak class="grid place-items-center rounded-[16px] border border-dashed border-line-strong px-4 py-12 text-center">
                <p class="text-[16px] font-bold">Tidak ada di kategori ini</p>
                <p class="mt-1 text-[14px] text-meta">Pilih "Semua" untuk melihat antrean lain.</p>
            </li>
        @endif
    </ul>

    <p class="mono mt-3 flex flex-wrap gap-x-3 gap-y-1 border-t border-line pt-3 text-[12px] text-meta">
        <span>Spasi atau Enter tinjau</span>
        <span aria-hidden="true">·</span>
        <span>Tab pindah baris</span>
    </p>
</section>

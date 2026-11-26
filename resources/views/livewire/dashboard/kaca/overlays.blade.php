{{-- Laci keputusan, dialog antrean, dan palet perintah (Lab › DecisionDrawer, FullQueue, CommandPalette). --}}
{{-- Tidak ada keputusan yang diambil di sini: laci hanya menampilkan rincian dan membuka halaman keputusan bila ada. --}}

{{-- ================= Laci keputusan ================= --}}
<div x-show="drawerOpen" x-cloak class="fixed inset-0 z-40" role="presentation">
    <div class="absolute inset-0" style="background: var(--scrim)" @click="closeDrawer()" x-show="drawerOpen" x-transition.opacity.duration.200ms></div>
    <aside role="dialog" aria-modal="true" aria-labelledby="drawer-title" x-trap.noscroll="drawerOpen"
           x-show="drawerOpen" x-transition:enter="kaca-spring" x-transition:enter-start="opacity-0 translate-x-[108%] sm:translate-x-[108%] max-sm:translate-y-full max-sm:translate-x-0" x-transition:enter-end="opacity-100 translate-x-0 translate-y-0"
           x-transition:leave="transition duration-200 ease-in" x-transition:leave-start="opacity-100 translate-x-0 translate-y-0" x-transition:leave-end="opacity-0 translate-x-[108%] max-sm:translate-y-full max-sm:translate-x-0"
           class="glass-strong absolute inset-x-0 top-[7vh] bottom-0 flex flex-col overflow-hidden rounded-t-[26px] text-ink sm:top-2 sm:right-2 sm:bottom-2 sm:left-auto sm:w-[min(460px,calc(100vw-16px))] sm:rounded-[24px]">
        <template x-if="item">
            <div class="flex min-h-0 flex-1 flex-col">
                <div class="relative h-28 shrink-0 overflow-hidden">
                    <img :src="item.photo" alt="" class="absolute inset-0 size-full object-cover">
                    <div class="absolute inset-0" style="background: linear-gradient(to top, rgba(9,26,18,.74), rgba(9,26,18,.12))"></div>
                    <div class="absolute inset-x-3 top-3 flex items-center justify-between gap-2">
                        <div class="flex items-center gap-1 rounded-[12px] bg-[#1e2a08]/80 p-1 text-[#fef9c3] backdrop-blur">
                            <button type="button" @click="nav(-1)" :disabled="index <= 0" class="grid size-9 place-items-center rounded-[9px] hover:bg-white/10 disabled:opacity-35" aria-label="Keputusan sebelumnya (K)"><x-ui.icon name="chevron-left" class="size-4" /></button>
                            <span class="px-1 text-[13px] font-bold tabular-nums" x-text="(index + 1) + ' dari ' + queue.length"></span>
                            <button type="button" @click="nav(1)" :disabled="index >= queue.length - 1" class="grid size-9 place-items-center rounded-[9px] hover:bg-white/10 disabled:opacity-35" aria-label="Keputusan berikutnya (J)"><x-ui.icon name="chevron-right" class="size-4" /></button>
                        </div>
                        <button type="button" @click="closeDrawer()" class="grid size-11 place-items-center rounded-full bg-[#fef9c3]/90 text-[#1e2a08]" aria-label="Tutup panel"><x-ui.icon name="x" class="size-4" /></button>
                    </div>
                    <p class="absolute bottom-3 left-4 flex items-center gap-2 text-[13px] font-semibold text-[#fef9c3]">
                        <span class="mono rounded-[6px] bg-black/30 px-1.5 py-px" x-text="item.id"></span><span x-text="item.group"></span>
                    </p>
                </div>

                <div class="relative flex-1 overflow-x-hidden overflow-y-auto px-5 pt-4 pb-5">
                    <h2 id="drawer-title" class="text-[21px] leading-tight font-black tracking-tight text-balance" x-text="item.title"></h2>
                    <p class="mt-1 text-[14px] text-meta" x-text="item.meta"></p>

                    <div class="mt-4 rounded-[16px] border border-line bg-field p-4">
                        <p class="text-[13px] font-semibold text-meta" x-text="item.detail.amountLabel"></p>
                        <p class="mt-1 text-[32px] leading-none font-black tracking-tight tabular-nums" x-text="item.detail.amount"></p>
                        <p class="mt-1.5 text-[13px] text-meta" x-text="item.detail.amountNote" x-show="item.detail.amountNote"></p>
                    </div>

                    <div class="mt-4">
                        <h3 class="text-[15px] font-extrabold" x-text="item.detail.reasonsTitle"></h3>
                        <ul class="mt-2 space-y-2">
                            <template x-for="r in item.detail.reasons" :key="r">
                                <li class="flex gap-2.5 text-[14px] leading-snug">
                                    <x-ui.icon name="check-circle" class="mt-0.5 size-[18px] shrink-0 text-primary" />
                                    <span x-text="r"></span>
                                </li>
                            </template>
                        </ul>
                    </div>

                    <div class="mt-5" x-show="item.detail.breakdown.length > 0">
                        <h3 class="text-[15px] font-extrabold" x-text="item.detail.breakdownTitle"></h3>
                        <ul class="mt-2 divide-y divide-line">
                            <template x-for="b in item.detail.breakdown" :key="b.label + b.value">
                                <li class="flex items-center justify-between gap-3 py-2.5 text-[14px]">
                                    <span class="min-w-0 truncate" x-text="b.label"></span>
                                    <span class="flex shrink-0 items-center gap-1.5 font-bold tabular-nums" :class="b.warn ? 'text-warning' : ''">
                                        <x-ui.icon name="alert" class="size-4" x-show="b.warn" />
                                        <span x-text="b.value"></span>
                                    </span>
                                </li>
                            </template>
                        </ul>
                    </div>

                    <div x-show="item.detail.file" class="mt-4 flex min-h-11 items-center gap-3 rounded-[14px] border border-line p-3 text-[14px]">
                        <x-ui.icon name="file" class="size-5 text-danger" />
                        <span class="min-w-0 flex-1 truncate font-semibold" x-text="item.detail.file"></span>
                    </div>
                </div>

                <div class="shrink-0 border-t border-line px-5 py-4">
                    <div class="flex flex-wrap items-center gap-2">
                        <template x-if="item.url">
                            <a :href="item.url" class="ml-auto flex h-11 items-center gap-2 rounded-[12px] bg-primary px-4 text-[14px] font-extrabold text-primary-ink shadow-[0_10px_22px_-12px_rgba(30,42,8,.9)]">
                                <span x-text="'Buka halaman ' + item.primary.toLowerCase()"></span><x-ui.icon name="arrow-right" class="size-4" />
                            </a>
                        </template>
                        <template x-if="!item.url">
                            <p class="ml-auto text-[13px] font-semibold text-meta" x-text="item.pending"></p>
                        </template>
                        <p class="mono hidden basis-full pt-1 text-right text-[12px] text-meta sm:block">J / K pindah keputusan · Esc tutup</p>
                    </div>
                </div>
            </div>
        </template>
    </aside>
</div>

{{-- ================= Semua antrean ================= --}}
<div x-show="fullOpen" x-cloak class="fixed inset-0 z-40 flex items-end justify-center sm:items-center sm:p-6">
    <div class="absolute inset-0" style="background: var(--scrim)" @click="fullOpen = false"></div>
    <div role="dialog" aria-modal="true" aria-labelledby="fullq-title" x-trap.noscroll="fullOpen" x-show="fullOpen" x-transition:enter="kaca-spring" x-transition:enter-start="opacity-0 translate-y-10 scale-[.98]" x-transition:enter-end="opacity-100 translate-y-0 scale-100" x-transition:leave="transition duration-200 ease-in" x-transition:leave-end="opacity-0 translate-y-8"
         class="glass-strong relative flex max-h-[88vh] w-full max-w-[760px] flex-col overflow-hidden rounded-t-[26px] text-ink sm:rounded-[26px]">
        <div class="flex items-center justify-between gap-3 border-b border-line px-5 py-4">
            <h2 id="fullq-title" class="text-[20px] font-black tracking-tight">Antrean keputusan · <span x-text="queue.length"></span></h2>
            <button type="button" @click="fullOpen = false" class="grid size-11 place-items-center rounded-full hover:bg-primary-soft" aria-label="Tutup"><x-ui.icon name="x" class="size-5" /></button>
        </div>
        <div class="flex-1 overflow-y-auto px-5 pt-3 pb-5">
            <ul class="divide-y divide-line">
                <template x-for="(i, n) in queue" :key="i.key">
                    <li>
                        <button type="button" @click="fullOpen = false; review(n)" class="grid w-full grid-cols-[40px_minmax(0,1fr)_auto] items-center gap-3 py-3 text-left">
                            <img :src="i.photo" alt="" class="size-10 rounded-[10px] object-cover">
                            <span class="min-w-0"><span class="block truncate text-[15px] font-bold" x-text="i.title"></span><span class="mono block text-[12px] text-meta" x-text="i.id + ' · ' + i.group"></span></span>
                            <span class="text-[13px] font-bold" :class="i.riskTone === 'danger' ? 'text-danger' : 'text-warning'" x-text="i.risk"></span>
                        </button>
                    </li>
                </template>
                <li x-show="queue.length === 0" class="py-10 text-center text-[14px] text-meta">Tidak ada keputusan yang menunggu.</li>
            </ul>
        </div>
    </div>
</div>

{{-- ================= Palet perintah ================= --}}
<div x-show="paletteOpen" x-cloak class="fixed inset-0 z-50 flex items-start justify-center px-3 pt-[12vh]">
    <div class="absolute inset-0" style="background: var(--scrim)" @click="paletteOpen = false"></div>
    <div role="dialog" aria-modal="true" aria-label="Cari dan perintah" x-trap.noscroll="paletteOpen" x-show="paletteOpen" x-transition:enter="kaca-spring" x-transition:enter-start="opacity-0 -translate-y-4 scale-[.97]" x-transition:enter-end="opacity-100 translate-y-0 scale-100" x-transition:leave="transition duration-150 ease-in" x-transition:leave-end="opacity-0 -translate-y-2 scale-[.98]"
         @keydown.escape.prevent="paletteOpen = false" @keydown.arrow-down.prevent="active = Math.min(rows.length - 1, active + 1)" @keydown.arrow-up.prevent="active = Math.max(0, active - 1)" @keydown.enter.prevent="runRow()"
         class="glass-strong relative w-full max-w-[640px] overflow-hidden rounded-[22px] text-ink">
        <div class="flex items-center gap-3 border-b border-line px-4">
            <x-ui.icon name="search" class="size-5 text-meta" />
            <input x-ref="paletteInput" x-model="q" @input="active = 0" placeholder="Coba: sengketa, verifikasi mitra, plastik" aria-label="Cari" class="h-14 min-w-0 flex-1 bg-transparent text-[16px] outline-none placeholder:text-meta/80">
            <kbd class="mono rounded-[6px] border border-line-strong px-1.5 py-0.5 text-[12px] text-meta">Esc</kbd>
        </div>
        <div class="max-h-[56vh] overflow-y-auto p-2">
            <div x-show="chips.length > 0" class="mb-2 flex items-start gap-3 rounded-[14px] bg-[var(--accent-soft)] px-3 py-2.5">
                <x-ui.icon name="sparkles" class="mt-0.5 size-[18px] shrink-0 text-accent" />
                <div class="min-w-0 flex-1">
                    <p class="text-[14px] font-bold">Saring dengan:</p>
                    <p class="mt-1 flex flex-wrap gap-1.5">
                        <template x-for="c in chips" :key="c[0] + c[1]"><span class="mono rounded-[7px] border border-line-strong bg-[var(--panel-strong)] px-1.5 py-0.5 text-[12px]"><span x-text="c[0] + ': '"></span><strong x-text="c[1]"></strong></span></template>
                    </p>
                </div>
                <span class="text-[12px] font-semibold text-meta" x-text="foundItems.length + ' cocok'"></span>
            </div>
            <template x-for="(row, n) in rows" :key="row.key">
                <div>
                    <p x-show="row.head" class="px-3 pt-3 pb-1 text-[12px] font-bold text-meta" x-text="row.head"></p>
                    <button type="button" @mouseenter="active = n" @click="active = n; runRow()" class="flex w-full items-center gap-3 rounded-[12px] px-3 py-2.5 text-left text-[14px]"
                            :class="n === active ? 'bg-primary-soft shadow-[inset_0_0_0_1px_var(--line-strong)]' : ''">
                        <span class="min-w-0 flex-1 truncate font-semibold" x-text="row.label"></span>
                        <span class="mono text-[12px] text-meta" x-text="row.hint"></span>
                    </button>
                </div>
            </template>
            <p x-show="rows.length === 0" class="px-3 py-8 text-center text-[14px] text-meta">Tidak ada yang cocok dengan “<span x-text="q"></span>”.</p>
        </div>
        <div class="mono flex flex-wrap gap-x-4 gap-y-1 border-t border-line px-4 py-2.5 text-[12px] text-meta"><span>↑↓ pindah</span><span>↵ buka</span><span>Esc tutup</span></div>
    </div>
</div>

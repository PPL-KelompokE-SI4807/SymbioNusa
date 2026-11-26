{{-- Strip ringkasan per periode (Lab › SummaryStrip). Angka perbandingan periode adalah contoh Lab: DDL tidak punya tanggal order. --}}
<section class="glass overflow-hidden" :aria-label="'Ringkasan ' + period.toLowerCase()" :aria-busy="loading.toString()">
    <div class="flex items-center justify-between gap-3 px-3.5 pt-3 sm:px-5">
        <p class="text-[12px] font-semibold text-meta">Perbandingan periode</p>
        <span class="rounded-[6px] border border-dashed border-line-strong px-1.5 py-px text-[12px] text-meta">Data demo</span>
    </div>
    <ul class="grid grid-cols-2 lg:grid-cols-4">
        <template x-for="(m, i) in periods[period].metrics" :key="m.label">
            <li class="relative min-w-0 px-3.5 py-4 sm:px-5"
                :class="[i % 2 ? 'border-l border-line' : '', i >= 2 ? 'border-t border-line lg:border-t-0' : '', i === 2 ? 'lg:border-l' : ''].join(' ')">
                <p class="text-[13px] font-semibold text-meta" x-text="m.label"></p>
                <p class="mt-1 flex items-baseline gap-1.5">
                    <span class="text-[26px] leading-none font-black tracking-tight tabular-nums sm:text-[30px]" x-text="num(shown[i], m.d)"></span>
                    <span class="text-[14px] font-bold text-meta" x-text="m.unit"></span>
                </p>
                <p class="mt-2 flex flex-wrap items-center gap-x-1 text-[13px] font-bold" :class="better(m) ? 'text-primary' : 'text-danger'">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path :d="m.value >= m.prev ? 'M7 17 L17 7 M9 7 L17 7 L17 15' : 'M7 7 L17 17 M17 9 L17 17 L9 17'" />
                    </svg>
                    <span :class="better(m) ? '' : 'kaca-flag'" x-text="num(Math.abs((m.value - m.prev) / m.prev) * 100, 0) + '%'"></span>
                    <span class="font-medium text-meta" x-text="periods[period].compare"></span>
                </p>
                <div class="mt-2.5 space-y-1" aria-hidden="true">
                    <span class="block h-1.5 overflow-hidden rounded-full bg-track"><span class="block h-full rounded-full bg-primary transition-[width] duration-700 ease-[cubic-bezier(.16,1,.3,1)]" :style="{ width: loading ? '0%' : (m.value / Math.max(m.value, m.prev) * 100) + '%', transitionDelay: (i * 50) + 'ms' }"></span></span>
                    <span class="block h-1.5 overflow-hidden rounded-full bg-track"><span class="block h-full rounded-full transition-[width] duration-700 ease-[cubic-bezier(.16,1,.3,1)]" style="background: var(--c-plan)" :style="{ width: loading ? '0%' : (m.prev / Math.max(m.value, m.prev) * 100) + '%', transitionDelay: (i * 50 + 80) + 'ms' }"></span></span>
                </div>
                <span x-show="loading" x-cloak x-transition.opacity class="shimmer pointer-events-none absolute inset-1 rounded-[14px]"></span>
            </li>
        </template>
    </ul>
</section>

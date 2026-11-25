{{-- Umur tagihan (Lab › InvoiceAging). Invoice berstatus TERBIT, dikelompokkan dari jatuh tempo karena DDL tidak menyimpan tanggal terbit. --}}
@use('App\Support\Format')
@php
    $a = $kaca['aging'];
    $total = max(0.0001, $a['total']);
@endphp
<section class="glass flex h-full flex-col p-4 sm:p-5" aria-labelledby="aging-title" x-data="{ hover: null }">
    <h2 id="aging-title" class="card-title">Umur tagihan</h2>
    <p class="mt-1 text-[14px] text-meta">{{ $a['count'] }} invoice belum dibayar, dikelompokkan dari tanggal jatuh tempo.</p>
    <p class="mt-3 flex flex-wrap items-baseline gap-x-2">
        <span class="text-[30px] leading-none font-black tracking-tight tabular-nums">{{ Format::rupiah($a['total']) }}</span>
        <span class="text-[13px] font-semibold text-meta">belum dibayar</span>
    </p>

    <div class="mt-3 flex h-4 gap-1" role="img" aria-label="Pembagian tagihan per umur">
        @foreach ($a['buckets'] as $i => $b)
            @if ($b['amount'] > 0)
                <span class="relative h-full rounded-[5px] transition-opacity {{ $i === 1 ? 'hatch-bg' : '' }}"
                      style="flex-grow: {{ $b['amount'] }}; {{ $i === 1 ? 'background-color: var(--c-plan)' : 'background: '.$b['tone'] }}"
                      :style="hover !== null && hover !== {{ $i }} ? { opacity: 0.35 } : {}" @mouseenter="hover = {{ $i }}" @mouseleave="hover = null"></span>
            @endif
        @endforeach
        @if ($a['total'] <= 0)<span class="h-full flex-1 rounded-[5px] bg-track"></span>@endif
    </div>
    <ul class="mt-3 grid grid-cols-2 gap-x-4 gap-y-2">
        @foreach ($a['buckets'] as $i => $b)
            <li @mouseenter="hover = {{ $i }}" @mouseleave="hover = null" class="flex items-start gap-2 text-[13px]">
                <span class="mt-1 size-2.5 shrink-0 rounded-[3px] {{ $i === 1 ? 'hatch-bg' : '' }}" style="{{ $i === 1 ? 'background-color: var(--c-plan)' : 'background: '.$b['tone'] }}"></span>
                <span class="min-w-0">
                    <span class="block font-semibold text-meta">{{ $b['label'] }}</span>
                    <span class="block font-bold tabular-nums">{{ Format::rupiah($b['amount']) }} <span class="font-medium text-meta">· {{ $b['count'] }}</span></span>
                </span>
            </li>
        @endforeach
    </ul>

    <h3 class="mt-4 border-t border-line pt-4 text-[15px] font-extrabold">Paling lama</h3>
    @if ($a['oldest'] === [])
        <p class="mt-2 text-[14px] text-meta">Tidak ada invoice yang belum dibayar.</p>
    @else
        <ul class="mt-1 divide-y divide-line">
            @foreach ($a['oldest'] as $inv)
                <li class="flex items-center gap-3 py-2.5">
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-[14px] font-bold">{{ $inv['buyer'] }}</p>
                        <p class="mono text-[12px] text-meta">{{ $inv['id'] }} · {{ Format::rupiah($inv['amount']) }}</p>
                    </div>
                    <span class="shrink-0 text-[13px] font-extrabold tabular-nums {{ $inv['days'] > 60 ? 'text-danger' : ($inv['days'] > 0 ? 'text-accent-text' : 'text-meta') }}">
                        {{ $inv['days'] > 0 ? 'lewat '.Format::number($inv['days']).' hari' : ($inv['days'] === 0 ? 'jatuh tempo hari ini' : Format::number(abs($inv['days'])).' hari lagi') }}
                    </span>
                </li>
            @endforeach
        </ul>
    @endif
</section>

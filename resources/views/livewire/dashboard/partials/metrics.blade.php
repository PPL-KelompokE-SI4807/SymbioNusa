{{-- Parameter: $metricRows [label, value, unit, segs [[nilai, token warna]], legend [[token warna, teks]]]. --}}
{{-- Strip metrik: satu permukaan putih. Data dampak tidak bertanggal (DDL), jadi tidak ada delta periode;
     tiap metrik membawa bilah bagian-dari-keseluruhan dari data yang ada, dengan legenda teks. --}}
<x-ui.card :padding="false">
    <dl class="grid grid-cols-1 divide-y divide-outline-hairline sm:grid-cols-2 sm:divide-y-0 xl:grid-cols-4 xl:divide-x">
        @foreach ($metricRows as $i => $row)
            @php($segTotal = array_sum(array_column($row['segs'], 0)))
            <div @class(['min-w-0 p-[18px] sm:p-6', 'sm:border-t sm:border-outline-hairline xl:border-t-0' => $i >= 2, 'sm:border-l sm:border-outline-hairline xl:border-l-0' => $i % 2 === 1])>
                <dt class="text-label-md font-semibold text-on-surface-muted">{{ $row['label'] }}</dt>
                <dd class="mt-1.5 flex items-baseline gap-1.5 tabular-nums">
                    <span class="text-dash-metric">{{ $row['value'] }}</span>
                    @if ($row['unit'])<span class="text-body-md font-bold text-on-surface-muted">{{ $row['unit'] }}</span>@endif
                </dd>
                <dd class="mt-3">
                    <span class="mini-bar" aria-hidden="true">
                        @foreach ($row['segs'] as [$v, $color])
                            @if ($segTotal > 0 && $v > 0)
                                <span style="width: {{ round(100 * $v / $segTotal, 2) }}%; background: var(--color-{{ $color }})"></span>
                            @endif
                        @endforeach
                    </span>
                    <span class="mt-2 flex flex-wrap gap-x-3 gap-y-1 text-body-sm font-medium text-on-surface-muted">
                        @foreach ($row['legend'] as [$color, $text])
                            <span class="inline-flex items-center gap-1.5"><span class="size-2 shrink-0" style="background: var(--color-{{ $color }})" aria-hidden="true"></span>{{ $text }}</span>
                        @endforeach
                    </span>
                </dd>
            </div>
        @endforeach
    </dl>
</x-ui.card>

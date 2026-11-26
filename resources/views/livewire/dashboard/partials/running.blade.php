{{--
    Zona 2 "Sedang berjalan" — bersama untuk semua peran: jadwal 7 hari + baris dengan blok tanggal dan posisi di alur 10 langkah.
    Parameter: $running ['count', 'week' => 7 hari, 'items' => [id, title, meta, status, step?, step_label?, time?, date?, date_caption?]],
    $runningEmpty (satu baris penyebab saat kosong).
--}}
@use('App\Support\Format')
@php($focusRing ??= 'focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus-ring')
<x-ui.card :padding="false" class="p-[18px] sm:p-6" aria-labelledby="running-title" x-data="daftarBerjalan()">
    <p class="dash-eyebrow">Zona 2 · Berjalan</p>
    <h2 id="running-title" class="mt-2 text-dash-title">Sedang berjalan · {{ Format::number($running['count']) }}</h2>
    @if ($running['items'] === [])
        <p class="mt-4 text-body-md text-on-surface-muted">{{ $runningEmpty }}</p>
    @else
        {{-- Jadwal 7 hari: tinggi batang = jumlah inspeksi/pengiriman terjadwal per hari (WIB). Klik hari menyorot barisnya. --}}
        @php($weekMax = max(1, collect($running['week'])->max('count')))
        <div class="mt-4" role="group" aria-label="Jadwal 7 hari">
            <p class="text-label-sm font-semibold text-on-surface-muted">Jadwal 7 hari · {{ $running['week'][0]['label'] }} s.d. {{ $running['week'][6]['label'] }}</p>
            <div class="mt-2 grid grid-cols-7 gap-1.5">
                @foreach ($running['week'] as $d)
                    <button type="button" @click="pickDay('{{ $d['date'] }}')" :aria-pressed="(day === '{{ $d['date'] }}').toString()"
                            @disabled($d['count'] === 0)
                            class="week-day {{ $focusRing }}">
                        <span class="week-bar-track" aria-hidden="true">
                            @if ($d['count'] > 0)
                                <span class="week-bar" style="height: {{ round(100 * $d['count'] / $weekMax) }}%"></span>
                            @endif
                        </span>
                        <span class="text-label-md font-extrabold tabular-nums">{{ $d['count'] > 0 ? Format::number($d['count']) : '–' }}</span>
                        <span @class(['text-label-sm text-on-surface-muted', 'font-bold text-on-surface!' => $loop->first])>{{ $d['dow'] }}</span>
                        <span class="text-label-sm text-on-surface-muted tabular-nums">{{ $d['day'] }}</span>
                        <span class="sr-only">— {{ Format::number($d['count']) }} proses terjadwal pada {{ $d['label'] }}</span>
                    </button>
                @endforeach
            </div>
        </div>

        <ul x-ref="list" class="mt-4">
            @foreach ($running['items'] as $idx => $item)
                @php($iso = $item['date']['iso'] ?? '')
                <li @if ($idx >= 4) x-show="open" x-cloak data-extra @endif data-date="{{ $iso }}"
                    :class="day && day !== '{{ $iso }}' ? 'opacity-40' : ''"
                    class="flex flex-wrap items-center gap-x-4 gap-y-1 rounded-md border-t border-outline-hairline py-3 transition-opacity first:border-t-0 sm:flex-nowrap">
                    <p class="date-block w-[76px] shrink-0" @if ($item['date']) title="{{ $item['date']['label'] }}" @endif>
                        @if ($item['date'])
                            <b>{{ $item['date']['day'] }}</b>
                            <span>{{ $item['date']['month'] }}@isset($item['date_caption'])<br>{{ $item['date_caption'] }}@endisset</span>
                        @else
                            <b aria-hidden="true">–</b><span>belum dijadwalkan</span>
                        @endif
                    </p>
                    <div class="min-w-0 flex-1 basis-[calc(100%-6rem)] sm:basis-0">
                        <p class="text-dash-row">{{ $item['title'] }}</p>
                        <p class="mt-0.5 text-body-sm text-on-surface-muted">{{ collect([$item['time'] ? $item['time'].' WIB' : null, $item['meta']])->filter()->implode(' · ') }}</p>
                        @if ($item['step'])
                            {{-- Posisi order di alur 10 langkah: selesai / sekarang / belum. --}}
                            <p class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1">
                                <span class="step-bar" aria-hidden="true">
                                    @for ($n = 1; $n <= 10; $n++)
                                        <i @class(['done' => $n < $item['step'], 'now' => $n === $item['step']])></i>
                                    @endfor
                                </span>
                                <span class="text-label-sm font-semibold text-on-surface-muted tabular-nums">Tahap {{ $item['step'] }} dari 10 · {{ $item['step_label'] }}</span>
                            </p>
                        @endif
                    </div>
                    <p class="shrink-0 pl-[92px] text-label-md font-semibold text-on-surface sm:pl-0">{{ $item['status'] }}</p>
                </li>
            @endforeach
        </ul>
        @if (count($running['items']) > 4)
            <button type="button" @click="toggle()" :aria-expanded="open.toString()"
                    class="mt-1 inline-flex h-10 items-center rounded-sm text-label-md font-bold text-primary hover:underline underline-offset-4 {{ $focusRing }}"
                    x-text="open ? 'Tampilkan lebih sedikit' : 'Lihat {{ count($running['items']) - 4 }} lainnya'">Lihat {{ count($running['items']) - 4 }} lainnya</button>
        @endif
        @if ($running['count'] > count($running['items']))
            <p class="mt-1 text-body-sm text-on-surface-muted">Menampilkan {{ Format::number(count($running['items'])) }} dari {{ Format::number($running['count']) }}</p>
        @endif
    @endif
</x-ui.card>

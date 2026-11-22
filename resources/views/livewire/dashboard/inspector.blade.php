{{--
    Dasbor Inspector (FND-04, DESIGN.md amandemen FND v8): 4 tile → Jadwal inspeksi + Verifikasi dokumen (kiri)
    dan "Berikutnya" (kanan, satu-satunya tombol utama). Halaman tugas belum ada, jadi aksi baris belum bertautan.
--}}
@use('App\Support\Format')
@use('App\Support\StatusMap')
@use('Illuminate\Support\Carbon')

<div class="space-y-6">
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($tiles as $tile)
            <x-ui.kpi :label="$tile['label']" :value="$tile['value']" :unit="$tile['unit']" :caption="$tile['caption']" :tone="$tile['tone']" />
        @endforeach
    </div>

    <div class="grid items-start gap-6 xl:grid-cols-[minmax(0,1fr)_380px]">
        <div class="min-w-0 space-y-6">
            <x-ui.card :padding="false">
                <div class="flex items-center justify-between gap-3 px-6 pt-5 pb-2">
                    <h2 class="text-headline-md">Jadwal inspeksi</h2>
                    <p class="text-body-sm text-on-surface-subtle">{{ count($schedule) }} tugas aktif</p>
                </div>
                @if ($schedule === [])
                    <p class="px-6 pb-6 text-body-md text-on-surface-muted">Belum ada tugas inspeksi yang aktif. Tugas baru muncul di sini saat Admin menugaskan Anda.</p>
                @else
                    <ul class="px-3 pb-3">
                        @foreach ($schedule as ['inspection' => $inspection, 'material' => $material])
                            @php($order = $inspection->purchaseOrder)
                            <li class="flex flex-wrap items-center gap-x-4 gap-y-2 rounded-md p-3 hover:bg-surface-muted sm:flex-nowrap">
                                @if ($material['photo'] ?? null)
                                    <img src="{{ $material['photo'] }}" alt="" class="size-12 shrink-0 rounded-sm object-cover">
                                @else
                                    <x-ui.monogram :name="$order->seller->nama ?? 'PO'" />
                                @endif
                                <div class="min-w-0 flex-1 basis-[calc(100%-4rem)] sm:basis-0">
                                    <p class="text-label-md font-semibold">{{ $material['nama'] ?? 'Material' }} · {{ Format::kg($order->volume_kg ?? 0) }}</p>
                                    <p class="mt-0.5 text-body-sm text-on-surface-subtle">PO-{{ $inspection->purchase_orders_id_purchase_order }} · {{ $order->seller->nama ?? '–' }} → {{ $order->buyer->nama ?? '–' }}</p>
                                </div>
                                <div class="shrink-0 pl-14 sm:w-28 sm:pl-0">
                                    <p class="text-label-md">{{ $inspection->jadwal ? Format::dayMonth($inspection->jadwal).', '.Carbon::parse($inspection->jadwal)->format('H.i') : 'Belum dijadwalkan' }}</p>
                                    <p class="text-body-sm text-on-surface-subtle">{{ StatusMap::label('inspections.mode_verifikasi', $inspection->mode_verifikasi) }}</p>
                                </div>
                                <div class="shrink-0 sm:w-32">
                                    <x-ui.badge :variant="StatusMap::badge('inspections.status', $inspection->status)">{{ StatusMap::label('inspections.status', $inspection->status) }}</x-ui.badge>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-ui.card>

            <x-ui.card :padding="false">
                <div class="flex items-center justify-between gap-3 px-6 pt-5 pb-2">
                    <h2 class="text-headline-md">Verifikasi dokumen</h2>
                    <p class="text-body-sm text-on-surface-subtle">{{ $docs->count() }} menunggu</p>
                </div>
                @if ($docs->isEmpty())
                    <p class="px-6 pb-6 text-body-md text-on-surface-muted">Tidak ada dokumen yang menunggu diperiksa.</p>
                @else
                    <ul class="px-3 pb-3">
                        @foreach ($docs as $doc)
                            <li class="flex items-center gap-4 rounded-md p-3">
                                <x-ui.monogram :name="$doc->seller ?? 'Dokumen'" />
                                <div class="min-w-0 flex-1">
                                    <p class="text-label-md font-semibold">Dokumen PO-{{ $doc->po }}</p>
                                    <p class="mt-0.5 text-body-sm text-on-surface-subtle">{{ $doc->seller ?? '–' }} · mode desk</p>
                                </div>
                                <x-ui.badge variant="warning">Menunggu</x-ui.badge>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-ui.card>
        </div>

        <x-ui.card title="Berikutnya">
            @if ($next)
                @php($inspection = $next['inspection'])
                @if ($next['material']['photo'] ?? null)
                    <img src="{{ $next['material']['photo'] }}" alt="" class="h-36 w-full rounded-md object-cover">
                @endif
                <p class="mt-4 text-body-lg font-semibold">Inspeksi {{ strtolower(StatusMap::label('inspections.mode_verifikasi', $inspection->mode_verifikasi)) }} PO-{{ $inspection->purchase_orders_id_purchase_order }}</p>
                <dl class="mt-3 space-y-3">
                    @foreach ([
                        ['clock', 'Waktu', $inspection->jadwal ? Format::date($inspection->jadwal).', '.Carbon::parse($inspection->jadwal)->format('H.i').' WIB' : 'Belum dijadwalkan'],
                        ['map-pin', 'Lokasi', $next['material']['lokasi'] ?? 'Belum diisi Seller'],
                        ['layers', 'Material', ($next['material']['nama'] ?? 'Material').' · '.Format::kg($inspection->purchaseOrder->volume_kg ?? 0)],
                    ] as [$icon, $label, $value])
                        <div class="relative min-h-9 pl-12">
                            <dt class="text-body-sm text-on-surface-subtle">
                                <span class="absolute top-0 left-0 flex size-9 items-center justify-center rounded-sm bg-surface-muted text-secondary" aria-hidden="true">
                                    <x-ui.icon :name="$icon" class="size-[18px]" />
                                </span>
                                {{ $label }}
                            </dt>
                            <dd class="text-label-md font-semibold">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>
                <p class="mt-4 text-body-sm text-on-surface-subtle">Halaman Tugas Inspeksi segera hadir; laporan mutu diisi dari sana.</p>
            @else
                <p class="text-body-md text-on-surface-muted">Tidak ada inspeksi berikutnya.</p>
            @endif
        </x-ui.card>
    </div>
</div>

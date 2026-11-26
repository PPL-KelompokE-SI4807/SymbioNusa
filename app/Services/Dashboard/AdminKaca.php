<?php

namespace App\Services\Dashboard;

use App\Models\Inspection;
use App\Support\Format;
use App\Support\StatusMap;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Data dasbor Admin varian Kaca (DESIGN.md, amandemen FND v12). Bentuk datanya mengikuti SymbioNusa Lab
 * (D:\PPL\SymbioNusa-Lab\src\data\demo.js) supaya tampilannya sama, tetapi isinya dari DB bila DDL menyimpannya.
 *
 * Yang berasal dari data nyata: Sungai Order, antrean keputusan, inspeksi per jam, umur tagihan, aktivitas,
 * beban Inspector, tonase per kategori, band dampak. Yang TIDAK bisa dihitung dari DDL (tidak ada tanggal
 * pembuatan order, kota perusahaan, atau kapasitas Inspector) memakai contoh Lab dan membawa tanda "demo":
 * perbandingan periode di strip ringkasan, dan peta pengawasan.
 */
final class AdminKaca
{
    private const PHOTO = [
        'plastik' => 'plastik.jpg', 'pangan' => 'ampas-tahu.jpg', 'tekstil' => 'kain.jpg', 'kayu' => 'serbuk-kayu.jpg', 'kertas' => 'kardus.jpg',
    ];

    private const EXIT_TONE = ['DITOLAK' => 'danger', 'GAGAL_INSPEKSI' => 'danger', 'SENGKETA' => 'danger', 'DIBATALKAN' => 'meta'];

    /**
     * @param  array<string, mixed>  $admin  hasil DashboardService::admin()
     * @return array<string, mixed>
     */
    public function build(array $admin, ?Carbon $now = null): array
    {
        $now ??= Carbon::now('Asia/Jakarta');
        $queue = $this->queue($admin['queue'], $now);
        $river = $this->river($admin['flow']);

        return [
            'now' => [
                'label' => $this->dayName($now).', '.$now->format('j').' '.$this->monthShort($now).' '.$now->format('Y'),
                'time' => $now->format('H:i'),
                'hour' => (int) $now->format('G') + (int) $now->format('i') / 60,
            ],
            'summary' => [
                'running' => $river['active_total'],
                'pending' => count($queue),
                'stuck' => $river['stuck'],
            ],
            'periods' => $this->periods(),
            'queue' => $queue,
            'hourly' => $this->hourly($now),
            'river' => $river,
            'regions' => $this->regions(),
            'aging' => $this->aging($now),
            'activity' => $this->activity($now),
            'inspectors' => $this->inspectorLoad(),
            'materials' => $this->materials($admin['materials']),
            'impact' => $this->impact($admin['metrics']),
        ];
    }

    private function photo(?string $class): string
    {
        return asset('images/material/'.(self::PHOTO[$class ?? ''] ?? 'kardus.jpg'));
    }

    private function classOf(?string $kategori): ?string
    {
        return DashboardService::categoryColor($kategori);
    }

    private function dayName(Carbon $d): string
    {
        return ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'][$d->dayOfWeek];
    }

    private function monthShort(Carbon $d): string
    {
        return ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'][$d->month - 1];
    }

    // ------------------------------------------------------------------ antrean keputusan

    /**
     * Antrean dari adminQueue(), diratakan ke bentuk Lab dan diurutkan dari risiko tertinggi.
     * Tombol aksi hanya ada bila halaman keputusannya ada (url); tidak ada keputusan yang diambil di dasbor.
     *
     * @return list<array<string, mixed>>
     */
    private function queue(array $q, Carbon $now): array
    {
        $items = [];

        foreach ($q['groups'] as $group) {
            foreach ($group['items'] as $item) {
                $items[] = match ($group['key']) {
                    'sengketa' => $this->disputeItem($item),
                    'sertifikat' => $this->certItem($item),
                    'penugasan' => $this->assignItem($item, $now),
                    default => $this->verifyItem($item),
                };
            }
        }

        usort($items, fn (array $a, array $b) => [$a['rank'], $a['sort']] <=> [$b['rank'], $b['sort']]);

        return array_map(fn (array $i) => collect($i)->except(['rank', 'sort'])->all(), $items);
    }

    private function disputeItem(array $item): array
    {
        $id = (int) str_replace('sengketa-', '', $item['id']);
        $d = DB::table('disputes AS d')
            ->leftJoin('purchase_orders AS po', 'po.id_purchase_order', '=', 'd.purchase_orders_id_purchase_order')
            ->leftJoin('rfq_contracts AS r', 'r.id_rfq_contract', '=', 'po.rfq_contracts_id_rfq_contract')
            ->leftJoin('listings AS l', 'l.id_listings', '=', 'r.listings_id_listings')
            ->leftJoin('categories AS c', 'c.id_categories', '=', 'l.categories_id_categories')
            ->leftJoin('companies AS s', 's.id_company', '=', 'po.companies_id_seller')
            ->leftJoin('companies AS b', 'b.id_company', '=', 'po.companies_id_buyer')
            ->where('d.id_dispute', $id)
            ->first(['d.*', 'po.volume_kg', 'po.harga_per_kg', 'po.toleransi_persen', 'c.nama AS kategori', 's.nama AS seller', 'b.nama AS buyer']);
        $class = $this->classOf($d->kategori ?? null);
        $nilai = (float) ($d->volume_kg ?? 0) * (float) ($d->harga_per_kg ?? 0);

        return [
            'id' => 'DSP-'.str_pad((string) $id, 4, '0', STR_PAD_LEFT),
            'key' => $item['id'],
            'kind' => 'sengketa',
            'group' => 'Sengketa & Klaim',
            'title' => 'Sengketa #'.$id.' · PO-'.($d->purchase_orders_id_purchase_order ?? '–'),
            'meta' => $item['meta'],
            'age' => null,
            'risk' => 'Menunggu putusan',
            'riskTone' => 'danger',
            'primary' => 'Putuskan',
            'url' => $item['url'],
            'pending' => $item['url'] ? null : 'Halaman putusan sengketa segera hadir',
            'category' => $class,
            'photo' => $this->photo($class),
            'rank' => 0,
            'sort' => $id,
            'detail' => [
                'amountLabel' => 'Nilai order terkait',
                'amount' => Format::rupiah($nilai),
                'amountNote' => $d ? Format::kg($d->volume_kg).' × '.Format::rupiah($d->harga_per_kg).'/kg' : '',
                'reasonsTitle' => 'Isi sengketa',
                'reasons' => array_values(array_filter([
                    $d && $d->deskripsi ? $d->deskripsi : 'Pelapor belum menulis uraian.',
                    $d ? StatusMap::label('disputes.jenis', $d->jenis) : null,
                    $d && $d->toleransi_persen !== null ? 'Toleransi kontrak '.Format::number($d->toleransi_persen, 1).'%' : null,
                ])),
                'breakdownTitle' => 'Pihak dalam order',
                'breakdown' => [
                    ['label' => 'Seller', 'value' => $d->seller ?? '–'],
                    ['label' => 'Buyer', 'value' => $d->buyer ?? '–'],
                    ['label' => 'Pelapor', 'value' => $item['party'] ?? '–'],
                ],
                'file' => $d && $d->bukti_url ? basename($d->bukti_url) : null,
            ],
        ];
    }

    private function certItem(array $item): array
    {
        $id = (int) str_replace('sertifikat-', '', $item['id']);
        $left = (int) ($item['days_left'] ?? 0);
        $tasks = DB::table('inspections AS i')
            ->join('inspector_profiles AS ip', 'ip.id_user', '=', 'i.users_id_user')
            ->where('ip.id_inspector', $id)->whereIn('i.status', ['DITUGASKAN', 'DIPROSES'])
            ->orderBy('i.jadwal')->limit(5)->get(['i.purchase_orders_id_purchase_order AS po', 'i.jadwal']);
        $profile = DB::table('inspector_profiles')->where('id_inspector', $id)->first(['wilayah_tugas', 'sertifikat_url', 'berlaku_sampai']);
        $until = $profile?->berlaku_sampai ? Carbon::parse($profile->berlaku_sampai) : null;

        return [
            'id' => 'INS-'.str_pad((string) $id, 4, '0', STR_PAD_LEFT),
            'key' => $item['id'],
            'kind' => 'sertifikat',
            'group' => 'Registri Inspector',
            'title' => 'Sertifikat '.$item['title'].' berakhir '.$left.' hari lagi',
            'meta' => 'Inspector · '.($profile->wilayah_tugas ?? '–').' · '.$tasks->count().' tugas aktif',
            'expiresInDays' => $left,
            'age' => $until ? $this->dayMonth($until) : null,
            'risk' => $left <= 7 ? 'Segera Berakhir' : 'Berakhir ≤ 30 hari',
            'riskTone' => 'warning',
            'primary' => 'Perpanjang',
            'url' => $item['url'],
            'pending' => $item['url'] ? null : 'Halaman perpanjangan segera hadir',
            'category' => null,
            'photo' => $this->photo(null),
            'rank' => $left <= 7 ? 1 : 3,
            'sort' => $left,
            'detail' => [
                'amountLabel' => 'Tugas aktif',
                'amount' => $tasks->count().' inspeksi',
                'amountNote' => $until ? 'sertifikat berlaku sampai '.Format::date($until) : '',
                'reasonsTitle' => 'Dampaknya',
                'reasons' => [
                    $until ? 'Setelah '.$this->dayMonth($until).', Inspector tidak bisa menerima tugas baru.' : 'Tanggal berakhir belum tercatat.',
                    'Tugas yang sudah ditugaskan tetap bisa diselesaikan.',
                ],
                'breakdownTitle' => 'Tugas aktif',
                'breakdown' => $tasks->map(fn ($t) => [
                    'label' => 'PO-'.$t->po,
                    'value' => $t->jadwal ? Carbon::parse($t->jadwal)->timezone('Asia/Jakarta')->format('j M, H.i') : 'Belum dijadwalkan',
                ])->all(),
                'file' => $profile && $profile->sertifikat_url ? basename($profile->sertifikat_url) : null,
            ],
        ];
    }

    private function assignItem(array $item, Carbon $now): array
    {
        $po = (int) $item['po'];
        $row = DB::table('purchase_orders AS po')
            ->leftJoin('rfq_contracts AS r', 'r.id_rfq_contract', '=', 'po.rfq_contracts_id_rfq_contract')
            ->leftJoin('listings AS l', 'l.id_listings', '=', 'r.listings_id_listings')
            ->leftJoin('categories AS c', 'c.id_categories', '=', 'l.categories_id_categories')
            ->where('po.id_purchase_order', $po)
            ->first(['po.volume_kg', 'po.harga_per_kg', 'c.nama AS kategori', 'l.lokasi']);
        $class = $this->classOf($row->kategori ?? null);
        $since = DB::table('order_status_history')->where('purchase_orders_id_purchase_order', $po)->where('ke', 'DIKONFIRMASI')->max('waktu');
        $hours = $since ? (int) Carbon::parse($since)->diffInHours($now) : null;
        $load = $this->activeInspectors();

        return [
            'id' => 'PO-'.$po,
            'key' => $item['id'],
            'kind' => 'penugasan',
            'group' => 'Penugasan Inspeksi',
            'title' => 'PO-'.$po.' belum punya Inspector',
            'meta' => $item['meta'],
            'age' => $hours === null ? null : $this->humanHours($hours),
            'ageHours' => $hours,
            'risk' => 'Menunggu',
            'riskTone' => 'warning',
            'primary' => 'Tugaskan',
            'url' => $item['url'],
            'pending' => $item['url'] ? null : 'Halaman penugasan inspeksi segera hadir',
            'category' => $class,
            'photo' => $this->photo($class),
            'rank' => 2,
            'sort' => $po,
            'detail' => [
                'amountLabel' => 'Nilai order',
                'amount' => Format::rupiah((float) ($row->volume_kg ?? 0) * (float) ($row->harga_per_kg ?? 0)),
                'amountNote' => $row ? Format::kg($row->volume_kg).' × '.Format::rupiah($row->harga_per_kg).'/kg' : '',
                'reasonsTitle' => 'Yang perlu dilakukan',
                'reasons' => [
                    'Order sudah dikonfirmasi dan belum punya Inspector yang ditugaskan.',
                    $row && $row->lokasi ? 'Lokasi material: '.$row->lokasi : 'Lokasi material belum tercatat.',
                ],
                'breakdownTitle' => 'Beban Inspector aktif',
                'breakdown' => $load->map(fn ($i) => ['label' => $i['nama'], 'value' => $i['aktif'].' tugas aktif · '.$i['wilayah']])->all(),
                'file' => null,
            ],
        ];
    }

    private function verifyItem(array $item): array
    {
        $id = (int) str_replace('verifikasi-', '', $item['id']);
        $docs = DB::table('company_documents')->where('id_company', $id)->get(['jenis', 'status', 'berlaku_sampai', 'file_url']);
        $valid = $docs->where('status', 'VALID')->count();
        $co = DB::table('companies')->where('id_company', $id)->first(['tipe', 'nib']);
        $class = $this->classOf(DB::table('listings AS l')->join('categories AS c', 'c.id_categories', '=', 'l.categories_id_categories')
            ->where('l.companies_id_company', $id)->value('c.nama'));

        return [
            'id' => 'MTR-'.str_pad((string) $id, 4, '0', STR_PAD_LEFT),
            'key' => $item['id'],
            'kind' => 'verifikasi',
            'group' => 'Verifikasi Mitra',
            'title' => $item['title'].' mengajukan verifikasi',
            'meta' => $item['meta'].' · '.$docs->count().' dokumen',
            'age' => null,
            'risk' => 'Menunggu',
            'riskTone' => 'warning',
            'primary' => 'Tinjau',
            'url' => $item['url'],
            'pending' => $item['url'] ? null : 'Halaman verifikasi segera hadir',
            'category' => $class,
            'photo' => $this->photo($class),
            'rank' => 2,
            'sort' => $id,
            'detail' => [
                'amountLabel' => 'Dokumen valid',
                'amount' => $valid.' dari '.$docs->count(),
                'amountNote' => $docs->count() === 0 ? 'belum ada dokumen diunggah' : 'dokumen wajib harus valid sebelum disetujui',
                'reasonsTitle' => 'Yang sudah dicek',
                'reasons' => [
                    $co && $co->nib ? 'NIB '.$co->nib.' tercatat.' : 'NIB belum diisi.',
                    $valid === $docs->count() && $docs->count() > 0 ? 'Semua dokumen berstatus valid.' : 'Ada dokumen yang belum valid atau belum diunggah.',
                ],
                'breakdownTitle' => 'Dokumen',
                'breakdown' => $docs->map(fn ($d) => [
                    'label' => $d->jenis,
                    'value' => $d->status === 'VALID' ? 'Valid'.($d->berlaku_sampai ? ' · '.Format::date($d->berlaku_sampai) : '') : ucfirst(strtolower($d->status)),
                    'warn' => $d->status !== 'VALID',
                ])->all(),
                'file' => $docs->first()?->file_url ? basename($docs->first()->file_url) : null,
            ],
        ];
    }

    private function humanHours(int $h): string
    {
        return $h >= 48 ? intdiv($h, 24).' hari' : $h.' jam';
    }

    private function dayMonth(Carbon $d): string
    {
        return $d->format('j').' '.$this->monthShort($d);
    }

    /** @return Collection<int, array{nama: string, wilayah: string, aktif: int}> */
    private function activeInspectors()
    {
        return DB::table('inspector_profiles AS ip')
            ->join('users AS u', 'u.id_user', '=', 'ip.id_user')
            ->where('ip.status_aktif', 1)
            ->get(['u.id_user', 'u.nama', 'ip.wilayah_tugas'])
            ->map(fn ($r) => [
                'nama' => (string) $r->nama,
                'wilayah' => (string) ($r->wilayah_tugas ?: '–'),
                'aktif' => DB::table('inspections')->where('users_id_user', $r->id_user)->whereIn('status', ['DITUGASKAN', 'DIPROSES'])->count(),
            ]);
    }

    // ------------------------------------------------------------------ strip ringkasan (contoh Lab, ditandai demo)

    /**
     * DDL tidak menyimpan tanggal pembuatan order, jadi perbandingan per periode tidak bisa dihitung.
     * Angka di bawah adalah contoh dari Lab dan ditampilkan dengan tanda "Data demo".
     *
     * @return array<string, mixed>
     */
    private function periods(): array
    {
        $m = fn (string $label, float $v, float $prev, string $unit, int $d, bool $lowerBetter = false) => compact('label', 'unit', 'd') + ['value' => $v, 'prev' => $prev, 'lowerBetter' => $lowerBetter];

        return [
            'Hari ini' => ['compare' => 'dibanding kemarin', 'metrics' => [
                $m('Order baru', 6, 4, 'order', 0), $m('Tonase selesai', 4.8, 6.1, 'ton', 1),
                $m('Nilai transaksi', 18.4, 15.2, 'juta Rp', 1), $m('Waktu keputusan', 31, 38, 'jam', 0, true),
            ]],
            '7 hari' => ['compare' => 'dibanding 7 hari sebelumnya', 'metrics' => [
                $m('Order baru', 34, 29, 'order', 0), $m('Tonase selesai', 38.6, 33.9, 'ton', 1),
                $m('Nilai transaksi', 126.7, 131.4, 'juta Rp', 1), $m('Waktu keputusan', 34, 33, 'jam', 0, true),
            ]],
            '30 hari' => ['compare' => 'dibanding 30 hari sebelumnya', 'metrics' => [
                $m('Order baru', 118, 92, 'order', 0), $m('Tonase selesai', 128.4, 101.2, 'ton', 1),
                $m('Nilai transaksi', 486.2, 412.9, 'juta Rp', 1), $m('Waktu keputusan', 36, 47, 'jam', 0, true),
            ]],
        ];
    }

    // ------------------------------------------------------------------ inspeksi per jam

    /**
     * Inspeksi pada hari ini; bila hari ini kosong, hari terakhir yang punya jadwal (judulnya memuat tanggal).
     *
     * @return array<string, mixed>
     */
    private function hourly(Carbon $now): array
    {
        $rows = Inspection::with(['purchaseOrder.seller', 'inspector'])->whereNotNull('jadwal')->orderBy('jadwal')->get()
            ->map(fn (Inspection $i) => ['i' => $i, 'at' => Carbon::parse($i->jadwal)->timezone('Asia/Jakarta')]);

        $date = $rows->contains(fn ($r) => $r['at']->isSameDay($now))
            ? $now->copy()->startOfDay()
            : ($rows->filter(fn ($r) => $r['at']->lte($now))->last()['at'] ?? $now)->copy()->startOfDay();
        $isToday = $date->isSameDay($now);
        $day = $rows->filter(fn ($r) => $r['at']->isSameDay($date));

        $hours = range(7, 16);
        $series = collect($hours)->map(fn (int $h) => ['h' => $h, 'done' => 0, 'run' => 0, 'wait' => 0, 'plan' => 0, 'fail' => 0])->keyBy('h')->all();
        foreach ($day as $r) {
            $h = min(16, max(7, (int) $r['at']->format('G')));
            $key = match ($r['i']->status) {
                'SELESAI' => 'done', 'DIPROSES' => 'run', default => 'plan',
            };
            $series[$h][$key]++;
        }

        $materials = DB::table('purchase_orders AS po')
            ->leftJoin('rfq_contracts AS r', 'r.id_rfq_contract', '=', 'po.rfq_contracts_id_rfq_contract')
            ->leftJoin('listings AS l', 'l.id_listings', '=', 'r.listings_id_listings')
            ->leftJoin('categories AS c', 'c.id_categories', '=', 'l.categories_id_categories')
            ->whereIn('po.id_purchase_order', $day->pluck('i.purchase_orders_id_purchase_order')->all())
            ->get(['po.id_purchase_order AS id', 'c.nama AS kategori', 'l.lokasi'])->keyBy('id');

        $next = $day->map(function ($r) use ($materials) {
            $i = $r['i'];
            $mat = $materials[$i->purchase_orders_id_purchase_order] ?? null;
            $state = $i->status === 'DIPROSES' ? 'run' : ($i->status === 'SELESAI' ? 'done' : 'plan');
            $class = $this->classOf($mat->kategori ?? null);

            return [
                'time' => $r['at']->format('H.i'),
                'state' => $state,
                'po' => 'PO-'.$i->purchase_orders_id_purchase_order,
                'material' => $mat->kategori ?? 'Material',
                'who' => $i->inspector->nama ?? 'Belum ada Inspector',
                'place' => $mat->lokasi ?? '–',
                'step' => match ($state) {
                    'run' => 'Sedang berjalan', 'done' => 'Selesai', default => 'Terjadwal'
                },
                'photo' => $this->photo($class),
            ];
        })->values()->all();

        $totals = array_reduce($series, fn ($a, $r) => [
            'done' => $a['done'] + $r['done'], 'wait' => $a['wait'] + $r['wait'], 'all' => $a['all'] + $r['done'] + $r['run'] + $r['wait'] + $r['plan'] + $r['fail'],
        ], ['done' => 0, 'wait' => 0, 'all' => 0]);

        return [
            'title' => $isToday ? 'Inspeksi hari ini' : 'Inspeksi '.$this->dayMonth($date),
            'isToday' => $isToday,
            'rows' => array_values($series),
            'next' => $next,
            'totals' => $totals,
            'nowHour' => $isToday ? (int) $now->format('G') + (int) $now->format('i') / 60 : null,
            'nowTime' => $now->format('H.i'),
            'max' => max(4, (int) ceil(max(array_map(fn ($r) => $r['done'] + $r['run'] + $r['wait'] + $r['plan'] + $r['fail'], $series)) / 2) * 2),
        ];
    }

    // ------------------------------------------------------------------ Sungai Order

    /**
     * @param  array<string, mixed>  $flow  DashboardService::orderFlow()
     * @return array<string, mixed>
     */
    private function river(array $flow): array
    {
        $ids = collect($flow['stages'])->concat($flow['exits'])->flatMap(fn ($s) => collect($s['orders'])->pluck('id'))->all();
        $history = DB::table('order_status_history')->whereIn('purchase_orders_id_purchase_order', $ids)->whereNotNull('waktu')
            ->orderBy('waktu')->get(['purchase_orders_id_purchase_order AS id', 'ke', 'waktu'])->groupBy('id');

        $stages = collect($flow['stages'])->map(function (array $s) use ($history) {
            $ages = collect($s['orders'])->pluck('age')->filter(fn ($a) => $a !== null);
            $orders = collect($s['orders'])->map(function (array $o) use ($history) {
                $class = $this->classOf($o['kategori']);
                $steps = [];
                $rows = $history[$o['id']] ?? collect();
                foreach ($rows as $n => $row) {
                    $start = Carbon::parse($row->waktu);
                    $end = isset($rows[$n + 1]) ? Carbon::parse($rows[$n + 1]->waktu) : null;
                    $steps[] = [
                        'label' => StatusMap::label('purchase_orders.status', $row->ke),
                        'hours' => $end ? max(1, (int) $start->diffInHours($end)) : null,
                        'current' => $end === null,
                    ];
                }

                return $o + ['material' => $o['kategori'] ?? 'Material', 'color' => $class, 'history' => $steps];
            })->all();

            return [
                'key' => $s['key'], 'group' => $s['group'], 'label' => $s['label'], 'short' => $s['short'],
                'count' => $s['count'], 'ton' => $s['kg'] / 1000, 'nilai' => $s['nilai'],
                'avg' => $ages->count() > 0 ? round($ages->avg(), 1) : null,
                'orders' => $orders,
            ];
        })->values();

        $done = $stages->firstWhere('key', 'SELESAI');
        $active = $stages->reject(fn ($s) => $s['key'] === 'SELESAI')->values();

        // Titik tertahan: tahap dengan umur rata-rata tertinggi, hanya bila nyata lebih lama dari tahap lain.
        $withAge = $active->filter(fn ($s) => $s['avg'] !== null && $s['count'] >= 2);
        $stuck = null;
        if ($withAge->count() >= 2) {
            $top = $withAge->sortByDesc('avg')->first();
            $others = $withAge->reject(fn ($s) => $s['key'] === $top['key'])->avg('avg');
            if ($others > 0 && $top['avg'] >= 1.5 * $others) {
                $stuck = ['key' => $top['key'], 'label' => $top['label'], 'count' => $top['count'], 'avg' => $top['avg'],
                    'over7' => collect($top['orders'])->where('age', '>', 7)->count(), 'ratio' => round($top['avg'] / $others, 1)];
            }
        }
        $stages = $stages->map(fn ($s) => $s + ['stuck' => $stuck && $stuck['key'] === $s['key']])->all();

        $exits = collect($flow['exits'])->map(fn (array $x) => [
            'key' => $x['key'], 'label' => $x['label'], 'from' => $x['origin'], 'count' => $x['count'],
            'tone' => self::EXIT_TONE[$x['key']] ?? 'meta', 'ton' => $x['kg'] / 1000,
            'orders' => collect($x['orders'])->map(fn ($o) => $o + ['material' => $o['kategori'] ?? 'Material', 'color' => $this->classOf($o['kategori']), 'history' => []])->all(),
        ])->all();

        $activeTotal = $active->filter(fn ($s) => in_array($s['key'], DashboardService::ACTIVE_ORDER_STATUSES, true))->sum('count');
        $busiest = $active->sortByDesc('count')->first();

        return [
            'stages' => $stages,
            'exits' => $exits,
            'done' => ['count' => $done['count'] ?? 0, 'ton' => $done['ton'] ?? 0],
            'active_total' => $activeTotal,
            'stuck' => $stuck,
            'busiest' => $busiest ? ['key' => $busiest['key'], 'label' => $busiest['label'], 'count' => $busiest['count']] : null,
            'selected' => $stuck['key'] ?? ($busiest['key'] ?? 'DRAFT'),
        ];
    }

    // ------------------------------------------------------------------ peta pengawasan (contoh Lab, ditandai demo)

    /**
     * DDL tidak menyimpan kota perusahaan (companies.lat/lng kosong) maupun temuan kepatuhan (compliance_notes kosong),
     * jadi peta memakai contoh Lab dan ditandai "Data demo".
     *
     * @return list<array<string, mixed>>
     */
    private function regions(): array
    {
        $r = fn (string $code, string $name, string $prov, int $c, int $row, int $orders, float $ton, int $insp, string $status, ?string $note = null, ?string $noteId = null) => [
            'code' => $code, 'name' => $name, 'prov' => $prov, 'c' => $c, 'r' => $row, 'orders' => $orders, 'ton' => $ton,
            'inspectors' => $insp, 'status' => $status, 'note' => $note, 'noteId' => $noteId,
        ];

        return [
            $r('SRG', 'Serang', 'Banten', 0, 1, 2, 6.1, 1, 'patuh'),
            $r('TGR', 'Tangerang', 'Banten', 1, 1, 5, 14.8, 2, 'temuan', 'Label kemasan cacahan plastik tidak mencantumkan kode resin', 'CMP-0031'),
            $r('JKT', 'Jakarta Timur', 'DKI Jakarta', 2, 0, 6, 12.2, 2, 'patuh'),
            $r('BKS', 'Bekasi', 'Jawa Barat', 3, 0, 7, 21.4, 2, 'patuh'),
            $r('KRW', 'Karawang', 'Jawa Barat', 4, 0, 4, 15.9, 1, 'peringatan', 'Dua pengiriman tanpa manifes digital dalam 30 hari', 'CMP-0028'),
            $r('BGR', 'Bogor', 'Jawa Barat', 2, 1, 3, 7.3, 1, 'patuh'),
            $r('CMH', 'Cimahi', 'Jawa Barat', 3, 1, 4, 9.8, 1, 'patuh'),
            $r('BDG', 'Bandung', 'Jawa Barat', 4, 1, 9, 33.8, 3, 'temuan', 'Kadar air ampas tahu di atas batas pada 2 inspeksi', 'CMP-0033'),
            $r('MJL', 'Majalaya', 'Jawa Barat', 4, 2, 3, 8.4, 1, 'patuh'),
            $r('TSM', 'Tasikmalaya', 'Jawa Barat', 5, 2, 0, 0, 0, 'sepi'),
            $r('CRB', 'Cirebon', 'Jawa Barat', 6, 1, 1, 2.6, 0, 'patuh'),
            $r('TGL', 'Tegal', 'Jawa Tengah', 7, 1, 0, 0, 0, 'sepi'),
            $r('SMG', 'Semarang', 'Jawa Tengah', 8, 0, 4, 11.7, 1, 'patuh'),
            $r('KDS', 'Kudus', 'Jawa Tengah', 9, 0, 2, 5.2, 0, 'patuh'),
            $r('SKA', 'Surakarta', 'Jawa Tengah', 8, 1, 1, 1.9, 0, 'patuh'),
            $r('GSK', 'Gresik', 'Jawa Timur', 10, 0, 0, 0, 0, 'sepi'),
            $r('SBY', 'Surabaya', 'Jawa Timur', 11, 0, 3, 8.9, 1, 'patuh'),
            $r('SDA', 'Sidoarjo', 'Jawa Timur', 11, 1, 2, 4.4, 0, 'temuan', 'Laporan mutu serbuk kayu belum diunggah lewat 7 hari', 'CMP-0035'),
            $r('MLG', 'Malang', 'Jawa Timur', 10, 2, 1, 2.2, 0, 'patuh'),
        ];
    }

    // ------------------------------------------------------------------ umur tagihan

    /**
     * Invoice yang belum dibayar, dikelompokkan dari jatuh tempo (DDL tidak menyimpan tanggal terbit).
     *
     * @return array<string, mixed>
     */
    private function aging(Carbon $now): array
    {
        $today = $now->copy()->startOfDay();
        $rows = DB::table('invoices AS i')
            ->leftJoin('purchase_orders AS po', 'po.id_purchase_order', '=', 'i.purchase_orders_id_purchase_order')
            ->leftJoin('companies AS b', 'b.id_company', '=', 'po.companies_id_buyer')
            ->where('i.status', 'TERBIT')
            ->get(['i.nomor', 'i.total', 'i.jatuh_tempo', 'b.nama AS buyer'])
            ->map(fn ($r) => [
                'id' => $r->nomor, 'buyer' => $r->buyer ?? '–', 'amount' => (float) $r->total,
                'days' => $r->jatuh_tempo ? (int) Carbon::parse($r->jatuh_tempo)->startOfDay()->diffInDays($today, false) : 0,
            ]);

        $buckets = [
            ['label' => 'Belum jatuh tempo', 'tone' => 'var(--c-done)', 'test' => fn ($d) => $d <= 0],
            ['label' => 'Lewat 1–30 hari', 'tone' => 'var(--c-plan)', 'test' => fn ($d) => $d >= 1 && $d <= 30],
            ['label' => 'Lewat 31–60 hari', 'tone' => 'var(--c-wait)', 'test' => fn ($d) => $d >= 31 && $d <= 60],
            ['label' => 'Lewat 60 hari', 'tone' => 'var(--c-fail)', 'test' => fn ($d) => $d > 60],
        ];

        return [
            'buckets' => array_map(fn ($b) => [
                'label' => $b['label'], 'tone' => $b['tone'],
                'count' => $rows->filter(fn ($r) => $b['test']($r['days']))->count(),
                'amount' => (float) $rows->filter(fn ($r) => $b['test']($r['days']))->sum('amount'),
            ], $buckets),
            'oldest' => $rows->sortByDesc('days')->take(3)->values()->all(),
            'count' => $rows->count(),
            'total' => (float) $rows->sum('amount'),
        ];
    }

    // ------------------------------------------------------------------ aktivitas

    /** @return list<array<string, mixed>> */
    private function activity(Carbon $now): array
    {
        return DB::table('order_status_history AS h')
            ->leftJoin('users AS u', 'u.id_user', '=', 'h.users_id_user')
            ->whereNotNull('h.waktu')
            ->orderByDesc('h.waktu')->limit(6)
            ->get(['h.purchase_orders_id_purchase_order AS po', 'h.dari', 'h.ke', 'h.waktu', 'u.nama'])
            ->map(function ($h) {
                $who = $h->nama ?: 'Sistem';
                $at = Carbon::parse($h->waktu)->timezone('Asia/Jakarta');
                $tone = match (true) {
                    in_array($h->ke, ['DITOLAK', 'GAGAL_INSPEKSI', 'SENGKETA'], true) => 'danger',
                    in_array($h->ke, ['SELESAI', 'DIBAYAR', 'DITERIMA'], true) => 'primary',
                    $h->ke === 'INSPEKSI' => 'info',
                    default => 'meta',
                };

                return [
                    'who' => $who,
                    'what' => 'memindahkan PO-'.$h->po.' ke '.StatusMap::label('purchase_orders.status', $h->ke),
                    'time' => $at->format('j M, H:i'),
                    'tone' => $tone,
                    'initials' => collect(preg_split('/\s+/', trim($who)))->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode(''),
                ];
            })->all();
    }

    // ------------------------------------------------------------------ beban Inspector

    /**
     * Tugas selesai dari seluruh tugas yang diberikan per Inspector (DDL tidak menyimpan kapasitas mingguan).
     *
     * @return array<string, mixed>
     */
    private function inspectorLoad(): array
    {
        $rows = DB::table('inspector_profiles AS ip')
            ->join('users AS u', 'u.id_user', '=', 'ip.id_user')
            ->where('ip.status_aktif', 1)
            ->get(['u.id_user', 'u.nama', 'ip.wilayah_tugas'])
            ->map(function ($r) {
                $all = DB::table('inspections')->where('users_id_user', $r->id_user)->selectRaw("COUNT(*) AS total, SUM(status = 'SELESAI') AS done, SUM(status IN ('DITUGASKAN','DIPROSES')) AS aktif")->first();

                return [
                    'name' => $r->nama, 'area' => $r->wilayah_tugas ?: '–', 'done' => (int) ($all->done ?? 0), 'cap' => max(1, (int) ($all->total ?? 0)),
                    'aktif' => (int) ($all->aktif ?? 0), 'total' => (int) ($all->total ?? 0),
                    'initials' => collect(preg_split('/\s+/', trim($r->nama)))->reject(fn ($w) => str_ends_with($w, '.'))->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode(''),
                ];
            })->sortByDesc('total')->values();

        $idle = $rows->where('aktif', 0)->count();

        return [
            'rows' => $rows->all(),
            'note' => $rows->count() === 0 ? null : ($idle > 0 ? $idle.' dari '.$rows->count().' Inspector aktif tidak punya tugas berjalan.' : 'Semua Inspector aktif punya tugas berjalan.'),
        ];
    }

    // ------------------------------------------------------------------ tonase per kategori & dampak

    /** @return array<string, mixed> */
    private function materials(array $m): array
    {
        return [
            'total' => $m['total_ton'],
            'rows' => collect($m['rows'])->map(fn (array $r) => [
                'key' => $r['key'], 'name' => $r['nama'], 'ton' => $r['ton'], 'share' => $r['share'], 'color' => $r['color'] ?? 'lainnya',
                'photo' => $r['photo'] ?? $this->photo(null),
            ])->all(),
        ];
    }

    /** @return list<array<string, mixed>> */
    private function impact(array $m): array
    {
        $hemat = ($m['hemat_seller'] + $m['hemat_buyer']) / 1_000_000;

        return [
            ['label' => 'Terdaur ulang', 'value' => round($m['tonase'], 1), 'decimals' => 1, 'unit' => 'ton', 'note' => Format::number($m['orders_selesai']).' order selesai'],
            ['label' => 'Penghematan', 'value' => round($hemat, 1), 'decimals' => 1, 'unit' => 'juta Rp', 'note' => 'Seller dan Buyer'],
            ['label' => 'Emisi ditekan', 'value' => round($m['co2e_kg'] / 1000, 1), 'decimals' => 1, 'unit' => 'ton CO₂e', 'note' => 'setelah angkutan'],
            ['label' => 'Inspector aktif', 'value' => $m['inspector_aktif'], 'decimals' => 0, 'unit' => 'dari '.$m['inspector_total'], 'note' => max(0, $m['inspector_total'] - $m['inspector_aktif']).' tidak aktif'],
        ];
    }
}

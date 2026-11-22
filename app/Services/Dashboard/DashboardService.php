<?php

namespace App\Services\Dashboard;

use App\Enums\InspectorStatus;
use App\Models\Company;
use App\Models\DemandRequest;
use App\Models\Dispute;
use App\Models\ImpactLog;
use App\Models\Inspection;
use App\Models\InspectorProfile;
use App\Models\Invoice;
use App\Models\Listing;
use App\Models\Payment;
use App\Models\PurchaseOrder;
use App\Models\RfqContract;
use App\Support\Format;
use App\Support\MaterialPhoto;
use App\Support\StatusMap;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

/**
 * FND-04 (SYM-28): data dasbor per peran, struktur FND v8 (docs/design/DESIGN.md, amandemen 19 Nov 2026).
 * Semua angka dihitung dari data transaksi saat diminta; tidak ada angka yang dikarang. Elemen yang datanya
 * belum ada di DDL (target DLH, SLA, perbandingan bulanan, tanggal pembuatan) sengaja tidak ditampilkan.
 */
class DashboardService
{
    /** Status order yang masih berjalan (bukan akhir). */
    public const ACTIVE_ORDER_STATUSES = [
        'MENUNGGU_KONFIRMASI', 'DIKONFIRMASI', 'INSPEKSI', 'LOLOS_INSPEKSI',
        'INVOICED', 'DIBAYAR', 'DALAM_PENGIRIMAN', 'DITERIMA', 'SENGKETA',
    ];

    /** Alur normal 10 langkah (03_STATUS_MAP) dalam tiga kelompok. */
    public const FLOW_GROUPS = [
        'Kesepakatan' => ['DRAFT', 'MENUNGGU_KONFIRMASI', 'DIKONFIRMASI'],
        'Mutu & pembayaran' => ['INSPEKSI', 'LOLOS_INSPEKSI', 'INVOICED', 'DIBAYAR'],
        'Pengiriman' => ['DALAM_PENGIRIMAN', 'DITERIMA', 'SELESAI'],
    ];

    /** Singkatan resmi label tahap untuk Sungai Order (panduan dashboard §7.1). Nama lengkap tetap dari StatusMap. */
    public const FLOW_SHORT_LABELS = [
        'MENUNGGU_KONFIRMASI' => 'Menunggu Konf.',
        'LOLOS_INSPEKSI' => 'Lolos Insp.',
        'INVOICED' => 'Menunggu Bayar',
        'DALAM_PENGIRIMAN' => 'Dalam Kirim',
    ];

    /** Jalur keluar dan tahap asalnya (panduan dashboard §6.1). */
    public const FLOW_EXITS = [
        'DITOLAK' => 'MENUNGGU_KONFIRMASI',
        'DIBATALKAN' => 'DIKONFIRMASI',
        'GAGAL_INSPEKSI' => 'INSPEKSI',
        'SENGKETA' => 'DITERIMA',
    ];

    /** Paling banyak baris yang dimuat per daftar; hitungan di judul tetap total sebenarnya. */
    private const LIST_LIMIT = 25;

    /**
     * Dasbor Admin (Senja di Sawah): Sungai Order → Perlu tindakan Anda → Sedang berjalan → Gambaran total tercatat.
     * Satu sumber per fakta: hitungan tahap, baris panel, batang grafik, dan antrean dihitung di sini, sekali.
     *
     * @return array<string, mixed>
     */
    public function admin(): array
    {
        $today = Carbon::today();

        $inspectors = DB::table('inspector_profiles')->selectRaw(
            'COUNT(*) AS total, SUM(CASE WHEN status_aktif = 1 AND (berlaku_sampai IS NULL OR berlaku_sampai >= ?) THEN 1 ELSE 0 END) AS aktif',
            [$today->toDateString()]
        )->first();

        $impact = ImpactLog::query()->selectRaw(
            'COALESCE(SUM(tonase_ton), 0) AS tonase, COALESCE(SUM(hemat_seller_rp), 0) AS seller, COALESCE(SUM(hemat_buyer_rp), 0) AS buyer, COALESCE(SUM(co2e_net_kg), 0) AS co2e, COALESCE(SUM(co2e_dihindari_kg), 0) AS co2e_avoided, COALESCE(SUM(co2e_angkutan_kg), 0) AS co2e_transport, COUNT(*) AS orders'
        )->first();

        // Tonase order yang masih berjalan di alur (bukan Draf/Selesai/jalur keluar akhir): pembanding untuk tonase selesai.
        $activeKg = (float) PurchaseOrder::whereIn('status', self::ACTIVE_ORDER_STATUSES)->sum('volume_kg');

        return [
            'flow' => $this->orderFlow($today),
            'queue' => $this->adminQueue($today),
            'running' => $this->adminRunning(),
            'metrics' => [
                'tonase' => (float) ($impact->tonase ?? 0),
                'orders_selesai' => (int) ($impact->orders ?? 0),
                'hemat_seller' => (float) ($impact->seller ?? 0),
                'hemat_buyer' => (float) ($impact->buyer ?? 0),
                'co2e_kg' => (float) ($impact->co2e ?? 0),
                'co2e_dihindari_kg' => (float) ($impact->co2e_avoided ?? 0),
                'co2e_angkutan_kg' => (float) ($impact->co2e_transport ?? 0),
                'aktif_ton' => $activeKg / 1000,
                'inspector_aktif' => (int) ($inspectors->aktif ?? 0),
                'inspector_total' => (int) ($inspectors->total ?? 0),
            ],
            'materials' => $this->materialComposition(),
        ];
    }

    /**
     * Sungai Order: 10 tahap STATUS_MAP + 4 jalur keluar. Hitungan, tonase, dan nilai adalah total sebenarnya per status;
     * baris order dimuat paling banyak LIST_LIMIT per tahap. Umur di tahap hanya diisi bila order_status_history
     * mencatat waktu masuk ke status saat ini (tanpa catatan, umur tidak ditebak).
     * Dengan $partyColumn ('companies_id_seller' / 'companies_id_buyer') dan $companyId, hanya order milik perusahaan itu.
     *
     * @return array<string, mixed>
     */
    private function orderFlow(Carbon $today, ?string $partyColumn = null, ?int $companyId = null): array
    {
        $scope = fn ($q, string $prefix = '') => $partyColumn ? $q->where($prefix.$partyColumn, $companyId) : $q;

        $totals = $scope(DB::table('purchase_orders'))
            ->selectRaw('status, COUNT(*) AS jumlah, COALESCE(SUM(volume_kg), 0) AS kg, COALESCE(SUM(volume_kg * harga_per_kg), 0) AS nilai')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        $ranked = $scope(DB::table('purchase_orders'))->selectRaw(
            'id_purchase_order, ROW_NUMBER() OVER (PARTITION BY status ORDER BY id_purchase_order) AS urutan'
        );

        $rows = DB::table('purchase_orders AS po')
            ->joinSub($ranked, 'rk', 'rk.id_purchase_order', '=', 'po.id_purchase_order')
            ->leftJoin('companies AS s', 's.id_company', '=', 'po.companies_id_seller')
            ->leftJoin('companies AS b', 'b.id_company', '=', 'po.companies_id_buyer')
            ->leftJoin('rfq_contracts AS r', 'r.id_rfq_contract', '=', 'po.rfq_contracts_id_rfq_contract')
            ->leftJoin('listings AS l', 'l.id_listings', '=', 'r.listings_id_listings')
            ->leftJoin('categories AS c', 'c.id_categories', '=', 'l.categories_id_categories')
            ->where('rk.urutan', '<=', self::LIST_LIMIT)
            ->orderBy('po.id_purchase_order')
            ->get(['po.id_purchase_order AS id', 'po.status', 'po.volume_kg', 'po.harga_per_kg', 's.nama AS seller', 'b.nama AS buyer', 'c.nama AS kategori']);

        // Waktu masuk ke status saat ini, dari riwayat status (satu-satunya sumber waktu untuk order).
        $entered = DB::table('order_status_history')
            ->whereIn('purchase_orders_id_purchase_order', $rows->pluck('id')->all())
            ->whereNotNull('waktu')
            ->groupBy('purchase_orders_id_purchase_order', 'ke')
            ->selectRaw('purchase_orders_id_purchase_order AS id, ke, MAX(waktu) AS waktu')
            ->get()
            ->groupBy('id');

        $byStatus = $rows->groupBy('status');

        $build = function (string $status) use ($totals, $byStatus, $entered, $today): array {
            $total = $totals[$status] ?? null;
            $orders = ($byStatus[$status] ?? collect())->map(function ($row) use ($status, $entered, $today) {
                $since = ($entered[$row->id] ?? collect())->firstWhere('ke', $status)?->waktu;

                return [
                    'id' => (int) $row->id,
                    'code' => 'PO-'.$row->id,
                    'seller' => $row->seller ?? '–',
                    'buyer' => $row->buyer ?? '–',
                    'kategori' => $row->kategori,
                    'kg' => (float) $row->volume_kg,
                    'nilai' => (float) $row->volume_kg * (float) $row->harga_per_kg,
                    'age' => $since ? (int) Carbon::parse($since)->startOfDay()->diffInDays($today) : null,
                ];
            })->sortByDesc(fn (array $o) => $o['age'] ?? -1)->values()->all();

            return [
                'key' => $status,
                'label' => StatusMap::label('purchase_orders.status', $status),
                'short' => self::FLOW_SHORT_LABELS[$status] ?? StatusMap::label('purchase_orders.status', $status),
                'count' => (int) ($total->jumlah ?? 0),
                'kg' => (float) ($total->kg ?? 0),
                'nilai' => (float) ($total->nilai ?? 0),
                'orders' => $orders,
            ];
        };

        $stages = [];
        foreach (self::FLOW_GROUPS as $group => $statuses) {
            foreach ($statuses as $status) {
                $stages[] = ['group' => $group] + $build($status);
            }
        }

        $exits = [];
        foreach (self::FLOW_EXITS as $status => $origin) {
            $exits[] = ['origin' => $origin] + $build($status);
        }

        return [
            'groups' => array_keys(self::FLOW_GROUPS),
            'stages' => $stages,
            'exits' => $exits,
            'list_limit' => self::LIST_LIMIT,
        ];
    }

    /**
     * Antrean keputusan Admin, dikelompokkan per jenis keputusan. Hitungan kelompok adalah total sebenarnya; item yang
     * halamannya belum ada tidak diberi aksi (url null) dan kelompoknya ditandai informasional.
     * "Mendesak": sengketa terbuka dan sertifikat yang berakhir ≤ 7 hari (aturan yang sudah dipakai sejak FND-04).
     *
     * @return array{groups: list<array<string, mixed>>}
     */
    private function adminQueue(Carbon $today): array
    {
        $groups = [];

        // 1. Sengketa terbuka.
        $disputes = Dispute::with('company')->where('status', 'DIBUKA');
        $groups[] = [
            'key' => 'sengketa',
            'title' => 'Putuskan sengketa',
            'count' => (clone $disputes)->count(),
            'urgent' => (clone $disputes)->count(),
            'reason' => ':n sengketa menunggu putusan',
            'action' => null,
            'pending_page' => 'Halaman putusan sengketa segera hadir',
            'items' => (clone $disputes)->orderBy('id_dispute')->limit(self::LIST_LIMIT)->get()->map(fn (Dispute $d) => [
                'id' => 'sengketa-'.$d->id_dispute,
                'po' => (int) $d->purchase_orders_id_purchase_order,
                'title' => 'Sengketa #'.$d->id_dispute.' · PO-'.$d->purchase_orders_id_purchase_order,
                'meta' => StatusMap::label('disputes.jenis', $d->jenis).' · pelapor '.($d->company->nama ?? '–'),
                'party' => $d->company->nama ?? 'Sengketa',
                'person' => false,
                'urgent' => true,
                'date' => null,
                'url' => null,
            ])->all(),
        ];

        // 2. Sertifikat Inspector yang berakhir ≤ 30 hari.
        $certs = DB::table('inspector_profiles')
            ->join('users', 'users.id_user', '=', 'inspector_profiles.id_user')
            ->where('inspector_profiles.status_aktif', 1)
            ->whereBetween('inspector_profiles.berlaku_sampai', [$today->toDateString(), $today->copy()->addDays(30)->toDateString()]);
        $renew = Route::has('admin.inspectors.renew.form');
        $groups[] = [
            'key' => 'sertifikat',
            'title' => 'Perpanjang sertifikat Inspector',
            'count' => (clone $certs)->count(),
            'urgent' => (clone $certs)->where('inspector_profiles.berlaku_sampai', '<=', $today->copy()->addDays(7)->toDateString())->count(),
            'reason' => ':n sertifikat berakhir ≤ 30 hari',
            'action' => $renew ? 'Perpanjang' : null,
            'pending_page' => $renew ? null : 'Halaman perpanjangan segera hadir',
            'items' => (clone $certs)->orderBy('inspector_profiles.berlaku_sampai')->limit(self::LIST_LIMIT)
                ->get(['users.nama', 'inspector_profiles.id_inspector', 'inspector_profiles.berlaku_sampai', 'inspector_profiles.no_sertifikat'])
                ->map(function ($cert) use ($today, $renew) {
                    $until = Carbon::parse($cert->berlaku_sampai);

                    return [
                        'id' => 'sertifikat-'.$cert->id_inspector,
                        'po' => null,
                        'title' => $cert->nama,
                        'meta' => 'Sertifikat '.($cert->no_sertifikat ?: 'tanpa nomor').' · berakhir '.Format::date($until),
                        'party' => $cert->nama,
                        'person' => true,
                        'urgent' => $until->lte($today->copy()->addDays(7)),
                        'date' => $this->dateBlock($until, $today),
                        'date_caption' => 'berakhir',
                        'days_left' => (int) $today->copy()->startOfDay()->diffInDays($until->copy()->startOfDay()),
                        'url' => $renew ? route('admin.inspectors.renew.form', $cert->id_inspector) : null,
                    ];
                })->all(),
        ];

        // 3. Order dikonfirmasi yang belum punya Inspector.
        $unassigned = PurchaseOrder::with(['seller', 'buyer'])
            ->where('status', 'DIKONFIRMASI')
            ->whereDoesntHave('inspections', fn ($q) => $q->whereIn('status', ['DITUGASKAN', 'DIPROSES', 'SELESAI']));
        $groups[] = [
            'key' => 'penugasan',
            'title' => 'Tetapkan Inspector',
            'count' => (clone $unassigned)->count(),
            'urgent' => 0,
            'reason' => ':n order dikonfirmasi belum punya Inspector',
            'action' => null,
            'pending_page' => 'Halaman penugasan inspeksi segera hadir',
            'items' => (clone $unassigned)->orderBy('id_purchase_order')->limit(self::LIST_LIMIT)->get()->map(fn (PurchaseOrder $o) => [
                'id' => 'penugasan-'.$o->id_purchase_order,
                'po' => (int) $o->id_purchase_order,
                'title' => 'PO-'.$o->id_purchase_order,
                'meta' => ($o->seller->nama ?? '–').' → '.($o->buyer->nama ?? '–').' · '.Format::kg($o->volume_kg).' · '.Format::rupiahShort((float) $o->volume_kg * (float) $o->harga_per_kg),
                'party' => $o->seller->nama ?? 'PO',
                'person' => false,
                'urgent' => false,
                'date' => null,
                'url' => null,
            ])->all(),
        ];

        // 4. Perusahaan menunggu verifikasi.
        $pending = Company::where('status_verifikasi', 'PENDING');
        $review = Route::has('admin.companies.show');
        $groups[] = [
            'key' => 'verifikasi',
            'title' => 'Verifikasi mitra',
            'count' => (clone $pending)->count(),
            'urgent' => 0,
            'reason' => ':n perusahaan menunggu verifikasi',
            'action' => $review ? 'Tinjau' : null,
            'pending_page' => $review ? null : 'Halaman verifikasi segera hadir',
            'items' => (clone $pending)->orderBy('id_company')->limit(self::LIST_LIMIT)->get()->map(fn (Company $c) => [
                'id' => 'verifikasi-'.$c->id_company,
                'po' => null,
                'title' => $c->nama,
                'meta' => ucfirst(strtolower((string) $c->tipe)).' · NIB '.($c->nib ?? '–'),
                'party' => $c->nama,
                'person' => false,
                'urgent' => false,
                'date' => null,
                'url' => $review ? route('admin.companies.show', $c->id_company) : null,
            ])->all(),
        ];

        return ['groups' => array_values(array_filter($groups, fn (array $g) => $g['count'] > 0))];
    }

    /**
     * Zona 2 Admin: inspeksi dan pengiriman yang sedang berjalan, dengan jadwal sebagai jangkar waktu.
     *
     * @return array{count: int, items: list<array<string, mixed>>}
     */
    private function adminRunning(): array
    {
        $today = Carbon::now('Asia/Jakarta')->startOfDay();

        $inspections = Inspection::with(['purchaseOrder.seller', 'purchaseOrder.buyer', 'inspector'])
            ->whereIn('status', ['DITUGASKAN', 'DIPROSES']);
        $shipments = DB::table('shipments_manifests AS sm')
            ->leftJoin('purchase_orders AS po', 'po.id_purchase_order', '=', 'sm.purchase_orders_id_purchase_order')
            ->leftJoin('companies AS s', 's.id_company', '=', 'po.companies_id_seller')
            ->leftJoin('companies AS b', 'b.id_company', '=', 'po.companies_id_buyer')
            ->where('sm.status', 'DALAM_PERJALANAN');

        $count = (clone $inspections)->count() + (clone $shipments)->count();
        $materials = $this->orderMaterials((clone $inspections)->pluck('purchase_orders_id_purchase_order')->all());

        $items = (clone $inspections)->orderByRaw('jadwal IS NULL, jadwal')->limit(self::LIST_LIMIT)->get()
            ->map(function (Inspection $i) use ($materials, $today) {
                $po = $i->purchaseOrder;
                $material = $materials[$i->purchase_orders_id_purchase_order] ?? null;
                $jadwal = $i->jadwal ? Carbon::parse($i->jadwal)->timezone('Asia/Jakarta') : null;

                return [
                    'id' => 'inspeksi-'.$i->id_inspection,
                    'sort' => $jadwal->timestamp ?? PHP_INT_MAX,
                    'kind' => 'Inspeksi',
                    'title' => 'Inspeksi PO-'.$i->purchase_orders_id_purchase_order.($material['nama'] ?? null ? ' · '.$material['nama'] : ''),
                    'meta' => collect([
                        $i->inspector->nama ?? null,
                        StatusMap::label('inspections.mode_verifikasi', $i->mode_verifikasi),
                        $material['lokasi'] ?? null,
                    ])->filter()->implode(' · '),
                    'status' => StatusMap::label('inspections.status', $i->status),
                    'step' => $this->flowStep($po?->status),
                    'step_label' => $po ? StatusMap::label('purchase_orders.status', $po->status) : null,
                    'time' => $jadwal?->format('H.i'),
                    'date' => $jadwal ? $this->dateBlock($jadwal, $today) : null,
                ];
            });

        $items = $items->concat((clone $shipments)->orderByRaw('sm.jadwal IS NULL, sm.jadwal')->limit(self::LIST_LIMIT)
            ->get(['sm.id_shipment_manifest', 'sm.jadwal', 'sm.rute', 'sm.armada', 'sm.berat_dikirim_kg', 'po.id_purchase_order', 'po.status AS po_status', 's.nama AS seller', 'b.nama AS buyer'])
            ->map(function ($sm) use ($today) {
                $jadwal = $sm->jadwal ? Carbon::parse($sm->jadwal)->timezone('Asia/Jakarta') : null;

                return [
                    'id' => 'kirim-'.$sm->id_shipment_manifest,
                    'sort' => $jadwal->timestamp ?? PHP_INT_MAX,
                    'kind' => 'Pengiriman',
                    'title' => 'Pengiriman PO-'.$sm->id_purchase_order,
                    'meta' => collect([($sm->seller ?? '–').' → '.($sm->buyer ?? '–'), $sm->rute, Format::kg($sm->berat_dikirim_kg)])->filter()->implode(' · '),
                    'status' => 'Dalam Perjalanan', // 03_STATUS_MAP: shipments_manifests.status DALAM_PERJALANAN
                    'step' => $this->flowStep($sm->po_status),
                    'step_label' => $sm->po_status ? StatusMap::label('purchase_orders.status', $sm->po_status) : null,
                    'time' => $jadwal?->format('H.i'),
                    'date' => $jadwal ? $this->dateBlock($jadwal, $today) : null,
                ];
            }));

        // Jadwal 7 hari (hari ini s.d. +6, WIB) dari SEMUA proses berjalan, bukan hanya baris yang dimuat.
        $dates = (clone $inspections)->whereNotNull('jadwal')->pluck('jadwal')
            ->concat((clone $shipments)->whereNotNull('sm.jadwal')->pluck('sm.jadwal'))
            ->map(fn ($j) => Carbon::parse($j)->timezone('Asia/Jakarta')->toDateString())
            ->countBy();
        $week = $this->weekStrip($today, $dates->all());

        return [
            'count' => $count,
            'week' => $week,
            'items' => $items->sortBy('sort')->values()->map(fn (array $i) => collect($i)->except('sort')->all())->all(),
        ];
    }

    /** Posisi status order di alur normal 10 langkah (1–10); null untuk jalur keluar atau status tak dikenal. */
    private function flowStep(?string $status): ?int
    {
        $index = array_search($status, array_merge(...array_values(self::FLOW_GROUPS)), true);

        return $index === false ? null : $index + 1;
    }

    /** Lima kelas material dengan warna tetap (panduan dashboard §3.2), urutan legenda bila tonasenya sama. */
    public const MATERIAL_CLASSES = ['plastik' => 'Plastik', 'pangan' => 'Pangan', 'tekstil' => 'Tekstil', 'kayu' => 'Kayu', 'kertas' => 'Kertas'];

    /** Kata kunci MaterialPhoto per kelas (foto material asli di public/images/material). */
    private const CLASS_PHOTO_QUERY = ['plastik' => 'plastik', 'pangan' => 'ampas tahu', 'tekstil' => 'kain', 'kayu' => 'serbuk kayu', 'kertas' => 'kardus'];

    /**
     * Komposisi material dari impact_logs (order selesai), per kelas material berwarna tetap. DDL tidak punya tanggal
     * di impact_logs, jadi periodenya "Total tercatat". Persen dihitung terhadap total seluruh set; kategori di luar
     * lima kelas digabung menjadi "Lainnya" (nada netral) agar satu warna selalu berarti satu kelas.
     *
     * @return array<string, mixed>
     */
    private function materialComposition(): array
    {
        $orders = DB::table('impact_logs AS il')
            ->join('purchase_orders AS po', 'po.id_purchase_order', '=', 'il.purchase_orders_id_purchase_order')
            ->leftJoin('rfq_contracts AS r', 'r.id_rfq_contract', '=', 'po.rfq_contracts_id_rfq_contract')
            ->leftJoin('listings AS l', 'l.id_listings', '=', 'r.listings_id_listings')
            ->leftJoin('categories AS c', 'c.id_categories', '=', 'l.categories_id_categories')
            ->leftJoin('companies AS s', 's.id_company', '=', 'po.companies_id_seller')
            ->leftJoin('companies AS b', 'b.id_company', '=', 'po.companies_id_buyer')
            ->orderByDesc('il.tonase_ton')
            ->get(['po.id_purchase_order AS id', 'c.nama AS kategori', 'il.tonase_ton AS ton', 'po.volume_kg', 'po.harga_per_kg', 's.nama AS seller', 'b.nama AS buyer'])
            ->map(fn ($o) => [
                'class' => self::categoryColor($o->kategori) ?? 'lainnya',
                'id' => (int) $o->id,
                'code' => 'PO-'.$o->id,
                'kategori' => $o->kategori ?? 'Tanpa kategori',
                'seller' => $o->seller ?? '–',
                'buyer' => $o->buyer ?? '–',
                'ton' => (float) $o->ton,
                'nilai' => (float) $o->volume_kg * (float) $o->harga_per_kg,
            ]);

        $totalTon = (float) $orders->sum('ton');
        $classes = array_keys(self::MATERIAL_CLASSES);

        $rows = $orders->groupBy('class')->map(fn ($list, $class) => [
            'key' => $class,
            'nama' => self::MATERIAL_CLASSES[$class] ?? 'Lainnya',
            'color' => $class === 'lainnya' ? null : $class,
            'photo' => $class === 'lainnya' ? null : MaterialPhoto::forCategory(self::CLASS_PHOTO_QUERY[$class]),
            'kategori' => $list->pluck('kategori')->unique()->values()->all(),
            'ton' => (float) $list->sum('ton'),
            'nilai' => (float) $list->sum('nilai'),
            'count' => $list->count(),
            'share' => $totalTon > 0 ? (float) $list->sum('ton') / $totalTon : 0.0,
            'orders' => $list->take(self::LIST_LIMIT)->map(fn (array $o) => collect($o)->except('class')->all())->values()->all(),
        ])->sortBy([
            fn ($a, $b) => ($a['key'] === 'lainnya') <=> ($b['key'] === 'lainnya'),
            fn ($a, $b) => $b['ton'] <=> $a['ton'],
            fn ($a, $b) => array_search($a['key'], $classes, true) <=> array_search($b['key'], $classes, true),
        ])->values()->all();

        return [
            'total_ton' => $totalTon,
            'total_nilai' => (float) $orders->sum('nilai'),
            'rows' => $rows,
            'list_limit' => self::LIST_LIMIT,
        ];
    }

    /**
     * Token warna kategori (panduan dashboard §3.2) dari nama kategori. Kategori di luar lima kelas: null (nada netral).
     */
    public static function categoryColor(?string $name): ?string
    {
        $name = ' '.strtolower((string) $name).' ';
        $map = [
            'plastik' => ['plastik', 'polimer', 'pet', 'hdpe'],
            'pangan' => ['pangan', 'organik', 'ampas', 'tahu', 'okara', 'makanan'],
            'tekstil' => ['tekstil', 'kain', 'perca', 'garmen'],
            'kayu' => ['kayu', 'serbuk', 'gergaji'],
            'kertas' => ['kertas', 'kardus', 'karton'],
        ];

        foreach ($map as $token => $words) {
            foreach ($words as $word) {
                if (preg_match('/\b'.preg_quote($word, '/').'\b/', $name)) {
                    return $token;
                }
            }
        }

        return null;
    }

    /**
     * Blok tanggal: angka hari, bulan pendek, dan apakah sudah lewat.
     *
     * @return array{day: string, month: string, iso: string, overdue: bool, label: string}
     */
    private function dateBlock(Carbon $date, Carbon $today): array
    {
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

        return [
            'day' => $date->format('j'),
            'month' => $months[$date->month - 1].($date->year !== $today->year ? ' '.$date->format('Y') : ''),
            'iso' => $date->toDateString(),
            'overdue' => $date->toDateString() < $today->toDateString(),
            'label' => Format::date($date),
        ];
    }

    /**
     * Dasbor Seller (Senja di Sawah): Perlu tindakan Anda → Sedang berjalan → Gambaran total tercatat.
     * Semua angka dari order, invoice, pembayaran, RFQ, dan listing milik perusahaan Seller; satu fakta = satu sumber.
     *
     * @return array<string, mixed>
     */
    public function seller(int $companyId): array
    {
        $today = Carbon::now('Asia/Jakarta')->startOfDay();
        $flow = $this->orderFlow($today, 'companies_id_seller', $companyId);
        $byKey = collect([...$flow['stages'], ...$flow['exits']])->keyBy('key');

        $activeStatuses = array_values(array_diff(self::ACTIVE_ORDER_STATUSES, ['MENUNGGU_KONFIRMASI']));
        $sum = fn (array $keys, string $field) => (float) collect($keys)->sum(fn ($k) => $byKey[$k][$field] ?? 0);

        $invoices = Invoice::whereHas('purchaseOrder', fn ($q) => $q->where('companies_id_seller', $companyId));
        $unpaid = (float) (clone $invoices)->where('status', 'TERBIT')->sum('total');
        $paid = (float) (clone $invoices)->where('status', 'DIBAYAR')->sum('total');
        $overdue = (clone $invoices)->where('status', 'TERBIT')->whereNotNull('jatuh_tempo')->where('jatuh_tempo', '<', $today->toDateString())->count();

        $impactTon = (float) ImpactLog::whereHas('purchaseOrder', fn ($q) => $q->where('companies_id_seller', $companyId))->sum('tonase_ton');

        $published = Listing::with('category')->where('companies_id_company', $companyId)->where('status', 'PUBLISHED');
        $stokKg = (float) (clone $published)->sum('stok_kg');
        $volumeKg = (float) (clone $published)->sum('volume_kg');

        return [
            'flow' => $flow,
            'queue' => $this->sellerQueue($companyId, $today, $byKey['MENUNGGU_KONFIRMASI']),
            'running' => $this->orderRunning(
                'companies_id_seller', $companyId, $today,
                array_values(array_diff(self::ACTIVE_ORDER_STATUSES, ['MENUNGGU_KONFIRMASI'])),
                'buyer',
                RfqContract::with(['company', 'listing.category'])
                    ->where('status', 'NEGOSIASI')
                    ->whereHas('listing', fn ($q) => $q->where('companies_id_company', $companyId)),
            ),
            'metrics' => [
                'nilai_selesai' => $sum(['SELESAI'], 'nilai'),
                'order_selesai' => (int) ($byKey['SELESAI']['count'] ?? 0),
                'nilai_berjalan' => $sum($activeStatuses, 'nilai'),
                'ton_terdaur' => $impactTon,
                'ton_berjalan' => $sum($activeStatuses, 'kg') / 1000,
                'invoice_belum' => $unpaid,
                'invoice_dibayar' => $paid,
                'invoice_lewat' => $overdue,
                'stok_ton' => $stokKg / 1000,
                'terjual_ton' => max(0, $volumeKg - $stokKg) / 1000,
                'listing_aktif' => (clone $published)->count(),
            ],
            'listings' => [
                'count' => (clone $published)->count(),
                'items' => (clone $published)->orderByDesc('id_listings')->limit(6)->get()->map(fn (Listing $l) => [
                    'id' => (int) $l->id_listings,
                    'judul' => $l->judul,
                    'kategori' => $l->category->nama ?? null,
                    'lokasi' => $l->lokasi,
                    'stok_kg' => (float) $l->stok_kg,
                    'volume_kg' => (float) $l->volume_kg,
                    'harga_per_kg' => (float) $l->harga_per_kg,
                    'terjual' => (float) $l->volume_kg > 0 ? max(0, ((float) $l->volume_kg - (float) $l->stok_kg) / (float) $l->volume_kg) : 0.0,
                    'photo' => MaterialPhoto::forCategory($l->category->nama ?? $l->judul),
                ])->all(),
            ],
            'demands' => DemandRequest::with(['category', 'company'])
                ->where('status', 'TERBUKA')
                ->where('companies_id_company', '!=', $companyId)
                ->orderByDesc('id_demand_request')->limit(3)->get(),
            'demands_count' => DemandRequest::where('status', 'TERBUKA')->where('companies_id_company', '!=', $companyId)->count(),
        ];
    }

    /**
     * Antrean keputusan Seller. Konfirmasi PO belum punya halaman/aksi backend, jadi kelompoknya informasional.
     * "Mendesak": RFQ dengan tenggat hari ini/besok (aturan yang sudah dipakai sejak FND-04).
     *
     * @param  array<string, mixed>  $waiting  tahap MENUNGGU_KONFIRMASI dari orderFlow (sumber hitungan yang sama)
     * @return array{groups: list<array<string, mixed>>}
     */
    private function sellerQueue(int $companyId, Carbon $today, array $waiting): array
    {
        $groups = [];
        $maxAge = max([1, ...array_map(fn ($o) => $o['age'] ?? 0, $waiting['orders'])]);

        $groups[] = [
            'key' => 'konfirmasi',
            'title' => 'Konfirmasi order masuk',
            'count' => $waiting['count'],
            'urgent' => 0,
            'reason' => ':n order menunggu konfirmasi Anda',
            'action' => null,
            'pending_page' => 'Halaman konfirmasi PO segera hadir',
            'items' => array_map(fn (array $o) => [
                'id' => 'konfirmasi-'.$o['id'],
                'po' => $o['id'],
                'title' => $o['code'].($o['kategori'] ? ' · '.$o['kategori'] : ''),
                'meta' => $o['buyer'].' · '.Format::kg($o['kg']).' · '.Format::rupiahShort($o['nilai']),
                'party' => $o['buyer'],
                'person' => false,
                'urgent' => false,
                'date' => null,
                'url' => null,
                'age' => $o['age'],
                'age_max' => $maxAge,
            ], $waiting['orders']),
        ];

        $rfqs = RfqContract::with(['company', 'listing.category'])
            ->where('status', 'TERBUKA')
            ->whereHas('listing', fn ($q) => $q->where('companies_id_company', $companyId));
        $soon = $today->copy()->addDay()->endOfDay();
        $canReply = Route::has('rfq.show');
        $groups[] = [
            'key' => 'rfq',
            'title' => 'Balas RFQ',
            'count' => (clone $rfqs)->count(),
            'urgent' => (clone $rfqs)->whereNotNull('tenggat')->where('tenggat', '<=', $soon)->count(),
            'reason' => ':n permintaan penawaran menunggu balasan',
            'action' => $canReply ? 'Balas' : null,
            'pending_page' => $canReply ? null : 'Halaman RFQ segera hadir',
            'items' => (clone $rfqs)->orderByRaw('tenggat IS NULL, tenggat')->limit(self::LIST_LIMIT)->get()->map(function (RfqContract $rfq) use ($today, $soon, $canReply) {
                $due = $rfq->tenggat ? Carbon::parse($rfq->tenggat)->timezone('Asia/Jakarta') : null;

                return [
                    'id' => 'rfq-'.$rfq->id_rfq_contract,
                    'po' => null,
                    'title' => 'RFQ-'.$rfq->id_rfq_contract.' · '.($rfq->listing->category->nama ?? $rfq->listing->judul ?? 'Listing'),
                    'meta' => ($rfq->company->nama ?? '–').($rfq->volume_kg_disepakati ? ' · '.Format::kg($rfq->volume_kg_disepakati) : '').($due ? ' · tenggat '.$due->format('H.i').' WIB' : ''),
                    'party' => $rfq->company->nama ?? 'RFQ',
                    'person' => false,
                    'urgent' => $due !== null && $due->lte($soon),
                    'date' => $due ? $this->dateBlock($due, $today) : null,
                    'date_caption' => 'tenggat',
                    'url' => $canReply ? route('rfq.show', $rfq->id_rfq_contract) : null,
                ];
            })->all(),
        ];

        $proofs = Payment::with('invoice.purchaseOrder.buyer')
            ->where('status_verifikasi', 'MENUNGGU')
            ->whereHas('invoice.purchaseOrder', fn ($q) => $q->where('companies_id_seller', $companyId));
        $canVerify = Route::has('seller.payments.verify');
        $groups[] = [
            'key' => 'bukti',
            'title' => 'Periksa bukti bayar',
            'count' => (clone $proofs)->count(),
            'urgent' => 0,
            'reason' => ':n bukti transfer menunggu diperiksa',
            'action' => $canVerify ? 'Periksa' : null,
            'pending_page' => $canVerify ? null : 'Halaman verifikasi pembayaran segera hadir',
            'items' => (clone $proofs)->orderBy('id_payment')->limit(self::LIST_LIMIT)->get()->map(fn (Payment $pay) => [
                'id' => 'bukti-'.$pay->id_payment,
                'po' => $pay->invoice->purchase_orders_id_purchase_order ?? null,
                'title' => ($pay->invoice->nomor ?? 'Invoice').' · '.Format::rupiahShort($pay->nominal),
                'meta' => ($pay->invoice->purchaseOrder->buyer->nama ?? '–').($pay->rekening_asal ? ' · dari '.$pay->rekening_asal : ''),
                'party' => $pay->invoice->purchaseOrder->buyer->nama ?? 'Pembayaran',
                'person' => false,
                'urgent' => false,
                // Tanggal transfer adalah catatan masa lalu, bukan tenggat: tidak pernah ditandai "lewat".
                'date' => $pay->tgl_transfer ? ['overdue' => false] + $this->dateBlock(Carbon::parse($pay->tgl_transfer), $today) : null,
                'date_caption' => 'ditransfer',
                'url' => $canVerify ? route('seller.payments.verify', $pay->id_payment) : null,
            ])->all(),
        ];

        return ['groups' => array_values(array_filter($groups, fn (array $g) => $g['count'] > 0))];
    }

    /**
     * Zona 2 Seller/Buyer: order yang sedang berjalan (dan, bila diberikan, RFQ dalam negosiasi), masing-masing dengan
     * jangkar waktu yang benar-benar tercatat: jadwal inspeksi, jatuh tempo invoice, jadwal kirim, atau tenggat RFQ.
     *
     * @param  list<string>  $statuses  status order yang ditampilkan
     * @param  'buyer'|'seller'  $counterpart  pihak lawan yang ditulis di baris
     * @param  Builder<RfqContract>|null  $negotiations
     * @return array{count: int, week: list<array<string, mixed>>, items: list<array<string, mixed>>}
     */
    private function orderRunning(string $partyColumn, int $companyId, Carbon $today, array $statuses, string $counterpart, $negotiations = null): array
    {
        $orders = PurchaseOrder::with($counterpart)
            ->where($partyColumn, $companyId)
            ->whereIn('status', $statuses);

        $count = (clone $orders)->count() + ($negotiations ? (clone $negotiations)->count() : 0);
        $loaded = (clone $orders)->orderBy('id_purchase_order')->limit(50)->get();
        $materials = $this->orderMaterials($loaded->pluck('id_purchase_order')->all());
        $ids = $loaded->pluck('id_purchase_order')->all();
        $earliest = fn (string $table, string $column, array $statuses) => DB::table($table)
            ->whereIn('purchase_orders_id_purchase_order', $ids)
            ->whereIn('status', $statuses)
            ->whereNotNull($column)
            ->groupBy('purchase_orders_id_purchase_order')
            ->selectRaw("purchase_orders_id_purchase_order AS po, MIN({$column}) AS waktu")
            ->pluck('waktu', 'po');
        $inspectionAt = $earliest('inspections', 'jadwal', ['DITUGASKAN', 'DIPROSES']);
        $dueAt = $earliest('invoices', 'jatuh_tempo', ['TERBIT']);
        $shipments = $earliest('shipments_manifests', 'jadwal', ['DRAF', 'DALAM_PERJALANAN']);

        $items = $loaded->map(function (PurchaseOrder $po) use ($materials, $shipments, $inspectionAt, $dueAt, $today, $counterpart) {
            // Jangkar waktu sesuai tahap; tanpa catatan tanggal, blok tanggal dikosongkan (tidak ditebak).
            [$when, $caption] = match (true) {
                in_array($po->status, ['DIKONFIRMASI', 'INSPEKSI'], true) => [$inspectionAt[$po->id_purchase_order] ?? null, 'inspeksi'],
                $po->status === 'INVOICED' => [$dueAt[$po->id_purchase_order] ?? null, 'jatuh tempo'],
                in_array($po->status, ['DIBAYAR', 'DALAM_PENGIRIMAN'], true) => [$shipments[$po->id_purchase_order] ?? null, 'kirim'],
                default => [null, null],
            };
            $date = $when ? Carbon::parse($when)->timezone('Asia/Jakarta') : null;
            $material = $materials[$po->id_purchase_order] ?? null;

            return [
                'id' => 'po-'.$po->id_purchase_order,
                'sort' => $date->timestamp ?? PHP_INT_MAX,
                'title' => 'PO-'.$po->id_purchase_order.($material['nama'] ?? null ? ' · '.$material['nama'] : ''),
                'meta' => collect([$po->{$counterpart}->nama ?? null, Format::kg($po->volume_kg), Format::rupiahShort((float) $po->volume_kg * (float) $po->harga_per_kg)])->filter()->implode(' · '),
                'status' => StatusMap::label('purchase_orders.status', $po->status),
                'step' => $this->flowStep($po->status),
                'step_label' => StatusMap::label('purchase_orders.status', $po->status),
                'time' => $date && $caption !== 'jatuh tempo' ? $date->format('H.i') : null,
                'date' => $date ? $this->dateBlock($date, $today) : null,
                'date_caption' => $date ? $caption : null,
            ];
        });

        $items = $items->concat(! $negotiations ? [] : (clone $negotiations)->orderByRaw('tenggat IS NULL, tenggat')->limit(self::LIST_LIMIT)->get()
            ->map(function (RfqContract $rfq) use ($today) {
                $due = $rfq->tenggat ? Carbon::parse($rfq->tenggat)->timezone('Asia/Jakarta') : null;

                return [
                    'id' => 'rfq-'.$rfq->id_rfq_contract,
                    'sort' => $due->timestamp ?? PHP_INT_MAX,
                    'title' => 'Negosiasi RFQ-'.$rfq->id_rfq_contract.' · '.($rfq->listing->category->nama ?? $rfq->listing->judul ?? 'Listing'),
                    'meta' => ($rfq->company->nama ?? '–').($rfq->volume_kg_disepakati ? ' · '.Format::kg($rfq->volume_kg_disepakati) : ''),
                    'status' => StatusMap::label('rfq_contracts.status', 'NEGOSIASI'),
                    'step' => null,
                    'step_label' => null,
                    'time' => $due?->format('H.i'),
                    'date' => $due ? $this->dateBlock($due, $today) : null,
                    'date_caption' => $due ? 'tenggat' : null,
                ];
            }));

        $sorted = $items->sortBy('sort')->values();

        return [
            'count' => $count,
            'week' => $this->weekStrip($today, $sorted->pluck('date.iso')->filter()->countBy()->all()),
            'items' => $sorted->map(fn (array $i) => collect($i)->except('sort')->all())->all(),
        ];
    }

    /**
     * Tujuh hari mulai hari ini (WIB) dengan jumlah per tanggal.
     *
     * @param  array<string, int>  $countsByDate
     * @return list<array<string, mixed>>
     */
    private function weekStrip(Carbon $today, array $countsByDate): array
    {
        $days = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];

        return collect(range(0, 6))->map(function (int $offset) use ($today, $countsByDate, $days) {
            $day = $today->copy()->addDays($offset);

            return [
                'date' => $day->toDateString(),
                'label' => Format::date($day),
                'dow' => $offset === 0 ? 'Hari ini' : $days[$day->dayOfWeek],
                'day' => $day->format('j'),
                'count' => (int) ($countsByDate[$day->toDateString()] ?? 0),
            ];
        })->all();
    }

    /**
     * Dasbor Buyer (Senja di Sawah): Perlu tindakan Anda → Sedang berjalan (pelacak rute + order) → Gambaran total tercatat.
     * Semua angka dari order, invoice, RFQ, manifes, dan dampak milik perusahaan Buyer; satu fakta = satu sumber.
     *
     * @return array<string, mixed>
     */
    public function buyer(int $companyId): array
    {
        $today = Carbon::now('Asia/Jakarta')->startOfDay();
        $flow = $this->orderFlow($today, 'companies_id_buyer', $companyId);
        $byKey = collect([...$flow['stages'], ...$flow['exits']])->keyBy('key');
        $sum = fn (array $keys, string $field) => (float) collect($keys)->sum(fn ($k) => $byKey[$k][$field] ?? 0);
        $activeStatuses = self::ACTIVE_ORDER_STATUSES;

        $invoices = Invoice::whereHas('purchaseOrder', fn ($q) => $q->where('companies_id_buyer', $companyId));
        $impact = ImpactLog::whereHas('purchaseOrder', fn ($q) => $q->where('companies_id_buyer', $companyId))
            ->selectRaw('COALESCE(SUM(tonase_ton), 0) AS ton, COALESCE(SUM(hemat_buyer_rp), 0) AS hemat')->first();

        // Rekomendasi: listing aktif perusahaan lain, kategori kebutuhan terbuka Buyer lebih dulu.
        $demandCategories = DemandRequest::where('companies_id_company', $companyId)->where('status', 'TERBUKA')->pluck('categories_id_categories')->all();
        $recommend = Listing::with(['category', 'company'])
            ->where('status', 'PUBLISHED')
            ->where('companies_id_company', '!=', $companyId)
            ->where('stok_kg', '>', 0);
        $canRfq = Route::has('rfq.create');

        return [
            'flow' => $flow,
            'queue' => $this->buyerQueue($companyId, $today, $byKey['DITERIMA']),
            'shipments' => $this->buyerShipments($companyId, $today),
            'running' => $this->orderRunning(
                'companies_id_buyer', $companyId, $today,
                // Dalam Pengiriman tampil di pelacak rute; Menunggu Pembayaran dan Diterima ada di Zona 1 (satu tempat per fakta).
                ['MENUNGGU_KONFIRMASI', 'DIKONFIRMASI', 'INSPEKSI', 'LOLOS_INSPEKSI', 'DIBAYAR'],
                'seller',
            ),
            'metrics' => [
                'nilai_selesai' => $sum(['SELESAI'], 'nilai'),
                'order_selesai' => (int) ($byKey['SELESAI']['count'] ?? 0),
                'nilai_berjalan' => $sum($activeStatuses, 'nilai'),
                'ton_diterima' => (float) ($impact->ton ?? 0),
                'ton_berjalan' => $sum($activeStatuses, 'kg') / 1000,
                'tagihan_terbuka' => (float) (clone $invoices)->where('status', 'TERBIT')->sum('total'),
                'tagihan_dibayar' => (float) (clone $invoices)->where('status', 'DIBAYAR')->sum('total'),
                'tagihan_lewat' => (clone $invoices)->where('status', 'TERBIT')->whereNotNull('jatuh_tempo')->where('jatuh_tempo', '<', $today->toDateString())->count(),
                'hemat' => (float) ($impact->hemat ?? 0),
            ],
            'recommend' => [
                'by_demand' => $demandCategories !== [],
                'count' => (clone $recommend)->count(),
                'items' => (clone $recommend)
                    ->when($demandCategories !== [], fn ($q) => $q->orderByRaw('categories_id_categories IN ('.implode(',', array_map('intval', $demandCategories)).') DESC'))
                    ->orderByDesc('id_listings')->limit(3)->get()
                    ->map(fn (Listing $l) => [
                        'id' => (int) $l->id_listings,
                        'judul' => $l->judul,
                        'kategori' => $l->category->nama ?? null,
                        'seller' => $l->company->nama ?? null,
                        'verified' => ($l->company->status_verifikasi ?? null) === 'TERVERIFIKASI',
                        'lokasi' => $l->lokasi,
                        'stok_kg' => (float) $l->stok_kg,
                        'volume_kg' => (float) $l->volume_kg,
                        'harga_per_kg' => (float) $l->harga_per_kg,
                        'terjual' => (float) $l->volume_kg > 0 ? max(0, ((float) $l->volume_kg - (float) $l->stok_kg) / (float) $l->volume_kg) : 0.0,
                        'photo' => MaterialPhoto::forCategory($l->category->nama ?? $l->judul),
                        'match' => in_array($l->categories_id_categories, $demandCategories, true),
                        'url' => $canRfq ? route('rfq.create', ['listing_id' => $l->id_listings]) : null,
                    ])->all(),
            ],
            'demand_count' => count($demandCategories),
        ];
    }

    /**
     * Antrean keputusan Buyer: bayar tagihan, tanggapi balasan RFQ, konfirmasi penerimaan.
     * "Mendesak": tagihan jatuh tempo ≤ besok atau sudah lewat, RFQ dengan tenggat ≤ besok.
     * Konfirmasi penerimaan belum punya aksi backend, jadi kelompoknya informasional.
     *
     * @param  array<string, mixed>  $received  tahap DITERIMA dari orderFlow (sumber hitungan yang sama)
     * @return array{groups: list<array<string, mixed>>}
     */
    private function buyerQueue(int $companyId, Carbon $today, array $received): array
    {
        $groups = [];
        $soon = $today->copy()->addDay()->endOfDay();

        $open = Invoice::with('purchaseOrder.seller')
            ->whereHas('purchaseOrder', fn ($q) => $q->where('companies_id_buyer', $companyId))
            ->where('status', 'TERBIT');
        $canPay = Route::has('buyer.invoices.pay');
        $groups[] = [
            'key' => 'bayar',
            'title' => 'Bayar tagihan',
            'count' => (clone $open)->count(),
            'urgent' => (clone $open)->whereNotNull('jatuh_tempo')->where('jatuh_tempo', '<=', $soon->toDateString())->count(),
            'reason' => ':n invoice menunggu pembayaran Anda',
            'action' => $canPay ? 'Bayar' : null,
            'pending_page' => $canPay ? null : 'Halaman pembayaran segera hadir',
            'items' => (clone $open)->orderByRaw('jatuh_tempo IS NULL, jatuh_tempo')->limit(self::LIST_LIMIT)->get()->map(function (Invoice $inv) use ($today, $soon, $canPay) {
                $due = $inv->jatuh_tempo ? Carbon::parse($inv->jatuh_tempo) : null;

                return [
                    'id' => 'bayar-'.$inv->id_invoice,
                    'po' => (int) $inv->purchase_orders_id_purchase_order,
                    'title' => $inv->nomor.' · '.Format::rupiahShort($inv->total),
                    'meta' => ($inv->purchaseOrder->seller->nama ?? '–').' · PO-'.$inv->purchase_orders_id_purchase_order,
                    'party' => $inv->purchaseOrder->seller->nama ?? 'Invoice',
                    'person' => false,
                    'urgent' => $due !== null && $due->lte($soon),
                    'date' => $due ? $this->dateBlock($due, $today) : null,
                    'date_caption' => $due ? 'jatuh tempo' : null,
                    'url' => $canPay ? route('buyer.invoices.pay', $inv->id_invoice) : null,
                ];
            })->all(),
        ];

        $offers = RfqContract::with(['listing.company', 'listing.category'])
            ->where('companies_id_company', $companyId)
            ->where('status', 'NEGOSIASI');
        $canReply = Route::has('rfq.show');
        $groups[] = [
            'key' => 'rfq',
            'title' => 'Tanggapi penawaran',
            'count' => (clone $offers)->count(),
            'urgent' => (clone $offers)->whereNotNull('tenggat')->where('tenggat', '<=', $soon)->count(),
            'reason' => ':n RFQ dibalas Seller, menunggu tanggapan Anda',
            'action' => $canReply ? 'Tanggapi' : null,
            'pending_page' => $canReply ? null : 'Halaman RFQ segera hadir',
            'items' => (clone $offers)->orderByRaw('tenggat IS NULL, tenggat')->limit(self::LIST_LIMIT)->get()->map(function (RfqContract $rfq) use ($today, $soon, $canReply) {
                $due = $rfq->tenggat ? Carbon::parse($rfq->tenggat)->timezone('Asia/Jakarta') : null;

                return [
                    'id' => 'rfq-'.$rfq->id_rfq_contract,
                    'po' => null,
                    'title' => 'RFQ-'.$rfq->id_rfq_contract.' · '.($rfq->listing->category->nama ?? $rfq->listing->judul ?? 'Listing'),
                    'meta' => ($rfq->listing->company->nama ?? '–').($rfq->volume_kg_disepakati ? ' · '.Format::kg($rfq->volume_kg_disepakati) : '').($rfq->harga_disepakati ? ' · '.Format::rupiah($rfq->harga_disepakati).'/kg' : ''),
                    'party' => $rfq->listing->company->nama ?? 'RFQ',
                    'person' => false,
                    'urgent' => $due !== null && $due->lte($soon),
                    'date' => $due ? $this->dateBlock($due, $today) : null,
                    'date_caption' => $due ? 'tenggat' : null,
                    'url' => $canReply ? route('rfq.show', $rfq->id_rfq_contract) : null,
                ];
            })->all(),
        ];

        $groups[] = [
            'key' => 'terima',
            'title' => 'Konfirmasi penerimaan',
            'count' => $received['count'],
            'urgent' => 0,
            'reason' => ':n order sudah diterima, menunggu konfirmasi Anda',
            'action' => null,
            'pending_page' => 'Halaman konfirmasi penerimaan segera hadir',
            'items' => array_map(fn (array $o) => [
                'id' => 'terima-'.$o['id'],
                'po' => $o['id'],
                'title' => $o['code'].($o['kategori'] ? ' · '.$o['kategori'] : ''),
                'meta' => $o['seller'].' · '.Format::kg($o['kg']).' · '.Format::rupiahShort($o['nilai']),
                'party' => $o['seller'],
                'person' => false,
                'urgent' => false,
                'date' => null,
                'url' => null,
                'age' => $o['age'],
                'age_max' => max([1, ...array_map(fn ($x) => $x['age'] ?? 0, $received['orders'])]),
            ], $received['orders']),
        ];

        return ['groups' => array_values(array_filter($groups, fn (array $g) => $g['count'] > 0))];
    }

    /**
     * Pengiriman ke Buyer untuk pelacak rute. Posisi penanda mengikuti status manifes (Draf 0 · Dalam perjalanan ½ ·
     * Tiba 1), bukan GPS — koordinat perjalanan tidak dicatat di DDL. Waktu milestone hanya yang tercatat.
     *
     * @return array{count: int, items: list<array<string, mixed>>}
     */
    private function buyerShipments(int $companyId, Carbon $today): array
    {
        $base = DB::table('shipments_manifests AS sm')
            ->join('purchase_orders AS po', 'po.id_purchase_order', '=', 'sm.purchase_orders_id_purchase_order')
            ->leftJoin('companies AS s', 's.id_company', '=', 'po.companies_id_seller')
            ->leftJoin('goods_receipts AS gr', 'gr.shipments_manifests_id_shipment_manifest', '=', 'sm.id_shipment_manifest')
            ->where('po.companies_id_buyer', $companyId)
            ->whereIn('sm.status', ['DRAF', 'DALAM_PERJALANAN', 'TIBA'])
            ->whereNotIn('po.status', ['SELESAI', 'DIBATALKAN', 'DITOLAK']);

        $rows = (clone $base)->orderByRaw('sm.jadwal IS NULL, sm.jadwal')->limit(self::LIST_LIMIT)
            ->get(['sm.id_shipment_manifest AS id', 'sm.status', 'sm.jadwal', 'sm.rute', 'sm.armada', 'sm.plat', 'sm.berat_dikirim_kg',
                'po.id_purchase_order AS po', 'po.volume_kg', 'po.jarak_km', 's.nama AS seller', 'gr.waktu AS tiba']);
        $materials = $this->orderMaterials($rows->pluck('po')->map(fn ($id) => (int) $id)->all());
        $fmt = fn ($t) => $t ? Carbon::parse($t)->timezone('Asia/Jakarta') : null;

        return [
            'count' => (clone $base)->count(),
            'items' => $rows->map(function ($r) use ($materials, $fmt, $today) {
                $jadwal = $fmt($r->jadwal);
                $tiba = $fmt($r->tiba);
                $arrived = $r->status === 'TIBA' || $tiba !== null;
                $stage = $arrived ? 2 : ($r->status === 'DALAM_PERJALANAN' ? 1 : 0);
                [$from, $to] = array_pad(array_map('trim', explode('→', (string) $r->rute)), 2, null);
                $material = $materials[(int) $r->po] ?? null;
                $stamp = fn (?Carbon $t) => $t ? Format::date($t).', '.$t->format('H.i').' WIB' : null;

                return [
                    'id' => (int) $r->id,
                    'po' => (int) $r->po,
                    'title' => 'PO-'.$r->po.($material['nama'] ?? null ? ' · '.$material['nama'] : ''),
                    'seller' => $r->seller,
                    'from' => $from ?: ($material['lokasi'] ?? 'Lokasi Seller'),
                    'to' => $to ?: 'Gudang Anda',
                    'armada' => $r->armada,
                    'plat' => $r->plat,
                    'muatan' => (float) $r->berat_dikirim_kg > 0 ? Format::kg($r->berat_dikirim_kg) : Format::kg($r->volume_kg).' (dipesan)',
                    'jarak' => $r->jarak_km !== null ? Format::number($r->jarak_km).' km' : null,
                    'progress' => [0, 0.5, 1][$stage],
                    'stage' => $stage,
                    'status' => ['Dijadwalkan', 'Dalam Perjalanan', 'Tiba'][$stage],
                    'date' => $jadwal ? $this->dateBlock($jadwal, $today) : null,
                    'milestones' => [
                        ['label' => 'Dijadwalkan', 'time' => $stamp($jadwal)],
                        ['label' => 'Dalam perjalanan', 'time' => null],
                        ['label' => 'Tiba', 'time' => $stamp($tiba)],
                    ],
                ];
            })->all(),
        ];
    }

    /**
     * Dasbor Inspector (Senja di Sawah): Perlu tindakan Anda → Sedang berjalan (garis waktu jam, 7 hari) → Gambaran
     * total tercatat (+ kartu kredensial). Semua dari inspeksi, verifikasi dokumen, hasil, dan profil milik Inspector ini.
     *
     * @return array<string, mixed>
     */
    public function inspector(int $userId): array
    {
        $today = InspectorProfile::today();
        $now = Carbon::now(InspectorProfile::TIMEZONE);
        $mine = Inspection::where('users_id_user', $userId);
        $profile = InspectorProfile::where('id_user', $userId)->first();

        $docs = DB::table('document_verifications')->where('users_id_user', $userId);
        $results = DB::table('inspection_results AS ir')
            ->join('inspections AS i', 'i.id_inspection', '=', 'ir.inspections_id_inspection')
            ->where('i.users_id_user', $userId)
            ->where('ir.status', 'FINAL');

        return [
            'queue' => $this->inspectorQueue($userId, $today, $profile),
            'timeline' => $this->inspectorTimeline($userId, $today, $now),
            'metrics' => [
                'selesai' => (clone $mine)->where('status', 'SELESAI')->count(),
                'berjalan' => (clone $mine)->whereIn('status', ['DITUGASKAN', 'DIPROSES'])->count(),
                'lulus' => (clone $results)->where('ir.hasil', 'LULUS')->count(),
                'gagal' => (clone $results)->where('ir.hasil', 'GAGAL')->count(),
                'dok_menunggu' => (clone $docs)->where('status', 'MENUNGGU')->count(),
                'dok_selesai' => (clone $docs)->whereIn('status', ['SETUJU', 'DITOLAK'])->count(),
                'lapangan' => (clone $mine)->where('status', '!=', 'DIBATALKAN')->where('mode_verifikasi', 'LAPANGAN')->count(),
                'desk' => (clone $mine)->where('status', '!=', 'DIBATALKAN')->where('mode_verifikasi', 'DESK')->count(),
            ],
            'credential' => $profile === null ? null : $this->credential($profile, $today),
        ];
    }

    /**
     * Antrean Inspector. Tugas inspeksi dan verifikasi dokumen belum punya halaman/aksi backend, jadi kelompoknya
     * informasional; perbarui sertifikat menuju Profil Kompetensi yang sudah ada.
     * "Mendesak": tugas yang jadwalnya hari ini atau sudah lewat, dan sertifikat yang berakhir ≤ 7 hari.
     *
     * @return array{groups: list<array<string, mixed>>}
     */
    private function inspectorQueue(int $userId, Carbon $today, ?InspectorProfile $profile): array
    {
        $groups = [];
        $endOfToday = $today->copy()->endOfDay()->utc();

        foreach ([
            ['tugas', 'DITUGASKAN', 'Mulai inspeksi', ':n inspeksi ditugaskan kepada Anda', 'Halaman tugas inspeksi segera hadir'],
            ['laporan', 'DIPROSES', 'Selesaikan laporan', ':n inspeksi sedang diproses, laporan belum final', 'Halaman formulir inspeksi segera hadir'],
        ] as [$key, $status, $title, $reason, $pending]) {
            $tasks = Inspection::with(['purchaseOrder.seller', 'purchaseOrder.buyer'])->where('users_id_user', $userId)->where('status', $status);
            $loaded = (clone $tasks)->orderByRaw('jadwal IS NULL, jadwal')->limit(self::LIST_LIMIT)->get();
            $materials = $this->orderMaterials($loaded->pluck('purchase_orders_id_purchase_order')->all());
            $groups[] = [
                'key' => $key,
                'title' => $title,
                'count' => (clone $tasks)->count(),
                'urgent' => (clone $tasks)->whereNotNull('jadwal')->where('jadwal', '<=', $endOfToday)->count(),
                'reason' => $reason,
                'action' => null,
                'pending_page' => $pending,
                'items' => $loaded->map(function (Inspection $i) use ($materials, $today, $endOfToday) {
                    $jadwal = $i->jadwal ? Carbon::parse($i->jadwal)->timezone(InspectorProfile::TIMEZONE) : null;
                    $material = $materials[$i->purchase_orders_id_purchase_order] ?? null;

                    return [
                        'id' => 'inspeksi-'.$i->id_inspection,
                        'po' => (int) $i->purchase_orders_id_purchase_order,
                        'title' => 'PO-'.$i->purchase_orders_id_purchase_order.($material['nama'] ?? null ? ' · '.$material['nama'] : ''),
                        'meta' => collect([
                            StatusMap::label('inspections.mode_verifikasi', $i->mode_verifikasi),
                            $material['lokasi'] ?? null,
                            ($i->purchaseOrder->seller->nama ?? '–').' → '.($i->purchaseOrder->buyer->nama ?? '–'),
                            $jadwal?->format('H.i').($jadwal ? ' WIB' : ''),
                        ])->filter()->implode(' · '),
                        'party' => $i->purchaseOrder->seller->nama ?? 'Inspeksi',
                        'person' => false,
                        'urgent' => $jadwal !== null && $jadwal->copy()->utc()->lte($endOfToday),
                        'date' => $jadwal ? $this->dateBlock($jadwal, $today) : null,
                        'date_caption' => $jadwal ? 'jadwal' : null,
                        'url' => null,
                    ];
                })->all(),
            ];
        }

        $docs = DB::table('document_verifications AS dv')
            ->leftJoin('purchase_orders AS po', 'po.id_purchase_order', '=', 'dv.purchase_orders_id_purchase_order')
            ->leftJoin('companies AS s', 's.id_company', '=', 'po.companies_id_seller')
            ->where('dv.users_id_user', $userId)
            ->where('dv.status', 'MENUNGGU');
        $groups[] = [
            'key' => 'dokumen',
            'title' => 'Verifikasi dokumen',
            'count' => (clone $docs)->count(),
            'urgent' => 0,
            'reason' => ':n berkas menunggu diperiksa',
            'action' => null,
            'pending_page' => 'Halaman verifikasi dokumen segera hadir',
            'items' => (clone $docs)->orderBy('dv.id_document_verification')->limit(self::LIST_LIMIT)
                ->get(['dv.id_document_verification AS id', 'dv.purchase_orders_id_purchase_order AS po', 'dv.verifiable_type', 's.nama AS seller'])
                ->map(fn ($d) => [
                    'id' => 'dokumen-'.$d->id,
                    'po' => (int) $d->po,
                    'title' => 'Berkas #'.$d->id.' · PO-'.$d->po,
                    'meta' => collect([$d->seller, match (class_basename((string) $d->verifiable_type)) {
                        'CompanyDocument' => 'Dokumen perusahaan',
                        'ShipmentDocument' => 'Dokumen pengiriman',
                        'InspectionResult' => 'Hasil inspeksi',
                        '' => null,
                        default => 'Berkas',
                    }])->filter()->implode(' · '),
                    'party' => $d->seller ?? 'Dokumen',
                    'person' => false,
                    'urgent' => false,
                    'date' => null,
                    'url' => null,
                ])->all(),
        ];

        if ($profile !== null && $profile->berlaku_sampai !== null && $profile->status() !== InspectorStatus::AKTIF && $profile->status_aktif) {
            $days = $profile->daysUntilExpiry($today);
            $canRenew = Route::has('inspector.profile');
            $groups[] = [
                'key' => 'sertifikat',
                'title' => 'Perbarui sertifikat',
                'count' => 1,
                'urgent' => $days !== null && $days <= 7 ? 1 : 0,
                'reason' => $days !== null && $days < 0 ? 'Sertifikat Anda sudah berakhir' : 'Sertifikat Anda berakhir ≤ 30 hari',
                'action' => $canRenew ? 'Perbarui' : null,
                'pending_page' => $canRenew ? null : 'Halaman profil kompetensi segera hadir',
                'items' => [[
                    'id' => 'sertifikat-'.$profile->id_inspector,
                    'po' => null,
                    'title' => 'Sertifikat '.($profile->no_sertifikat ?: 'tanpa nomor'),
                    'meta' => 'berakhir '.Format::date($profile->berlaku_sampai),
                    'party' => 'Sertifikat',
                    'person' => false,
                    'urgent' => $days !== null && $days <= 7,
                    'date' => $this->dateBlock(Carbon::parse($profile->berlaku_sampai), $today),
                    'date_caption' => 'berakhir',
                    'days_left' => max(0, (int) $days),
                    'url' => $canRenew ? route('inspector.profile') : null,
                ]],
            ];
        }

        return ['groups' => array_values(array_filter($groups, fn (array $g) => $g['count'] > 0))];
    }

    /**
     * Garis waktu jam 7 hari (hari ini s.d. +6, WIB): entri per hari dengan jam, lokasi, status, dan detail untuk
     * ekspansi di tempat. Penanda SEKARANG hanya pada hari ini, tepat satu, di antara entri menurut jam saat ini.
     *
     * @return array<string, mixed>
     */
    private function inspectorTimeline(int $userId, Carbon $today, Carbon $now): array
    {
        $from = $today->copy()->utc();
        $to = $today->copy()->addDays(7)->utc();
        $rows = DB::table('inspections AS i')
            ->join('purchase_orders AS po', 'po.id_purchase_order', '=', 'i.purchase_orders_id_purchase_order')
            ->leftJoin('companies AS s', 's.id_company', '=', 'po.companies_id_seller')
            ->leftJoin('companies AS b', 'b.id_company', '=', 'po.companies_id_buyer')
            ->leftJoin('rfq_contracts AS r', 'r.id_rfq_contract', '=', 'po.rfq_contracts_id_rfq_contract')
            ->leftJoin('listings AS l', 'l.id_listings', '=', 'r.listings_id_listings')
            ->leftJoin('categories AS c', 'c.id_categories', '=', 'l.categories_id_categories')
            ->where('i.users_id_user', $userId)
            ->where('i.status', '!=', 'DIBATALKAN')
            ->whereBetween('i.jadwal', [$from, $to])
            ->orderBy('i.jadwal')
            ->get(['i.id_inspection AS id', 'i.jadwal', 'i.status', 'i.mode_verifikasi', 'i.alasan_mode', 'po.id_purchase_order AS po', 'po.volume_kg',
                's.nama AS seller', 's.alamat AS alamat', 's.kontak AS kontak', 'b.nama AS buyer', 'c.nama AS kategori', 'l.lokasi AS lokasi']);

        $entries = $rows->map(function ($r) {
            $at = Carbon::parse($r->jadwal)->timezone(InspectorProfile::TIMEZONE);

            return [
                'id' => (int) $r->id,
                'date' => $at->toDateString(),
                'time' => $at->format('H.i'),
                'minutes' => $at->hour * 60 + $at->minute,
                'title' => 'PO-'.$r->po.($r->kategori ? ' · '.$r->kategori : ''),
                'lokasi' => $r->lokasi ?: ($r->alamat ?: 'Lokasi belum tercatat'),
                'status' => StatusMap::label('inspections.status', $r->status),
                'done' => $r->status === 'SELESAI',
                'mode' => StatusMap::label('inspections.mode_verifikasi', $r->mode_verifikasi),
                'pihak' => ($r->seller ?? '–').' → '.($r->buyer ?? '–'),
                'muatan' => Format::kg($r->volume_kg),
                'alamat' => $r->alamat,
                'kontak' => $r->kontak,
                'catatan' => $r->alasan_mode,
            ];
        })->groupBy('date');

        $week = $this->weekStrip($today, $entries->map->count()->all());

        return [
            'today' => $today->toDateString(),
            'now_minutes' => $now->hour * 60 + $now->minute,
            'now_label' => $now->format('H.i'),
            'week' => $week,
            'days' => collect($week)->mapWithKeys(fn ($d) => [$d['date'] => $entries[$d['date']] ?? collect()])->map(fn ($c) => collect($c)->values()->all())->all(),
        ];
    }

    /**
     * Kartu kredensial (panduan §7.11) dari profil Inspector sendiri; status dihitung dari tanggal berakhir sebenarnya.
     *
     * @return array<string, mixed>
     */
    private function credential(InspectorProfile $profile, Carbon $today): array
    {
        $status = $profile->status($today);
        $days = $profile->daysUntilExpiry($today);
        [$label, $tone] = match (true) {
            ! $profile->status_aktif => ['Nonaktif', 'meta'],
            $days !== null && $days < 0 => ['Berakhir', 'error'],
            $status === InspectorStatus::SEGERA_BERAKHIR => ['Berakhir ≤ 30 hari', 'warning'],
            default => ['Aktif', 'success'],
        };

        return [
            'nomor' => $profile->no_sertifikat,
            'wilayah' => $profile->wilayah_tugas,
            'keahlian' => $profile->expertiseCategories()->pluck('nama')->all(),
            'label' => $label,
            'tone' => $tone,
            'days' => $days,
            'date' => $profile->berlaku_sampai ? $this->dateBlock(Carbon::parse($profile->berlaku_sampai), $today) : null,
            'url' => Route::has('inspector.profile') ? route('inspector.profile') : null,
        ];
    }

    /**
     * Kategori, lokasi, dan foto material per PO (lewat RFQ → listing → kategori).
     *
     * @param  list<int>  $orderIds
     * @return array<int, array{nama: ?string, lokasi: ?string, photo: ?string}>
     */
    private function orderMaterials(array $orderIds): array
    {
        if ($orderIds === []) {
            return [];
        }

        return DB::table('purchase_orders')
            ->join('rfq_contracts', 'rfq_contracts.id_rfq_contract', '=', 'purchase_orders.rfq_contracts_id_rfq_contract')
            ->join('listings', 'listings.id_listings', '=', 'rfq_contracts.listings_id_listings')
            ->leftJoin('categories', 'categories.id_categories', '=', 'listings.categories_id_categories')
            ->whereIn('purchase_orders.id_purchase_order', $orderIds)
            ->get(['purchase_orders.id_purchase_order AS id', 'categories.nama AS nama', 'listings.lokasi AS lokasi'])
            ->mapWithKeys(fn ($row) => [(int) $row->id => [
                'nama' => $row->nama,
                'lokasi' => $row->lokasi,
                'photo' => MaterialPhoto::forCategory($row->nama),
            ]])->all();
    }
}

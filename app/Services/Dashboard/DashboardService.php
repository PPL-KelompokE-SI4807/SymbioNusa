<?php

namespace App\Services\Dashboard;

use App\Models\Company;
use App\Models\DemandRequest;
use App\Models\Dispute;
use App\Models\ImpactLog;
use App\Models\Inspection;
use App\Models\Invoice;
use App\Models\Listing;
use App\Models\Payment;
use App\Models\PurchaseOrder;
use App\Models\RfqContract;
use App\Support\Format;
use App\Support\MaterialPhoto;
use App\Support\StatusMap;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use stdClass;

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

    /**
     * @return array<string, mixed>
     */
    public function admin(): array
    {
        $today = Carbon::today();

        $inspectors = DB::table('inspector_profiles')->selectRaw(
            'COUNT(*) AS total, SUM(CASE WHEN status_aktif = 1 AND (berlaku_sampai IS NULL OR berlaku_sampai >= ?) THEN 1 ELSE 0 END) AS aktif',
            [$today->toDateString()]
        )->first();

        $expiringCerts = DB::table('inspector_profiles')
            ->join('users', 'users.id_user', '=', 'inspector_profiles.id_user')
            ->where('inspector_profiles.status_aktif', 1)
            ->whereBetween('inspector_profiles.berlaku_sampai', [$today->toDateString(), $today->copy()->addDays(30)->toDateString()])
            ->orderBy('inspector_profiles.berlaku_sampai')
            ->get(['users.nama', 'inspector_profiles.id_inspector', 'inspector_profiles.berlaku_sampai']);

        $orderCounts = PurchaseOrder::query()
            ->selectRaw('status, COUNT(*) AS jumlah')
            ->groupBy('status')
            ->pluck('jumlah', 'status');

        $count = fn (string ...$statuses): int => (int) collect($statuses)->sum(fn (string $s) => $orderCounts[$s] ?? 0);

        // Tahap tertahan: tahap berjalan (bukan Draf/Selesai) dengan order terbanyak.
        $candidates = ['MENUNGGU_KONFIRMASI', 'DIKONFIRMASI', 'INSPEKSI', 'LOLOS_INSPEKSI', 'INVOICED', 'DIBAYAR', 'DALAM_PENGIRIMAN', 'DITERIMA'];
        $stuckStatus = collect($candidates)->filter(fn ($s) => $count($s) > 0)->sortByDesc(fn ($s) => $count($s))->first();

        $groups = [];
        foreach (self::FLOW_GROUPS as $group => $statuses) {
            foreach ($statuses as $status) {
                $groups[$group][] = [
                    'status' => $status,
                    'label' => StatusMap::label('purchase_orders.status', $status),
                    'count' => $count($status),
                    'stuck' => $status === $stuckStatus,
                    'done' => $status === 'SELESAI',
                ];
            }
        }

        $unassigned = PurchaseOrder::with(['seller', 'buyer'])
            ->where('status', 'DIKONFIRMASI')
            ->whereDoesntHave('inspections', fn ($q) => $q->whereIn('status', ['DITUGASKAN', 'DIPROSES', 'SELESAI']))
            ->orderBy('id_purchase_order')->limit(10)->get();

        $impact = ImpactLog::query()->selectRaw(
            'COALESCE(SUM(tonase_ton), 0) AS tonase, COALESCE(SUM(hemat_seller_rp), 0) AS seller, COALESCE(SUM(hemat_buyer_rp), 0) AS buyer, COALESCE(SUM(co2e_net_kg), 0) AS co2e, COUNT(*) AS orders'
        )->first();

        return [
            'flow' => [
                'groups' => $groups,
                'stuck' => $stuckStatus === null ? null : [
                    'label' => StatusMap::label('purchase_orders.status', $stuckStatus),
                    'count' => $count($stuckStatus),
                    'unassigned' => in_array($stuckStatus, ['DIKONFIRMASI', 'INSPEKSI'], true) ? $unassigned->count() : 0,
                ],
                'exits' => [
                    ['label' => 'Ditolak', 'count' => $count('DITOLAK')],
                    ['label' => 'Gagal Inspeksi', 'count' => $count('GAGAL_INSPEKSI')],
                    ['label' => 'Sengketa', 'count' => $count('SENGKETA')],
                    ['label' => 'Dibatalkan', 'count' => $count('DIBATALKAN')],
                ],
                'rfq_open' => RfqContract::whereIn('status', ['TERBUKA', 'NEGOSIASI'])->count(),
            ],
            'queue' => $this->adminQueue($expiringCerts, $today, $unassigned),
            'materials' => $this->materials(),
            'impact' => [
                'tonase' => (float) ($impact->tonase ?? 0),
                'hemat_seller' => (float) ($impact->seller ?? 0),
                'hemat_buyer' => (float) ($impact->buyer ?? 0),
                'co2e_kg' => (float) ($impact->co2e ?? 0),
                'orders' => (int) ($impact->orders ?? 0),
                'inspector_aktif' => (int) ($inspectors->aktif ?? 0),
                'inspector_total' => (int) ($inspectors->total ?? 0),
                'sertifikat_berakhir' => $expiringCerts->count(),
            ],
        ];
    }

    /**
     * Antrean tindakan Admin. "Mendesak": sengketa terbuka dan sertifikat yang berakhir ≤ 7 hari.
     * Aksi hanya diberi tautan bila halamannya sudah ada.
     *
     * @param  Collection<int, stdClass>  $expiringCerts
     * @param  Collection<int, PurchaseOrder>  $unassigned
     * @return list<array<string, mixed>>
     */
    private function adminQueue(Collection $expiringCerts, Carbon $today, Collection $unassigned): array
    {
        $items = [];

        foreach (Dispute::with('company')->where('status', 'DIBUKA')->orderBy('id_dispute')->limit(10)->get() as $dispute) {
            $items[] = [
                'title' => 'Putuskan sengketa #'.$dispute->id_dispute,
                'meta' => StatusMap::label('disputes.jenis', $dispute->jenis).' · PO-'.$dispute->purchase_orders_id_purchase_order.' · pelapor '.($dispute->company->nama ?? '–'),
                'kind' => 'Sengketa & Klaim',
                'party' => $dispute->company->nama ?? 'Sengketa',
                'urgent' => true,
                'action' => 'Putuskan',
                'url' => null,
            ];
        }

        foreach ($expiringCerts as $cert) {
            $until = Carbon::parse($cert->berlaku_sampai);
            $items[] = [
                'title' => 'Perpanjang sertifikat '.$cert->nama,
                'meta' => 'Registri Inspector · berlaku sampai '.Format::date($until),
                'kind' => 'Registri Inspector',
                'party' => $cert->nama,
                'person' => true,
                'urgent' => $until->lte($today->copy()->addDays(7)),
                'action' => 'Buka',
                'url' => Route::has('admin.inspectors.renew.form') ? route('admin.inspectors.renew.form', $cert->id_inspector) : null,
            ];
        }

        foreach ($unassigned as $order) {
            $items[] = [
                'title' => 'Tetapkan Inspector untuk PO-'.$order->id_purchase_order,
                'meta' => ($order->seller->nama ?? '–').' → '.($order->buyer->nama ?? '–').' · '.Format::kg($order->volume_kg),
                'kind' => 'Penugasan Inspeksi',
                'party' => $order->seller->nama ?? 'PO',
                'urgent' => false,
                'action' => 'Tetapkan',
                'url' => null,
            ];
        }

        foreach (Company::where('status_verifikasi', 'PENDING')->orderBy('id_company')->limit(10)->get() as $company) {
            $items[] = [
                'title' => 'Verifikasi '.$company->nama,
                'meta' => 'Verifikasi Mitra · '.ucfirst(strtolower((string) $company->tipe)).' · NIB '.($company->nib ?? '–'),
                'kind' => 'Verifikasi Mitra',
                'party' => $company->nama,
                'urgent' => false,
                'action' => 'Tinjau',
                'url' => Route::has('admin.companies.show') ? route('admin.companies.show', $company->id_company) : null,
            ];
        }

        // Yang mendesak di atas; urutan lain dipertahankan.
        usort($items, fn (array $a, array $b) => (int) $b['urgent'] <=> (int) $a['urgent']);

        return $items;
    }

    /**
     * Material terdaur ulang per kategori dari impact_logs (tanpa tanggal di DDL, jadi totalnya sepanjang waktu).
     *
     * @return array{total: float, rows: list<array<string, mixed>>}
     */
    private function materials(): array
    {
        $rows = DB::table('impact_logs')
            ->join('purchase_orders', 'purchase_orders.id_purchase_order', '=', 'impact_logs.purchase_orders_id_purchase_order')
            ->join('rfq_contracts', 'rfq_contracts.id_rfq_contract', '=', 'purchase_orders.rfq_contracts_id_rfq_contract')
            ->join('listings', 'listings.id_listings', '=', 'rfq_contracts.listings_id_listings')
            ->join('categories', 'categories.id_categories', '=', 'listings.categories_id_categories')
            ->groupBy('categories.id_categories', 'categories.nama')
            ->selectRaw('categories.nama AS nama, SUM(impact_logs.tonase_ton) AS tonase, COUNT(DISTINCT purchase_orders.id_purchase_order) AS orders, COUNT(DISTINCT purchase_orders.companies_id_seller) AS sellers')
            ->orderByDesc('tonase')
            ->limit(5)
            ->get();

        $total = (float) $rows->sum('tonase');

        return [
            'total' => $total,
            'rows' => $rows->map(fn ($row) => [
                'nama' => $row->nama,
                'tonase' => (float) $row->tonase,
                'orders' => (int) $row->orders,
                'sellers' => (int) $row->sellers,
                'share' => $total > 0 ? (int) round(100 * (float) $row->tonase / $total) : 0,
                'photo' => MaterialPhoto::forCategory($row->nama),
            ])->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function seller(int $companyId): array
    {
        $today = Carbon::today();
        $orders = PurchaseOrder::where('companies_id_seller', $companyId);

        $rfqs = RfqContract::with(['company', 'listing.category'])
            ->where('status', 'TERBUKA')
            ->whereHas('listing', fn ($q) => $q->where('companies_id_company', $companyId))
            ->orderByRaw('tenggat IS NULL, tenggat')->limit(5)->get();

        $newOrders = (clone $orders)->with('buyer')->where('status', 'MENUNGGU_KONFIRMASI')->orderBy('id_purchase_order')->limit(5)->get();

        $unpaid = Invoice::whereHas('purchaseOrder', fn ($q) => $q->where('companies_id_seller', $companyId))->where('status', 'TERBIT');
        $unpaidTotal = (float) (clone $unpaid)->sum('total');
        $nextDue = (clone $unpaid)->whereNotNull('jatuh_tempo')->min('jatuh_tempo');

        $proofs = Payment::with('invoice')
            ->where('status_verifikasi', 'MENUNGGU')
            ->whereHas('invoice.purchaseOrder', fn ($q) => $q->where('companies_id_seller', $companyId))
            ->orderBy('id_payment')->limit(5)->get();

        $published = Listing::with('category')->where('companies_id_company', $companyId)->where('status', 'PUBLISHED');

        $todo = [];
        foreach ($rfqs as $rfq) {
            $due = $rfq->tenggat ? Carbon::parse($rfq->tenggat) : null;
            $todo[] = [
                'title' => 'Balas RFQ-'.$rfq->id_rfq_contract,
                'meta' => ($rfq->company->nama ?? '–').' · '.($rfq->listing->category->nama ?? $rfq->listing->judul ?? 'Listing'),
                'party' => $rfq->company->nama ?? 'RFQ',
                'date' => $due ? $this->relativeDay($due, $today) : '–',
                'date_caption' => 'tenggat',
                'urgent' => $due !== null && $due->lte($today->copy()->addDay()->endOfDay()),
                'action' => 'Balas',
                'url' => Route::has('rfq.show') ? route('rfq.show', $rfq->id_rfq_contract) : null,
            ];
        }
        foreach ($newOrders as $order) {
            $todo[] = [
                'title' => 'Konfirmasi PO-'.$order->id_purchase_order,
                'meta' => ($order->buyer->nama ?? '–').' · '.Format::rupiah((float) $order->volume_kg * (float) $order->harga_per_kg),
                'party' => $order->buyer->nama ?? 'PO',
                'date' => 'Baru',
                'date_caption' => 'masuk',
                'urgent' => false,
                'action' => 'Konfirmasi',
                'url' => null,
            ];
        }
        foreach ($proofs as $payment) {
            $todo[] = [
                'title' => 'Periksa bukti bayar '.($payment->invoice->nomor ?? 'invoice'),
                'meta' => 'Nominal '.Format::rupiah($payment->nominal).($payment->tgl_transfer ? ' · transfer '.Format::date($payment->tgl_transfer) : ''),
                'party' => 'Pembayaran',
                'date' => $payment->tgl_transfer ? Format::dayMonth($payment->tgl_transfer) : '–',
                'date_caption' => 'diunggah',
                'urgent' => false,
                'action' => 'Periksa',
                'url' => Route::has('seller.payments.verify') ? route('seller.payments.verify', $payment->id_payment) : null,
            ];
        }
        usort($todo, fn (array $a, array $b) => (int) $b['urgent'] <=> (int) $a['urgent']);

        $rfqCount = RfqContract::where('status', 'TERBUKA')->whereHas('listing', fn ($q) => $q->where('companies_id_company', $companyId))->count();
        [$unpaidValue, $unpaidUnit] = Format::rupiahParts($unpaidTotal);
        $stok = (float) (clone $published)->sum('stok_kg');
        $newCount = (clone $orders)->where('status', 'MENUNGGU_KONFIRMASI')->count();
        $nearestRfq = $rfqs->first()?->tenggat;

        return [
            'tiles' => [
                ['label' => 'RFQ belum dibalas', 'value' => Format::number($rfqCount), 'unit' => null,
                    'caption' => $nearestRfq ? 'Tenggat terdekat '.Format::date($nearestRfq) : 'Belum ada RFQ masuk', 'tone' => $rfqCount > 0 ? 'sun' : 'zero'],
                ['label' => 'PO baru', 'value' => Format::number($newCount), 'unit' => null,
                    'caption' => $newCount > 0 ? 'Perlu dikonfirmasi' : 'Tidak ada PO baru', 'tone' => $newCount > 0 ? 'plain' : 'zero'],
                ['label' => 'Invoice belum dibayar', 'value' => $unpaidValue, 'unit' => $unpaidUnit, 'exact' => Format::rupiah($unpaidTotal),
                    'caption' => $nextDue ? 'Menunggu Buyer · jatuh tempo '.Format::date($nextDue) : 'Menunggu Buyer', 'tone' => $unpaidTotal > 0 ? 'sky' : 'zero'],
                ['label' => 'Stok listing aktif', 'value' => Format::number($stok), 'unit' => 'kg',
                    'caption' => Format::number((clone $published)->count()).' listing dipublikasikan', 'tone' => $stok > 0 ? 'green' : 'zero'],
            ],
            'todo' => $todo,
            'orders' => (clone $orders)->with('buyer')->orderByDesc('id_purchase_order')->limit(5)->get(),
            'listing' => (clone $published)->orderByDesc('id_listings')->first(),
            'demands' => DemandRequest::with(['category', 'company'])
                ->where('status', 'TERBUKA')
                ->where('companies_id_company', '!=', $companyId)
                ->orderByDesc('id_demand_request')->limit(3)->get(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function buyer(int $companyId): array
    {
        $today = Carbon::today();
        $orders = PurchaseOrder::where('companies_id_buyer', $companyId);
        $open = Invoice::with('purchaseOrder.seller')
            ->whereHas('purchaseOrder', fn ($q) => $q->where('companies_id_buyer', $companyId))
            ->where('status', 'TERBIT');
        $openTotal = (float) (clone $open)->sum('total');
        $nextDue = (clone $open)->whereNotNull('jatuh_tempo')->min('jatuh_tempo');

        $negotiating = RfqContract::with(['listing.company', 'listing.category'])
            ->where('companies_id_company', $companyId)
            ->where('status', 'NEGOSIASI')
            ->orderByRaw('tenggat IS NULL, tenggat')->limit(5)->get();

        $todo = [];
        foreach ((clone $open)->orderByRaw('jatuh_tempo IS NULL, jatuh_tempo')->limit(5)->get() as $invoice) {
            $due = $invoice->jatuh_tempo ? Carbon::parse($invoice->jatuh_tempo) : null;
            $todo[] = [
                'title' => 'Bayar '.$invoice->nomor,
                'meta' => ($invoice->purchaseOrder->seller->nama ?? '–').' · '.Format::rupiah($invoice->total),
                'party' => $invoice->purchaseOrder->seller->nama ?? 'Invoice',
                'date' => $due ? $this->relativeDay($due, $today) : '–',
                'date_caption' => 'jatuh tempo',
                'urgent' => $due !== null && $due->lte($today->copy()->addDay()),
                'action' => 'Bayar',
                'url' => Route::has('buyer.invoices.pay') ? route('buyer.invoices.pay', $invoice->id_invoice) : null,
            ];
        }
        foreach ($negotiating as $rfq) {
            $due = $rfq->tenggat ? Carbon::parse($rfq->tenggat) : null;
            $todo[] = [
                'title' => 'Tanggapi balasan RFQ-'.$rfq->id_rfq_contract,
                'meta' => ($rfq->listing->company->nama ?? '–').' · '.($rfq->listing->category->nama ?? $rfq->listing->judul ?? 'Listing'),
                'party' => $rfq->listing->company->nama ?? 'RFQ',
                'date' => $due ? $this->relativeDay($due, $today) : '–',
                'date_caption' => 'tenggat',
                'urgent' => $due !== null && $due->lte($today->copy()->addDay()->endOfDay()),
                'action' => 'Tanggapi',
                'url' => Route::has('rfq.show') ? route('rfq.show', $rfq->id_rfq_contract) : null,
            ];
        }
        usort($todo, fn (array $a, array $b) => (int) $b['urgent'] <=> (int) $a['urgent']);

        [$openValue, $openUnit] = Format::rupiahParts($openTotal);
        $rfqOpen = RfqContract::where('companies_id_company', $companyId)->whereIn('status', ['TERBUKA', 'NEGOSIASI'])->count();
        $shipping = (clone $orders)->where('status', 'DALAM_PENGIRIMAN')->count();
        $done = (clone $orders)->where('status', 'SELESAI')->count();

        return [
            'tiles' => [
                ['label' => 'Tagihan terbuka', 'value' => $openValue, 'unit' => $openUnit, 'exact' => Format::rupiah($openTotal),
                    'caption' => $nextDue ? 'Jatuh tempo '.Format::date($nextDue) : 'Tidak ada tagihan', 'tone' => $openTotal > 0 ? 'sun' : 'zero'],
                ['label' => 'RFQ terbuka', 'value' => Format::number($rfqOpen), 'unit' => null,
                    'caption' => $negotiating->count() > 0 ? Format::number($negotiating->count()).' dibalas Seller' : 'Menunggu balasan Seller', 'tone' => $rfqOpen > 0 ? 'sky' : 'zero'],
                ['label' => 'Dalam pengiriman', 'value' => Format::number($shipping), 'unit' => null,
                    'caption' => $shipping > 0 ? 'Order sedang dikirim' : 'Tidak ada pengiriman', 'tone' => $shipping > 0 ? 'plain' : 'zero'],
                ['label' => 'Order selesai', 'value' => Format::number($done), 'unit' => null,
                    'caption' => 'Barang diterima dan dikonfirmasi', 'tone' => $done > 0 ? 'green' : 'zero'],
            ],
            'todo' => $todo,
            'orders' => (clone $orders)->with('seller')->orderByDesc('id_purchase_order')->limit(5)->get(),
            'demand' => DemandRequest::with('category')->where('companies_id_company', $companyId)->where('status', 'TERBUKA')->orderByDesc('id_demand_request')->first(),
            'shipments' => (clone $orders)->with('seller')->where('status', 'DALAM_PENGIRIMAN')->orderByDesc('id_purchase_order')->limit(3)->get(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function inspector(int $userId): array
    {
        $today = Carbon::today();
        $mine = Inspection::where('users_id_user', $userId);
        $active = (clone $mine)->with(['purchaseOrder.seller', 'purchaseOrder.buyer'])
            ->whereIn('status', ['DITUGASKAN', 'DIPROSES'])->orderByRaw('jadwal IS NULL, jadwal')->limit(10)->get();

        $materials = $this->orderMaterials($active->pluck('purchase_orders_id_purchase_order')->all());

        $docs = DB::table('document_verifications')
            ->leftJoin('purchase_orders', 'purchase_orders.id_purchase_order', '=', 'document_verifications.purchase_orders_id_purchase_order')
            ->leftJoin('companies', 'companies.id_company', '=', 'purchase_orders.companies_id_seller')
            ->where('document_verifications.users_id_user', $userId)
            ->where('document_verifications.status', 'MENUNGGU')
            ->orderBy('document_verifications.id_document_verification')
            ->limit(5)
            ->get(['document_verifications.id_document_verification', 'document_verifications.purchase_orders_id_purchase_order AS po', 'companies.nama AS seller']);

        $todayCount = (clone $mine)->whereIn('status', ['DITUGASKAN', 'DIPROSES'])->whereDate('jadwal', $today)->count();
        $processing = (clone $mine)->where('status', 'DIPROSES')->count();
        $done = (clone $mine)->where('status', 'SELESAI')->count();
        $next = $active->first();

        return [
            'tiles' => [
                ['label' => 'Inspeksi hari ini', 'value' => Format::number($todayCount), 'unit' => null,
                    'caption' => $todayCount > 0 && $next?->jadwal ? StatusMap::label('inspections.mode_verifikasi', $next->mode_verifikasi).', '.Carbon::parse($next->jadwal)->format('H.i').' WIB' : 'Tidak ada jadwal hari ini', 'tone' => $todayCount > 0 ? 'sun' : 'zero'],
                ['label' => 'Verifikasi dokumen', 'value' => Format::number($docs->count()), 'unit' => $docs->count() > 0 ? 'berkas' : null,
                    'caption' => $docs->count() > 0 ? 'Menunggu diperiksa' : 'Tidak ada dokumen', 'tone' => $docs->count() > 0 ? 'plain' : 'zero'],
                ['label' => 'Sedang diproses', 'value' => Format::number($processing), 'unit' => null,
                    'caption' => 'Laporan belum difinalisasi', 'tone' => $processing > 0 ? 'plain' : 'zero'],
                ['label' => 'Inspeksi selesai', 'value' => Format::number($done), 'unit' => null,
                    'caption' => 'Sepanjang penugasan', 'tone' => $done > 0 ? 'green' : 'zero'],
            ],
            'schedule' => $active->map(fn (Inspection $inspection) => [
                'inspection' => $inspection,
                'material' => $materials[$inspection->purchase_orders_id_purchase_order] ?? null,
            ])->all(),
            'docs' => $docs,
            'next' => $next ? ['inspection' => $next, 'material' => $materials[$next->purchase_orders_id_purchase_order] ?? null] : null,
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

    private function relativeDay(Carbon $date, Carbon $today): string
    {
        $day = $date->copy()->startOfDay();

        return match (true) {
            $day->equalTo($today) => 'Hari ini',
            $day->equalTo($today->copy()->addDay()) => 'Besok',
            $day->lessThan($today) => 'Lewat '.Format::dayMonth($date),
            default => Format::dayMonth($date),
        };
    }
}

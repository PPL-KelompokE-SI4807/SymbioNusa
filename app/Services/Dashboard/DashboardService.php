<?php

namespace App\Services\Dashboard;

use App\Models\Company;
use App\Models\DemandRequest;
use App\Models\Dispute;
use App\Models\ImpactLog;
use App\Models\Inspection;
use App\Models\Invoice;
use App\Models\Listing;
use App\Models\PurchaseOrder;
use App\Models\RfqContract;
use App\Support\Format;
use App\Support\StatusMap;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use stdClass;

/**
 * FND-04 (SYM-28): data dasbor per peran. Semua angka dihitung dari data transaksi saat diminta;
 * tidak ada angka yang dikarang. Elemen prototipe yang datanya belum ada di DDL (target DLH,
 * SLA, perbandingan bulanan) sengaja tidak ditampilkan.
 */
class DashboardService
{
    /** Status order yang masih berjalan (bukan akhir). */
    public const ACTIVE_ORDER_STATUSES = [
        'MENUNGGU_KONFIRMASI', 'DIKONFIRMASI', 'INSPEKSI', 'LOLOS_INSPEKSI',
        'INVOICED', 'DIBAYAR', 'DALAM_PENGIRIMAN', 'DITERIMA', 'SENGKETA',
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
            ->get(['users.nama', 'inspector_profiles.berlaku_sampai']);

        $orderCounts = PurchaseOrder::query()
            ->selectRaw('status, COUNT(*) AS jumlah')
            ->groupBy('status')
            ->pluck('jumlah', 'status');

        $count = fn (string ...$statuses): int => (int) collect($statuses)->sum(fn (string $s) => $orderCounts[$s] ?? 0);

        $flow = [
            ['label' => 'RFQ Terbuka', 'count' => RfqContract::whereIn('status', ['TERBUKA', 'NEGOSIASI'])->count(), 'track' => false],
            ['label' => 'Menunggu Konfirmasi', 'count' => $count('MENUNGGU_KONFIRMASI'), 'track' => true],
            ['label' => 'Dikonfirmasi', 'count' => $count('DIKONFIRMASI'), 'track' => true],
            ['label' => 'Inspeksi', 'count' => $count('INSPEKSI', 'LOLOS_INSPEKSI'), 'track' => true],
            ['label' => 'Menunggu Pembayaran', 'count' => $count('INVOICED'), 'track' => true],
            ['label' => 'Dibayar', 'count' => $count('DIBAYAR'), 'track' => true],
            ['label' => 'Dalam Pengiriman', 'count' => $count('DALAM_PENGIRIMAN', 'DITERIMA'), 'track' => true],
            ['label' => 'Selesai', 'count' => $count('SELESAI'), 'track' => false],
        ];

        // Tahap berjalan dengan order terbanyak ditandai sebagai tahap yang perlu perhatian.
        $tracked = array_filter($flow, fn (array $stage) => $stage['track'] && $stage['count'] > 0);
        $stuck = $tracked === [] ? null : array_reduce($tracked, fn (?array $carry, array $stage) => $carry === null || $stage['count'] > $carry['count'] ? $stage : $carry);

        $branches = [
            ['label' => 'Ditolak', 'count' => $count('DITOLAK'), 'variant' => 'danger'],
            ['label' => 'Gagal Inspeksi', 'count' => $count('GAGAL_INSPEKSI'), 'variant' => 'danger'],
            ['label' => 'Sengketa', 'count' => $count('SENGKETA'), 'variant' => 'danger'],
            ['label' => 'Dibatalkan', 'count' => $count('DIBATALKAN'), 'variant' => 'neutral'],
        ];

        $impact = ImpactLog::query()->selectRaw(
            'COALESCE(SUM(tonase_ton), 0) AS tonase, COALESCE(SUM(hemat_seller_rp), 0) AS seller, COALESCE(SUM(hemat_buyer_rp), 0) AS buyer, COALESCE(SUM(co2e_net_kg), 0) AS co2e, COUNT(*) AS orders'
        )->first();

        return [
            'impact' => [
                'tonase' => (float) ($impact->tonase ?? 0),
                'hemat_seller' => (float) ($impact->seller ?? 0),
                'hemat_buyer' => (float) ($impact->buyer ?? 0),
                'co2e_kg' => (float) ($impact->co2e ?? 0),
                'orders' => (int) ($impact->orders ?? 0),
            ],
            'inspectors' => [
                'aktif' => (int) ($inspectors->aktif ?? 0),
                'total' => (int) ($inspectors->total ?? 0),
                'segera_berakhir' => $expiringCerts->count(),
            ],
            'flow' => $flow,
            'stuck' => $stuck,
            'branches' => $branches,
            'queue' => $this->adminQueue($expiringCerts, $today),
            'modules' => [
                ['label' => 'Listing dipublikasikan', 'module' => 'Katalog', 'count' => Listing::where('status', 'PUBLISHED')->count()],
                ['label' => 'RFQ terbuka atau negosiasi', 'module' => 'Transaksi', 'count' => RfqContract::whereIn('status', ['TERBUKA', 'NEGOSIASI'])->count()],
                ['label' => 'Order berjalan', 'module' => 'Transaksi', 'count' => $count(...self::ACTIVE_ORDER_STATUSES)],
                ['label' => 'Invoice menunggu pembayaran', 'module' => 'Pembayaran', 'count' => Invoice::where('status', 'TERBIT')->count()],
                ['label' => 'Pengiriman dalam perjalanan', 'module' => 'Logistik', 'count' => DB::table('shipments_manifests')->where('status', 'DALAM_PERJALANAN')->count()],
                ['label' => 'Kebutuhan Buyer terbuka', 'module' => 'Kebutuhan', 'count' => DemandRequest::where('status', 'TERBUKA')->count()],
                ['label' => 'Inspeksi berjalan', 'module' => 'Inspeksi', 'count' => Inspection::whereIn('status', ['DITUGASKAN', 'DIPROSES'])->count()],
                ['label' => 'Perusahaan terdaftar', 'module' => 'Mitra', 'count' => Company::count()],
            ],
        ];
    }

    /**
     * Antrean tindakan Admin. Prioritas "Mendesak": sengketa terbuka dan sertifikat yang berakhir ≤ 7 hari.
     *
     * @param  Collection<int, stdClass>  $expiringCerts
     * @return list<array{tindakan: string, jenis: string, rincian: string, mendesak: bool}>
     */
    private function adminQueue(Collection $expiringCerts, Carbon $today): array
    {
        $items = [];

        foreach (Dispute::with('company')->where('status', 'DIBUKA')->orderBy('id_dispute')->limit(10)->get() as $dispute) {
            $items[] = [
                'tindakan' => 'Putuskan sengketa #'.$dispute->id_dispute,
                'jenis' => 'Sengketa & Klaim',
                'rincian' => StatusMap::label('disputes.jenis', $dispute->jenis).' · PO-'.$dispute->purchase_orders_id_purchase_order.' · pelapor '.($dispute->company->nama ?? '–'),
                'mendesak' => true,
            ];
        }

        foreach ($expiringCerts as $cert) {
            $until = Carbon::parse($cert->berlaku_sampai);
            $items[] = [
                'tindakan' => 'Perpanjang sertifikat Inspector '.$cert->nama,
                'jenis' => 'Registri Inspector',
                'rincian' => 'Berlaku sampai '.Format::date($until),
                'mendesak' => $until->lte($today->copy()->addDays(7)),
            ];
        }

        $unassigned = PurchaseOrder::with(['seller', 'buyer'])
            ->where('status', 'DIKONFIRMASI')
            ->whereDoesntHave('inspections', fn ($q) => $q->whereIn('status', ['DITUGASKAN', 'DIPROSES', 'SELESAI']))
            ->orderBy('id_purchase_order')->limit(10)->get();

        foreach ($unassigned as $order) {
            $items[] = [
                'tindakan' => 'Tugaskan Inspector untuk PO-'.$order->id_purchase_order,
                'jenis' => 'Penugasan Inspeksi',
                'rincian' => ($order->seller->nama ?? '–').' → '.($order->buyer->nama ?? '–').' · '.Format::kg($order->volume_kg),
                'mendesak' => false,
            ];
        }

        foreach (Company::where('status_verifikasi', 'PENDING')->orderBy('id_company')->limit(10)->get() as $company) {
            $items[] = [
                'tindakan' => 'Verifikasi '.$company->nama,
                'jenis' => 'Verifikasi Mitra',
                'rincian' => 'NIB '.($company->nib ?? '–').' · '.ucfirst(strtolower((string) $company->tipe)),
                'mendesak' => false,
            ];
        }

        return $items;
    }

    /**
     * @return array<string, mixed>
     */
    public function seller(int $companyId): array
    {
        $orders = PurchaseOrder::where('companies_id_seller', $companyId);
        $unpaid = Invoice::whereHas('purchaseOrder', fn ($q) => $q->where('companies_id_seller', $companyId))->where('status', 'TERBIT');

        return [
            'kpis' => [
                ['label' => 'Listing dipublikasikan', 'value' => Format::number(Listing::where('companies_id_company', $companyId)->where('status', 'PUBLISHED')->count())],
                ['label' => 'Stok tersedia', 'value' => Format::kg(Listing::where('companies_id_company', $companyId)->where('status', 'PUBLISHED')->sum('stok_kg'))],
                ['label' => 'Order berjalan', 'value' => Format::number((clone $orders)->whereIn('status', self::ACTIVE_ORDER_STATUSES)->count())],
                ['label' => 'Invoice menunggu pembayaran', 'value' => Format::rupiahShort((clone $unpaid)->sum('total')), 'exact' => Format::rupiah((clone $unpaid)->sum('total')), 'caption' => Format::number((clone $unpaid)->count()).' invoice'],
            ],
            'listings' => Listing::where('companies_id_company', $companyId)->orderByDesc('id_listings')->limit(5)->get(),
            'orders' => (clone $orders)->with('buyer')->orderByDesc('id_purchase_order')->limit(5)->get(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function buyer(int $companyId): array
    {
        $orders = PurchaseOrder::where('companies_id_buyer', $companyId);
        $invoices = Invoice::whereHas('purchaseOrder', fn ($q) => $q->where('companies_id_buyer', $companyId));

        return [
            'kpis' => [
                ['label' => 'Kebutuhan terbuka', 'value' => Format::number(DemandRequest::where('companies_id_company', $companyId)->where('status', 'TERBUKA')->count())],
                ['label' => 'Order berjalan', 'value' => Format::number((clone $orders)->whereIn('status', self::ACTIVE_ORDER_STATUSES)->count())],
                ['label' => 'Tagihan menunggu pembayaran', 'value' => Format::rupiahShort((clone $invoices)->where('status', 'TERBIT')->sum('total')), 'exact' => Format::rupiah((clone $invoices)->where('status', 'TERBIT')->sum('total'))],
                ['label' => 'Total pembelian dibayar', 'value' => Format::rupiahShort((clone $invoices)->where('status', 'DIBAYAR')->sum('total')), 'exact' => Format::rupiah((clone $invoices)->where('status', 'DIBAYAR')->sum('total'))],
            ],
            'demands' => DemandRequest::with('category')->where('companies_id_company', $companyId)->orderByDesc('id_demand_request')->limit(5)->get(),
            'orders' => (clone $orders)->with('seller')->orderByDesc('id_purchase_order')->limit(5)->get(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function inspector(int $userId): array
    {
        $mine = Inspection::where('users_id_user', $userId);

        return [
            'kpis' => [
                ['label' => 'Ditugaskan', 'value' => Format::number((clone $mine)->where('status', 'DITUGASKAN')->count())],
                ['label' => 'Diproses', 'value' => Format::number((clone $mine)->where('status', 'DIPROSES')->count())],
                ['label' => 'Selesai', 'value' => Format::number((clone $mine)->where('status', 'SELESAI')->count())],
                ['label' => 'Total penugasan', 'value' => Format::number((clone $mine)->count())],
            ],
            'active' => (clone $mine)->with(['purchaseOrder.seller', 'purchaseOrder.buyer'])->whereIn('status', ['DITUGASKAN', 'DIPROSES'])->orderBy('jadwal')->limit(10)->get(),
            'done' => (clone $mine)->with('purchaseOrder')->where('status', 'SELESAI')->orderByDesc('id_inspection')->limit(5)->get(),
        ];
    }
}

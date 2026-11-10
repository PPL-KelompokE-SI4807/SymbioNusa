<?php

namespace App\Livewire;

use App\Models\Company;
use App\Models\DemandRequest;
use App\Models\Dispute;
use App\Models\ImpactLog;
use App\Models\Inspection;
use App\Models\Invoice;
use App\Models\Listing;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class DashboardOverview extends Component
{
    public function render()
    {
        $user = Auth::user();
        $role = strtoupper($user->role ?? 'BUYER');

        $metrics = [];
        $impactMetrics = [];
        $tables = [];
        $errorMessage = null;

        try {
            if ($role === 'ADMIN') {
                $metrics = [
                    [
                        'key' => 'pending_companies',
                        'label' => 'Verifikasi Perusahaan Pending',
                        'value' => Company::where('status_verifikasi', 'PENDING')->count(),
                        'badge' => 'PBI-08 Profil Perusahaan',
                        'color' => 'amber',
                    ],
                    [
                        'key' => 'total_listings',
                        'label' => 'Listing Aktif',
                        'value' => Listing::where('status', 'PUBLISHED')->count(),
                        'badge' => 'PBI-09 Katalog Material',
                        'color' => 'blue',
                    ],
                    [
                        'key' => 'active_orders',
                        'label' => 'Pesanan Berjalan',
                        'value' => PurchaseOrder::whereNotIn('status', ['SELESAI', 'DIBATALKAN', 'DITOLAK'])->count(),
                        'badge' => 'PBI-05 Order & Transaksi',
                        'color' => 'purple',
                    ],
                    [
                        'key' => 'active_inspections',
                        'label' => 'Inspeksi Berjalan',
                        'value' => Inspection::whereIn('status', ['DITUGASKAN', 'DIPROSES'])->count(),
                        'badge' => 'PBI-18 Penugasan Inspeksi',
                        'color' => 'teal',
                    ],
                    [
                        'key' => 'open_disputes',
                        'label' => 'Sengketa Terbuka',
                        'value' => Dispute::where('status', 'DIBUKA')->count(),
                        'badge' => 'PBI-13 Mediasi Sengketa',
                        'color' => 'rose',
                    ],
                    [
                        'key' => 'total_companies',
                        'label' => 'Total Perusahaan Terdaftar',
                        'value' => Company::count(),
                        'badge' => 'Ekosistem SymbioNusa',
                        'color' => 'emerald',
                    ],
                ];

                $totalTonase = (float) ImpactLog::sum('tonase_ton');
                $hematSeller = (float) ImpactLog::sum('hemat_seller_rp');
                $hematBuyer = (float) ImpactLog::sum('hemat_buyer_rp');
                $totalCo2e = (float) ImpactLog::sum('co2e_net_kg');

                $impactMetrics = [
                    'tonase' => number_format($totalTonase, 2, ',', '.').' Ton',
                    'penghematan' => 'Rp '.number_format($hematSeller + $hematBuyer, 0, ',', '.'),
                    'co2e' => number_format($totalCo2e, 2, ',', '.').' kg CO₂e',
                ];

                $tables = [
                    'pending_companies' => Company::where('status_verifikasi', 'PENDING')->latest('id_company')->take(5)->get(),
                    'active_disputes' => Dispute::with(['purchaseOrder', 'company'])->where('status', 'DIBUKA')->latest('id_dispute')->take(5)->get(),
                    'recent_orders' => PurchaseOrder::with(['seller', 'buyer'])->latest('id_purchase_order')->take(5)->get(),
                ];
            } elseif ($role === 'SELLER') {
                $companyId = $user->id_company;

                if ($companyId) {
                    $metrics = [
                        [
                            'key' => 'my_listings',
                            'label' => 'Listing Aktif Saya',
                            'value' => Listing::where('companies_id_company', $companyId)->where('status', 'PUBLISHED')->count(),
                            'badge' => 'PBI-09 Listing Sisa',
                            'color' => 'blue',
                        ],
                        [
                            'key' => 'total_stock',
                            'label' => 'Total Stok Tersedia',
                            'value' => number_format((float) Listing::where('companies_id_company', $companyId)->where('status', 'PUBLISHED')->sum('stok_kg'), 0, ',', '.').' kg',
                            'badge' => 'Inventori Material',
                            'color' => 'teal',
                        ],
                        [
                            'key' => 'active_orders',
                            'label' => 'Pesanan Masuk Berjalan',
                            'value' => PurchaseOrder::where('companies_id_seller', $companyId)->whereNotIn('status', ['SELESAI', 'DIBATALKAN', 'DITOLAK'])->count(),
                            'badge' => 'PBI-05 Purchase Order',
                            'color' => 'purple',
                        ],
                        [
                            'key' => 'pending_invoices',
                            'label' => 'Invoice Menunggu Pembayaran',
                            'value' => Invoice::whereHas('purchaseOrder', fn ($q) => $q->where('companies_id_seller', $companyId))->where('status', 'TERBIT')->count(),
                            'badge' => 'PBI-06 Invoice',
                            'color' => 'amber',
                        ],
                        [
                            'key' => 'total_revenue',
                            'label' => 'Total Pendapatan Terverifikasi',
                            'value' => 'Rp '.number_format((float) Invoice::whereHas('purchaseOrder', fn ($q) => $q->where('companies_id_seller', $companyId))->where('status', 'DIBAYAR')->sum('total'), 0, ',', '.'),
                            'badge' => 'PBI-07 Pembayaran',
                            'color' => 'emerald',
                        ],
                    ];

                    $tables = [
                        'my_listings' => Listing::where('companies_id_company', $companyId)->latest('id_listings')->take(5)->get(),
                        'my_orders' => PurchaseOrder::with(['buyer'])->where('companies_id_seller', $companyId)->latest('id_purchase_order')->take(5)->get(),
                    ];
                }
            } elseif ($role === 'BUYER') {
                $companyId = $user->id_company;

                if ($companyId) {
                    $metrics = [
                        [
                            'key' => 'my_demands',
                            'label' => 'Kebutuhan Bahan Baku Aktif',
                            'value' => DemandRequest::where('companies_id_company', $companyId)->where('status', 'TERBUKA')->count(),
                            'badge' => 'PBI-14 Demand Request',
                            'color' => 'blue',
                        ],
                        [
                            'key' => 'active_orders',
                            'label' => 'Pesanan Pembelian Aktif',
                            'value' => PurchaseOrder::where('companies_id_buyer', $companyId)->whereNotIn('status', ['SELESAI', 'DIBATALKAN', 'DITOLAK'])->count(),
                            'badge' => 'PBI-05 Purchase Order',
                            'color' => 'purple',
                        ],
                        [
                            'key' => 'pending_payments',
                            'label' => 'Tagihan Menunggu Pembayaran',
                            'value' => Invoice::whereHas('purchaseOrder', fn ($q) => $q->where('companies_id_buyer', $companyId))->where('status', 'TERBIT')->count(),
                            'badge' => 'PBI-06 & PBI-07 Tagihan',
                            'color' => 'amber',
                        ],
                        [
                            'key' => 'total_spending',
                            'label' => 'Total Pembelian Selesai',
                            'value' => 'Rp '.number_format((float) Invoice::whereHas('purchaseOrder', fn ($q) => $q->where('companies_id_buyer', $companyId))->where('status', 'DIBAYAR')->sum('total'), 0, ',', '.'),
                            'badge' => 'Realisasi Bahan Baku',
                            'color' => 'emerald',
                        ],
                    ];

                    $tables = [
                        'my_demands' => DemandRequest::where('companies_id_company', $companyId)->latest('id_demand_request')->take(5)->get(),
                        'my_orders' => PurchaseOrder::with(['seller'])->where('companies_id_buyer', $companyId)->latest('id_purchase_order')->take(5)->get(),
                    ];
                }
            } elseif ($role === 'INSPECTOR') {
                $inspectorId = $user->id_user;

                $metrics = [
                    [
                        'key' => 'assigned_inspections',
                        'label' => 'Inspeksi Ditugaskan',
                        'value' => Inspection::where('users_id_user', $inspectorId)->where('status', 'DITUGASKAN')->count(),
                        'badge' => 'PBI-18 Penugasan',
                        'color' => 'amber',
                    ],
                    [
                        'key' => 'processing_inspections',
                        'label' => 'Sedang Dalam Proses',
                        'value' => Inspection::where('users_id_user', $inspectorId)->where('status', 'DIPROSES')->count(),
                        'badge' => 'PBI-17 & 19 Verifikasi Mutu',
                        'color' => 'blue',
                    ],
                    [
                        'key' => 'completed_inspections',
                        'label' => 'Inspeksi Selesai',
                        'value' => Inspection::where('users_id_user', $inspectorId)->where('status', 'SELESAI')->count(),
                        'badge' => 'Laporan Mutu Diterbitkan',
                        'color' => 'emerald',
                    ],
                    [
                        'key' => 'total_inspections',
                        'label' => 'Total Riwayat Inspeksi',
                        'value' => Inspection::where('users_id_user', $inspectorId)->count(),
                        'badge' => 'Rekam Jejak Inspector',
                        'color' => 'purple',
                    ],
                ];

                $tables = [
                    'assigned_inspections' => Inspection::with(['purchaseOrder.seller', 'purchaseOrder.buyer'])
                        ->where('users_id_user', $inspectorId)
                        ->whereIn('status', ['DITUGASKAN', 'DIPROSES'])
                        ->orderBy('jadwal', 'asc')
                        ->take(5)
                        ->get(),
                    'completed_inspections' => Inspection::with(['purchaseOrder'])
                        ->where('users_id_user', $inspectorId)
                        ->where('status', 'SELESAI')
                        ->latest('id_inspection')
                        ->take(5)
                        ->get(),
                ];
            }
        } catch (\Throwable $e) {
            Log::error('DashboardOverview query error: '.$e->getMessage(), [
                'exception' => $e,
                'role' => $role,
                'user_id' => $user?->id_user,
            ]);
            $errorMessage = 'Gagal memuat sebagian data metrik: '.$e->getMessage();
        }

        return view('livewire.dashboard-overview', compact(
            'user',
            'role',
            'metrics',
            'impactMetrics',
            'tables',
            'errorMessage'
        ));
    }
}

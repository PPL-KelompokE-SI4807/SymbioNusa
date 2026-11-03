<?php

namespace App\Livewire;

use App\Models\Company;
use App\Models\DemandRequest;
use App\Models\Dispute;
use App\Models\Inspection;
use App\Models\Invoice;
use App\Models\Listing;
use App\Models\PurchaseOrder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class DashboardOverview extends Component
{
    public function render()
    {
        $user = Auth::user();
        $role = strtoupper($user->role ?? 'BUYER');

        $metrics = [];
        $recentData = new Collection;

        try {
            if ($role === 'ADMIN') {
                $metrics = [
                    ['label' => 'Total Perusahaan', 'value' => Company::count(), 'color' => 'bg-emerald-50 text-emerald-700 border-emerald-200'],
                    ['label' => 'Verifikasi Pending', 'value' => Company::where('status_verifikasi', 'PENDING')->count(), 'color' => 'bg-amber-50 text-amber-700 border-amber-200'],
                    ['label' => 'Total Listing', 'value' => Listing::count(), 'color' => 'bg-blue-50 text-blue-700 border-blue-200'],
                    ['label' => 'Order Aktif', 'value' => PurchaseOrder::whereNotIn('status', ['SELESAI', 'DIBATALKAN', 'DITOLAK'])->count(), 'color' => 'bg-purple-50 text-purple-700 border-purple-200'],
                    ['label' => 'Sengketa Terbuka', 'value' => Dispute::where('status', 'TERBUKA')->count(), 'color' => 'bg-rose-50 text-rose-700 border-rose-200'],
                ];
                $recentData = Company::latest('id_company')->take(5)->get();
            } elseif ($role === 'SELLER') {
                $companyId = $user->id_company;
                $metrics = [
                    ['label' => 'Listing Saya', 'value' => Listing::where('companies_id_company', $companyId)->count(), 'color' => 'bg-blue-50 text-blue-700 border-blue-200'],
                    ['label' => 'Order Aktif', 'value' => PurchaseOrder::where('companies_id_seller', $companyId)->whereNotIn('status', ['SELESAI', 'DIBATALKAN', 'DITOLAK'])->count(), 'color' => 'bg-purple-50 text-purple-700 border-purple-200'],
                    ['label' => 'Invoice Terbit', 'value' => Invoice::whereHas('purchaseOrder', fn ($q) => $q->where('companies_id_seller', $companyId))->where('status', 'TERBIT')->count(), 'color' => 'bg-amber-50 text-amber-700 border-amber-200'],
                    ['label' => 'Total Pendapatan', 'value' => 'Rp '.number_format(Invoice::whereHas('purchaseOrder', fn ($q) => $q->where('companies_id_seller', $companyId))->where('status', 'DIBAYAR')->sum('total'), 0, ',', '.'), 'color' => 'bg-emerald-50 text-emerald-700 border-emerald-200'],
                ];
                $recentData = Listing::where('companies_id_company', $companyId)->latest('id_listings')->take(5)->get();
            } elseif ($role === 'BUYER') {
                $companyId = $user->id_company;
                $metrics = [
                    ['label' => 'Permintaan Kebutuhan', 'value' => DemandRequest::where('companies_id_company', $companyId)->count(), 'color' => 'bg-blue-50 text-blue-700 border-blue-200'],
                    ['label' => 'Order Aktif', 'value' => PurchaseOrder::where('companies_id_buyer', $companyId)->whereNotIn('status', ['SELESAI', 'DIBATALKAN', 'DITOLAK'])->count(), 'color' => 'bg-purple-50 text-purple-700 border-purple-200'],
                    ['label' => 'Tagihan Pending', 'value' => Invoice::whereHas('purchaseOrder', fn ($q) => $q->where('companies_id_buyer', $companyId))->where('status', 'TERBIT')->count(), 'color' => 'bg-amber-50 text-amber-700 border-amber-200'],
                    ['label' => 'Total Pembelian', 'value' => 'Rp '.number_format(Invoice::whereHas('purchaseOrder', fn ($q) => $q->where('companies_id_buyer', $companyId))->where('status', 'DIBAYAR')->sum('total'), 0, ',', '.'), 'color' => 'bg-emerald-50 text-emerald-700 border-emerald-200'],
                ];
                $recentData = DemandRequest::where('companies_id_company', $companyId)->latest('id_demand_request')->take(5)->get();
            } elseif ($role === 'INSPECTOR') {
                $metrics = [
                    ['label' => 'Inspeksi Ditugaskan', 'value' => Inspection::where('id_inspector', $user->id_user)->count(), 'color' => 'bg-blue-50 text-blue-700 border-blue-200'],
                    ['label' => 'Inspeksi Menunggu', 'value' => Inspection::where('id_inspector', $user->id_user)->where('status', 'MENUNGGU')->count(), 'color' => 'bg-amber-50 text-amber-700 border-amber-200'],
                    ['label' => 'Inspeksi Selesai', 'value' => Inspection::where('id_inspector', $user->id_user)->where('status', 'SELESAI')->count(), 'color' => 'bg-emerald-50 text-emerald-700 border-emerald-200'],
                ];
                $recentData = Inspection::with(['purchaseOrder'])->where('id_inspector', $user->id_user)->latest('id_inspection')->take(5)->get();
            }
        } catch (\Throwable $e) {
            $metrics = [
                ['label' => 'Status Sistem', 'value' => 'Normal (Fallback)', 'color' => 'bg-gray-50 text-gray-700 border-gray-200'],
            ];
            $recentData = new Collection;
        }

        return view('livewire.dashboard-overview', compact('user', 'role', 'metrics', 'recentData'));
    }
}

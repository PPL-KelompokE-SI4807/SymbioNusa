<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\DemandRequest;
use App\Models\Dispute;
use App\Models\Inspection;
use App\Models\Invoice;
use App\Models\Listing;
use App\Models\PurchaseOrder;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }

        $role = strtoupper($user->role ?? 'BUYER');

        return match ($role) {
            'ADMIN' => $this->adminDashboard($user),
            'SELLER' => $this->sellerDashboard($user),
            'BUYER' => $this->buyerDashboard($user),
            'INSPECTOR' => $this->inspectorDashboard($user),
            default => view('dashboard.generic', compact('user')),
        };
    }

    private function adminDashboard(User $user)
    {
        $metrics = [
            'total_companies' => 0,
            'pending_companies' => 0,
            'total_listings' => 0,
            'active_orders' => 0,
            'active_disputes' => 0,
        ];
        $recentCompanies = new Collection;
        $recentDisputes = new Collection;

        try {
            $metrics['total_companies'] = Company::count();
            $metrics['pending_companies'] = Company::where('status_verifikasi', 'PENDING')->count();
            $metrics['total_listings'] = Listing::count();
            $metrics['active_orders'] = PurchaseOrder::whereNotIn('status', ['SELESAI', 'DIBATALKAN', 'DITOLAK'])->count();
            $metrics['active_disputes'] = Dispute::where('status', 'TERBUKA')->count();
            $recentCompanies = Company::latest('id_company')->take(5)->get();
            $recentDisputes = Dispute::with(['purchaseOrder'])->latest('id_dispute')->take(5)->get();
        } catch (\Throwable $e) {
            // Defensive fallback when database query fails
        }

        return view('dashboard.admin', compact('user', 'metrics', 'recentCompanies', 'recentDisputes'));
    }

    private function sellerDashboard(User $user)
    {
        $companyId = $user->id_company;
        $metrics = [
            'my_listings' => 0,
            'active_orders' => 0,
            'pending_invoices' => 0,
            'total_revenue' => 0,
        ];
        $myListings = new Collection;
        $myOrders = new Collection;

        try {
            if ($companyId) {
                $metrics['my_listings'] = Listing::where('companies_id_company', $companyId)->count();
                $metrics['active_orders'] = PurchaseOrder::where('companies_id_seller', $companyId)->whereNotIn('status', ['SELESAI', 'DIBATALKAN', 'DITOLAK'])->count();
                $metrics['pending_invoices'] = Invoice::whereHas('purchaseOrder', function ($q) use ($companyId) {
                    $q->where('companies_id_seller', $companyId);
                })->where('status', 'TERBIT')->count();
                $metrics['total_revenue'] = Invoice::whereHas('purchaseOrder', function ($q) use ($companyId) {
                    $q->where('companies_id_seller', $companyId);
                })->where('status', 'DIBAYAR')->sum('total');
                $myListings = Listing::where('companies_id_company', $companyId)->latest('id_listings')->take(5)->get();
                $myOrders = PurchaseOrder::where('companies_id_seller', $companyId)->latest('id_purchase_order')->take(5)->get();
            }
        } catch (\Throwable $e) {
            // Defensive fallback when database query fails
        }

        return view('dashboard.seller', compact('user', 'metrics', 'myListings', 'myOrders'));
    }

    private function buyerDashboard(User $user)
    {
        $companyId = $user->id_company;
        $metrics = [
            'my_demand_requests' => 0,
            'active_orders' => 0,
            'pending_payments' => 0,
            'total_spending' => 0,
        ];
        $myDemands = new Collection;
        $myOrders = new Collection;

        try {
            if ($companyId) {
                $metrics['my_demand_requests'] = DemandRequest::where('companies_id_company', $companyId)->count();
                $metrics['active_orders'] = PurchaseOrder::where('companies_id_buyer', $companyId)->whereNotIn('status', ['SELESAI', 'DIBATALKAN', 'DITOLAK'])->count();
                $metrics['pending_payments'] = Invoice::whereHas('purchaseOrder', function ($q) use ($companyId) {
                    $q->where('companies_id_buyer', $companyId);
                })->where('status', 'TERBIT')->count();
                $metrics['total_spending'] = Invoice::whereHas('purchaseOrder', function ($q) use ($companyId) {
                    $q->where('companies_id_buyer', $companyId);
                })->where('status', 'DIBAYAR')->sum('total');
                $myDemands = DemandRequest::where('companies_id_company', $companyId)->latest('id_demand_request')->take(5)->get();
                $myOrders = PurchaseOrder::where('companies_id_buyer', $companyId)->latest('id_purchase_order')->take(5)->get();
            }
        } catch (\Throwable $e) {
            // Defensive fallback when database query fails
        }

        return view('dashboard.buyer', compact('user', 'metrics', 'myDemands', 'myOrders'));
    }

    private function inspectorDashboard(User $user)
    {
        $metrics = [
            'assigned_inspections' => 0,
            'pending_inspections' => 0,
            'completed_inspections' => 0,
        ];
        $assignedInspections = new Collection;

        try {
            $metrics['assigned_inspections'] = Inspection::where('id_inspector', $user->id_user)->count();
            $metrics['pending_inspections'] = Inspection::where('id_inspector', $user->id_user)->where('status', 'MENUNGGU')->count();
            $metrics['completed_inspections'] = Inspection::where('id_inspector', $user->id_user)->where('status', 'SELESAI')->count();
            $assignedInspections = Inspection::with(['purchaseOrder'])->where('id_inspector', $user->id_user)->latest('id_inspection')->take(5)->get();
        } catch (\Throwable $e) {
            // Defensive fallback when database query fails
        }

        return view('dashboard.inspector', compact('user', 'metrics', 'assignedInspections'));
    }
}

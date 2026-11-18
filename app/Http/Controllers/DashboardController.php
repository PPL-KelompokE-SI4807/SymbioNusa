<?php

namespace App\Http\Controllers;

use App\Models\Company;
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

        $role = $user->activeRole();
        $company = $user->company;
        $pendingCompaniesCount = $role === 'ADMIN'
            ? Company::where('status_verifikasi', 'PENDING')->count()
            : 0;

        $view = match ($role) {
            'ADMIN' => 'dashboard.admin',
            'SELLER' => 'dashboard.seller',
            'BUYER' => 'dashboard.buyer',
            'INSPECTOR' => 'dashboard.inspector',
            default => 'dashboard.generic',
        };

        return view($view, compact('user', 'role', 'company', 'pendingCompaniesCount'));
    }
}

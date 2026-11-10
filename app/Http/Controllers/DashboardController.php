<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display the role-based main dashboard.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }

        $role = strtoupper($user->role ?? 'BUYER');

        $view = match ($role) {
            'ADMIN' => 'dashboard.admin',
            'SELLER' => 'dashboard.seller',
            'BUYER' => 'dashboard.buyer',
            'INSPECTOR' => 'dashboard.inspector',
            default => 'dashboard.generic',
        };

        return view($view, compact('user', 'role'));
    }
}

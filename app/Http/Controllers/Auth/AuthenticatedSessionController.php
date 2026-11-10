<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * FND-01 (SYM-1) Login dan FND-02 (SYM-2) Logout.
 */
class AuthenticatedSessionController extends Controller
{
    /**
     * Tampilkan formulir masuk.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Proses masuk lalu arahkan ke dasbor (dasbor menyesuaikan tampilan per peran).
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        // Cegah session fixation.
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Akhiri sesi aktif dan regenerasi token CSRF.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah keluar dari SymbioNusa.');
    }
}

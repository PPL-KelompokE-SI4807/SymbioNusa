<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\DemoLoginController;
use Illuminate\Support\Facades\Route;

// FND-01 (SYM-1): Login
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');

    // Masuk 1-klik ke akun demo: hanya untuk pengembangan lokal.
    if (app()->environment('local')) {
        Route::post('/login/demo/{role}', DemoLoginController::class)
            ->whereIn('role', array_keys(DemoLoginController::ACCOUNTS))
            ->name('login.demo');
    }
});

// FND-02 (SYM-2): Logout
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

<?php

use App\Http\Controllers\Admin\CompanyVerificationController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CompanyDocumentController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::prefix('perusahaan')->name('companies.')->group(function () {
        Route::get('/profil', [CompanyController::class, 'edit'])->name('edit');
        Route::put('/profil', [CompanyController::class, 'update'])->name('update');
        Route::post('/ganti-peran', [CompanyController::class, 'switchRole'])->name('switch-role');

        Route::post('/dokumen', [CompanyDocumentController::class, 'store'])->name('documents.store');
        Route::delete('/dokumen/{document}', [CompanyDocumentController::class, 'destroy'])->name('documents.destroy');
    });

    Route::prefix('admin/verifikasi-perusahaan')->name('admin.companies.')->group(function () {
        Route::get('/', [CompanyVerificationController::class, 'index'])->name('index');
        Route::get('/{company}', [CompanyVerificationController::class, 'show'])->name('show');
        Route::post('/{company}/setujui', [CompanyVerificationController::class, 'approve'])->name('approve');
        Route::post('/{company}/tolak', [CompanyVerificationController::class, 'reject'])->name('reject');
    });

    Route::prefix('admin/kategori')->name('admin.categories.')->group(function () {
        Route::get('/', [CategoryController::class, 'index'])->name('index');
        Route::get('/create', [CategoryController::class, 'create'])->name('create');
        Route::post('/', [CategoryController::class, 'store'])->name('store');
        Route::get('/{category}', [CategoryController::class, 'show'])->name('show');
        Route::get('/{category}/edit', [CategoryController::class, 'edit'])->name('edit');
        Route::put('/{category}', [CategoryController::class, 'update'])->name('update');
        Route::patch('/{category}', [CategoryController::class, 'update']);
        Route::post('/{category}/arsip', [CategoryController::class, 'archive'])->name('archive');
        Route::post('/{category}/pulihkan', [CategoryController::class, 'unarchive'])->name('unarchive');
    });
});

require __DIR__.'/auth.php';

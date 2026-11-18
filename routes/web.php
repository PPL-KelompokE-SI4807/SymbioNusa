<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\RfqController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

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

    Route::prefix('rfq')->name('rfq.')->group(function () {
        Route::get('/', [RfqController::class, 'index'])->name('index');
        Route::get('/create', [RfqController::class, 'create'])->name('create');
        Route::post('/', [RfqController::class, 'store'])->name('store');
        Route::get('/{rfq}', [RfqController::class, 'show'])->name('show');
        Route::post('/{rfq}/counter', [RfqController::class, 'counter'])->name('counter');
        Route::post('/{rfq}/accept', [RfqController::class, 'accept'])->name('accept');
        Route::post('/{rfq}/reject', [RfqController::class, 'reject'])->name('reject');
        Route::post('/{rfq}/withdraw', [RfqController::class, 'withdraw'])->name('withdraw');
    });

    // PBI-07: Verifikasi Pembayaran (Buyer & Seller)
    Route::prefix('buyer/invoices')->name('buyer.invoices.')->group(function () {
        Route::get('/', [PaymentController::class, 'buyerInvoices'])->name('index');
        Route::get('/{invoice}/bayar', [PaymentController::class, 'create'])->name('pay');
        Route::post('/{invoice}/bayar', [PaymentController::class, 'store'])->name('store');
    });

    Route::prefix('seller/invoices')->name('seller.invoices.')->group(function () {
        Route::get('/', [PaymentController::class, 'sellerInvoices'])->name('index');
    });

    Route::prefix('seller/payments')->name('seller.payments.')->group(function () {
        Route::get('/{payment}/verifikasi', [PaymentController::class, 'showVerify'])->name('verify');
        Route::post('/{payment}/verifikasi', [PaymentController::class, 'processVerify'])->name('processVerify');
    });

    Route::get('/payments/{payment}/bukti', [PaymentController::class, 'streamProof'])->name('payments.proof');
});

require __DIR__.'/auth.php';

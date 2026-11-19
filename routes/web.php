<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PurchaseOrderController;
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

    Route::prefix('po')->name('po.')->group(function () {
        Route::get('/', [PurchaseOrderController::class, 'index'])->name('index');
        Route::get('/create', [PurchaseOrderController::class, 'create'])->name('create');
        Route::post('/', [PurchaseOrderController::class, 'store'])->name('store');
        Route::get('/{po}', [PurchaseOrderController::class, 'show'])->name('show');
        Route::post('/{po}/send', [PurchaseOrderController::class, 'send'])->name('send');
        Route::post('/{po}/confirm', [PurchaseOrderController::class, 'confirm'])->name('confirm');
        Route::post('/{po}/reject', [PurchaseOrderController::class, 'reject'])->name('reject');
        Route::post('/{po}/cancel', [PurchaseOrderController::class, 'cancel'])->name('cancel');
    });
});

require __DIR__.'/auth.php';

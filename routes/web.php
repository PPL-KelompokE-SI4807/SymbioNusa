<?php

use App\Http\Controllers\Admin\CompanyVerificationController;
use App\Http\Controllers\Admin\InspectionAssignmentController;
use App\Http\Controllers\Admin\ListingModerationController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CompanyDocumentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DemandRequestController;
use App\Http\Controllers\DocumentVerificationController;
use App\Http\Controllers\InspectorProfileController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\RfqController;
use App\Http\Controllers\Seller\ListingController;
use App\Http\Controllers\ShipmentController;
use App\Http\Controllers\ShipmentDocumentController;
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

    // PBI-01 (SYM-5): Registri Inspector & Sertifikasi Kompetensi
    Route::prefix('admin/inspector')->name('admin.inspectors.')->group(function () {
        Route::get('/', [InspectorProfileController::class, 'index'])->name('index');
        Route::get('/daftarkan', [InspectorProfileController::class, 'create'])->name('create');
        Route::post('/', [InspectorProfileController::class, 'store'])->name('store');
        Route::get('/{inspector}', [InspectorProfileController::class, 'show'])->name('show');
        Route::get('/{inspector}/ubah', [InspectorProfileController::class, 'edit'])->name('edit');
        Route::put('/{inspector}', [InspectorProfileController::class, 'update'])->name('update');
        Route::get('/{inspector}/perpanjang', [InspectorProfileController::class, 'renewForm'])->name('renew.form');
        Route::post('/{inspector}/perpanjang', [InspectorProfileController::class, 'renew'])->name('renew');
        Route::post('/{inspector}/keluarkan', [InspectorProfileController::class, 'deactivate'])->name('deactivate');
    });
    Route::get('/inspector/{inspector}/sertifikat', [InspectorProfileController::class, 'certificate'])->name('inspectors.certificate');
    Route::get('/profil-kompetensi', [InspectorProfileController::class, 'mine'])->name('inspector.profile');
    Route::post('/profil-kompetensi/sertifikat', [InspectorProfileController::class, 'uploadMine'])->name('inspector.profile.certificate');

    // PBI-08 (SYM-12): membuat/mengirim RFQ, PO, dan kebutuhan hanya untuk perusahaan TERVERIFIKASI.
    Route::prefix('rfq')->name('rfq.')->group(function () {
        Route::get('/', [RfqController::class, 'index'])->name('index');
        Route::get('/create', [RfqController::class, 'create'])->name('create')->middleware('company.verified');
        Route::post('/', [RfqController::class, 'store'])->name('store')->middleware('company.verified');
        Route::get('/{rfq}', [RfqController::class, 'show'])->name('show');
        Route::post('/{rfq}/counter', [RfqController::class, 'counter'])->name('counter')->middleware('company.verified');
        Route::post('/{rfq}/accept', [RfqController::class, 'accept'])->name('accept')->middleware('company.verified');
        Route::post('/{rfq}/reject', [RfqController::class, 'reject'])->name('reject');
        Route::post('/{rfq}/withdraw', [RfqController::class, 'withdraw'])->name('withdraw');
    });

    Route::prefix('po')->name('po.')->group(function () {
        Route::get('/', [PurchaseOrderController::class, 'index'])->name('index');
        Route::get('/create', [PurchaseOrderController::class, 'create'])->name('create')->middleware('company.verified');
        Route::post('/', [PurchaseOrderController::class, 'store'])->name('store')->middleware('company.verified');
        Route::get('/{po}', [PurchaseOrderController::class, 'show'])->name('show');
        Route::post('/{po}/send', [PurchaseOrderController::class, 'send'])->name('send')->middleware('company.verified');
        Route::post('/{po}/confirm', [PurchaseOrderController::class, 'confirm'])->name('confirm')->middleware('company.verified');
        Route::post('/{po}/reject', [PurchaseOrderController::class, 'reject'])->name('reject');
        Route::post('/{po}/cancel', [PurchaseOrderController::class, 'cancel'])->name('cancel');
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

    // PBI-14: Kebutuhan Bahan Baku Sekunder (Buyer & Seller)
    Route::prefix('buyer/kebutuhan')->name('buyer.demands.')->group(function () {
        Route::get('/', [DemandRequestController::class, 'buyerIndex'])->name('index');
        Route::get('/buat', [DemandRequestController::class, 'create'])->name('create')->middleware('company.verified');
        Route::post('/', [DemandRequestController::class, 'store'])->name('store')->middleware('company.verified');
        Route::get('/{demand}/edit', [DemandRequestController::class, 'edit'])->name('edit')->middleware('company.verified');
        Route::put('/{demand}', [DemandRequestController::class, 'update'])->name('update')->middleware('company.verified');
        Route::patch('/{demand}/tutup', [DemandRequestController::class, 'close'])->name('close');
        Route::delete('/{demand}', [DemandRequestController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('seller/papan-kebutuhan')->name('seller.demands.')->group(function () {
        Route::get('/', [DemandRequestController::class, 'sellerIndex'])->name('index');
        Route::get('/{demand}', [DemandRequestController::class, 'sellerShow'])->name('show');
    });

    // PBI-17 (SYM-21): Verifikasi Dokumen Legalitas Non-B3 (Inspector)
    Route::prefix('inspector/verifikasi-dokumen')->name('inspector.verifications.')->group(function () {
        Route::get('/', [DocumentVerificationController::class, 'index'])->name('index');
        Route::get('/{verification}', [DocumentVerificationController::class, 'show'])->name('show');
        Route::post('/{verification}', [DocumentVerificationController::class, 'verify'])->name('verify');
    });

    // PBI-18 (SYM-22): Penugasan Inspeksi (Admin)
    Route::prefix('admin/penugasan-inspeksi')->name('admin.inspections.')->group(function () {
        Route::get('/', [InspectionAssignmentController::class, 'index'])->name('index');
        Route::get('/{order}/tugaskan', [InspectionAssignmentController::class, 'create'])->name('create');
        Route::post('/{order}/tugaskan', [InspectionAssignmentController::class, 'store'])->name('store');
        Route::get('/{inspection}', [InspectionAssignmentController::class, 'show'])->name('show');
        Route::post('/{inspection}/batalkan', [InspectionAssignmentController::class, 'cancel'])->name('cancel');
    });

    // PBI-18 (SYM-22): Tugas Inspeksi (Inspector)
    Route::get('/inspector/tugas-inspeksi', [InspectionAssignmentController::class, 'inspectorTasks'])->name('inspector.tasks.index');

    // PBI-09 (SYM-13): Listing Sisa Produksi (Seller)
    Route::prefix('seller/listings')->name('seller.listings.')->group(function () {
        Route::get('/', [ListingController::class, 'index'])->name('index');
        Route::get('/buat', [ListingController::class, 'create'])->name('create');
        Route::post('/', [ListingController::class, 'store'])->name('store');
        Route::get('/{listing}', [ListingController::class, 'show'])->name('show');
        Route::get('/{listing}/edit', [ListingController::class, 'edit'])->name('edit');
        Route::put('/{listing}', [ListingController::class, 'update'])->name('update');
        Route::post('/{listing}/arsip', [ListingController::class, 'archive'])->name('archive');
    });

    // PBI-09 (SYM-13): Moderasi Listing (Admin)
    Route::prefix('admin/moderasi-listing')->name('admin.listings.')->group(function () {
        Route::get('/', [ListingModerationController::class, 'index'])->name('index');
        Route::post('/{listing}/turunkan', [ListingModerationController::class, 'archive'])->name('archive');
    });

    // PBI-11 (SYM-15): Pengiriman & Manifes Angkut (Seller mengelola; Buyer, Admin, Inspector melihat)
    Route::prefix('pengiriman')->name('shipments.')->group(function () {
        Route::get('/', [ShipmentController::class, 'index'])->name('index');
        Route::get('/buat', [ShipmentController::class, 'create'])->name('create')->middleware('company.verified');
        Route::post('/', [ShipmentController::class, 'store'])->name('store')->middleware('company.verified');
        Route::get('/{shipment}', [ShipmentController::class, 'show'])->name('show');
        Route::get('/{shipment}/ubah', [ShipmentController::class, 'edit'])->name('edit')->middleware('company.verified');
        Route::put('/{shipment}', [ShipmentController::class, 'update'])->name('update')->middleware('company.verified');
        Route::post('/{shipment}/terbitkan', [ShipmentController::class, 'issue'])->name('issue')->middleware('company.verified');
        Route::post('/{shipment}/tiba', [ShipmentController::class, 'arrive'])->name('arrive');
        Route::post('/{shipment}/batalkan', [ShipmentController::class, 'cancel'])->name('cancel');
        Route::get('/{shipment}/manifes', [ShipmentController::class, 'manifest'])->name('manifest');
        Route::get('/{shipment}/dokumen/{document}', [ShipmentDocumentController::class, 'download'])->name('documents.download');
        Route::delete('/{shipment}/dokumen/{document}', [ShipmentDocumentController::class, 'destroy'])->name('documents.destroy');
    });

    // Katalog Material (Publik / Buyer / Semua pengguna terautentikasi)
    Route::prefix('katalog')->name('catalog.')->group(function () {
        Route::get('/', [CatalogController::class, 'index'])->name('index');
        Route::get('/{listing}', [CatalogController::class, 'show'])->name('show');
    });
});

require __DIR__.'/auth.php';

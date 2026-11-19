<?php

use App\Http\Controllers\DemoActorController;
use App\Http\Controllers\ShipmentController;
use App\Http\Controllers\ShipmentDocumentController;
use App\Http\Middleware\DemoActor;
use Illuminate\Support\Facades\Route;


$guard = config('symbionusa.standalone') ? [DemoActor::class] : ['auth'];

Route::middleware($guard)->group(function () {
    Route::redirect('/', '/shipments');

    Route::post('/demo/actor', [DemoActorController::class, 'switch'])->name('demo.actor');

    // PBI-11 - Pengiriman & Manifes Angkut
    Route::resource('shipments', ShipmentController::class)->except(['destroy']);
    Route::post('shipments/{shipment}/start', [ShipmentController::class, 'start'])->name('shipments.start');
    Route::post('shipments/{shipment}/arrive', [ShipmentController::class, 'arrive'])->name('shipments.arrive');
    Route::post('shipments/{shipment}/cancel', [ShipmentController::class, 'cancel'])->name('shipments.cancel');
    Route::get('shipments/{shipment}/manifest', [ShipmentController::class, 'manifest'])->name('shipments.manifest');

    Route::get('shipments/{shipment}/documents/{document}', [ShipmentDocumentController::class, 'download'])->name('shipments.documents.download');
    Route::delete('shipments/{shipment}/documents/{document}', [ShipmentDocumentController::class, 'destroy'])->name('shipments.documents.destroy');
});

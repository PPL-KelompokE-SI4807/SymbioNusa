<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 | PBI-11 - Pengiriman & Manifes Angkut.
 | INTEGRASI: FK ke purchase_orders mengharuskan migrasi ini berjalan SETELAH migrasi
 | purchase_orders milik PBI-05. Jika perlu, ganti timestamp pada nama file ini.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipments_manifests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->restrictOnDelete();
            $table->string('no_manifes', 30)->nullable()->unique(); // terbit saat pengiriman dimulai
            $table->string('armada', 100);
            $table->string('plat', 20);
            $table->string('sopir', 100);
            $table->string('kontak_sopir', 20)->nullable();
            $table->dateTime('jadwal_berangkat');
            $table->dateTime('estimasi_tiba');
            $table->string('rute_asal');
            $table->string('rute_tujuan');
            $table->decimal('jarak_km', 8, 2)->nullable(); // bisa dipakai ImpactCalculator (Haversine/manual)
            $table->decimal('berat_dikirim_kg', 12, 2);
            $table->text('alasan_di_bawah_batas')->nullable();
            $table->text('catatan')->nullable();
            $table->string('status', 20)->default('DRAF')->index(); // DRAF, DALAM_PENGIRIMAN, TIBA, DIBATALKAN
            $table->dateTime('berangkat_at')->nullable();
            $table->dateTime('tiba_at')->nullable();
            $table->dateTime('dibatalkan_at')->nullable();
            $table->text('alasan_batal')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['purchase_order_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipments_manifests');
    }
};

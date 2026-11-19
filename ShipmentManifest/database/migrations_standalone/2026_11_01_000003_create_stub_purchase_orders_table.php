<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// STUB mode mandiri. Dimiliki PBI-05 (purchase_orders, order_status_history) dan PBI-17 (document_verifications).
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_po', 30)->unique();
            $table->foreignId('seller_company_id')->constrained('companies');
            $table->foreignId('buyer_company_id')->constrained('companies');
            $table->string('material');
            $table->decimal('volume_kg', 12, 2);
            $table->decimal('harga_per_kg', 12, 2);
            $table->decimal('toleransi_tonase_persen', 5, 2)->default(0); // snapshot dari kategori
            $table->string('status', 30)->default('DIKONFIRMASI')->index();
            $table->timestamps();
        });

        Schema::create('document_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
            $table->unsignedBigInteger('user_id')->nullable(); // Inspector
            $table->string('status', 20)->default('MENUNGGU'); // MENUNGGU / DISETUJUI / DITOLAK
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });

        Schema::create('order_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('dari', 30)->nullable();
            $table->string('ke', 30);
            $table->string('alasan')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_status_history');
        Schema::dropIfExists('document_verifications');
        Schema::dropIfExists('purchase_orders');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipment_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_manifest_id')->constrained('shipments_manifests')->cascadeOnDelete();
            $table->string('jenis', 30)->default('SURAT_JALAN');
            $table->string('file_url');
            $table->string('nama_asli');
            $table->string('mime', 100)->nullable();
            $table->unsignedInteger('ukuran_kb')->default(0);
            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipment_documents');
    }
};

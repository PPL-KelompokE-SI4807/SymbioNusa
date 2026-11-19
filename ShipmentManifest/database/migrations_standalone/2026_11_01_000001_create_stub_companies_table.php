<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// STUB mode mandiri. Dimiliki PBI-08. Hapus folder migrations_standalone saat integrasi.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('tipe', 20)->default('SELLER'); // SELLER / BUYER / KEDUANYA
            $table->string('nib', 30)->unique();
            $table->text('alamat')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('kontak', 50)->nullable();
            $table->string('status_verifikasi', 20)->default('TERVERIFIKASI');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};

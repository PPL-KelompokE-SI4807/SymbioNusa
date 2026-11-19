<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// STUB mode mandiri (FND-01 / PBI-08 menyediakan kolom ini pada sistem asli).
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->default('SELLER')->after('password');
            // Tanpa constraint FK agar aman di SQLite; sistem asli boleh memakai constrained().
            $table->unsignedBigInteger('company_id')->nullable()->after('role')->index();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'company_id']);
        });
    }
};

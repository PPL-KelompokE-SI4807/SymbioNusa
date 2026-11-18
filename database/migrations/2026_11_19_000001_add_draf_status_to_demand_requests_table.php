<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE demand_requests MODIFY COLUMN status ENUM('DRAF', 'TERBUKA', 'TERPENUHI', 'KEDALUWARSA') NOT NULL DEFAULT 'TERBUKA'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE demand_requests MODIFY COLUMN status ENUM('TERBUKA', 'TERPENUHI', 'KEDALUWARSA') NOT NULL DEFAULT 'TERBUKA'");
    }
};

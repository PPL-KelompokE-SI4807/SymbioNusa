<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $sqlPath = database_path('sql/clean_ddl.sql');
        if (file_exists($sqlPath)) {
            $sql = file_get_contents($sqlPath);
            // Split by semicolon that ends a CREATE TABLE statement
            $statements = preg_split('/;\s*\n\s*\n/', $sql);
            foreach ($statements as $stmt) {
                $stmt = trim($stmt);
                if ($stmt) {
                    DB::unprepared($stmt . ';');
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        $tables = [
            'notifications', 'shipment_documents', 'audit_logs', 'order_status_history',
            'watchlists', 'impact_logs', 'reviews', 'compliance_notes', 'document_verifications',
            'inspection_results', 'inspections', 'disputes', 'goods_receipts', 'shipments_manifests',
            'payments', 'invoices', 'purchase_orders', 'rfq_offers', 'rfq_contracts',
            'demand_requests', 'supply_schedules', 'listing_attachments', 'listings',
            'impact_factors', 'inspector_profiles', 'categories', 'company_documents',
            'users', 'companies'
        ];
        foreach ($tables as $table) {
            Schema::dropIfExists($table);
        }
        Schema::enableForeignKeyConstraints();
    }
};

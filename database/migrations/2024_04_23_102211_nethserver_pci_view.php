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
        DB::statement("
        CREATE OR REPLACE VIEW nethserver_pci AS
        SELECT 
            pci_obj->>'class_id' as class_id,
            pci_obj->>'vendor_id' as vendor_id,
            pci_obj->>'device_id' as device_id,
            pci_obj->>'class_name' as class_name,
            pci_obj->>'vendor_name' as vendor_name,
            pci_obj->>'device_name' as device_name,
            pci_obj->>'driver' as driver
        FROM installations,
            json_array_elements(data->'facts'->'nodes'->'1'->'pci') as pci_obj
        WHERE data->>'installation' LIKE 'nethserver'
        AND (data->'facts'->'nodes'->'1'->'product'->>'name') IS NOT NULL;
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS nethserver_pci");
    }
};

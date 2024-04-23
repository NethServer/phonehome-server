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
        CREATE OR REPLACE VIEW nethsecurity_pci AS
        SELECT 
            pci_obj->>'class_id' as class_id,
            pci_obj->>'vendor_id' as vendor_id,
            pci_obj->>'device_id' as device_id,
            pci_obj->>'class_name' as class_name,
            pci_obj->>'vendor_name' as vendor_name,
            pci_obj->>'device_name' as device_name,
            pci_obj->>'driver' as driver
        FROM installations,
            json_array_elements(data->'facts'->'pci') as pci_obj
        WHERE data->>'installation' LIKE 'nethsecurity';
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS nethsecurity_pci");
    }
};

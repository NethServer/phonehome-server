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
       CREATE OR REPLACE VIEW nethserver_view AS
       SELECT
           data->'facts'->'nodes'->'1'->'product'->>'name' AS product_name,
           data->'facts'->'nodes'->'1'->'product'->>'manufacturer' AS manufacturer,
           data->'facts'->'nodes'->'1'->'processors'->>'model' AS processor,
           (SELECT DISTINCT ON (elem->>'class_id') 
               elem->>'device_name' 
           FROM json_array_elements(data->'facts'->'nodes'->'1'->'pci') AS elem
           WHERE elem->>'class_name' = 'VGA compatible controller'
           LIMIT 1) AS vga_controller,
           (SELECT DISTINCT ON (elem->>'class_id') 
               elem->>'device_name' 
           FROM json_array_elements(data->'facts'->'nodes'->'1'->'pci') AS elem
           WHERE elem->>'class_name' = 'USB controller'
           LIMIT 1) AS usb_controller,
           (SELECT DISTINCT ON (elem->>'class_id') 
               elem->>'device_name' 
           FROM json_array_elements(data->'facts'->'nodes'->'1'->'pci') AS elem
           WHERE elem->>'class_name' = 'PCI bridge'
           LIMIT 1) AS pci_bridge,
           (SELECT DISTINCT ON (elem->>'class_id') 
               elem->>'device_name' 
           FROM json_array_elements(data->'facts'->'nodes'->'1'->'pci') AS elem
           WHERE elem->>'class_name' = 'SATA controller'
           LIMIT 1) AS sata_controller,
           (SELECT DISTINCT ON (elem->>'class_id') 
               elem->>'device_name' 
           FROM json_array_elements(data->'facts'->'nodes'->'1'->'pci') AS elem
           WHERE elem->>'class_name' = 'Communication controller'
           LIMIT 1) AS communication_controller,
           (SELECT DISTINCT ON (elem->>'class_id') 
               elem->>'device_name' 
           FROM json_array_elements(data->'facts'->'nodes'->'1'->'pci') AS elem
           WHERE elem->>'class_name' = 'SCSI storage controller'
           LIMIT 1) AS scsi_controller,
           (SELECT DISTINCT ON (elem->>'class_id') 
               elem->>'device_name' 
           FROM json_array_elements(data->'facts'->'nodes'->'1'->'pci') AS elem
           WHERE elem->>'class_name' = 'Ethernet controller'
           LIMIT 1) AS ethernet
       FROM installations
       WHERE data->>'installation' LIKE 'nethserver'
       AND (data->'facts'->'nodes'->'1'->'product'->>'name') IS NOT NULL
       AND updated_at >= CURRENT_TIMESTAMP - INTERVAL '72 hours';
       ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS nethserver_view');
    }
};

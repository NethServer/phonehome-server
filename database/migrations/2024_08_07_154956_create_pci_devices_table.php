<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('DROP VIEW IF EXISTS nethserver_view');
        DB::statement('DROP VIEW IF EXISTS nethsecurity_view');
        DB::statement('DROP VIEW IF EXISTS nethserver_pci');
        DB::statement('DROP VIEW IF EXISTS nethsecurity_pci');
        DB::statement("
            CREATE OR REPLACE VIEW pci_devices AS
            WITH pci_facts AS (SELECT id AS installation_id, jsonb_path_query(to_jsonb(data -> 'facts'), '$.**.pci[*]') AS devices
                               FROM installations)
            SELECT installation_id, pci_devices.*
            FROM pci_facts,
                 jsonb_to_record(pci_facts.devices) AS pci_devices(driver text, class_name text, class_id text, device_id text,
                                                                   device_name text, revision text, vendor_id text,
                                                                   vendor_name text);
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP VIEW pci_devices');
    }
};

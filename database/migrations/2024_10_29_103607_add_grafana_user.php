<?php

use Illuminate\Database\Migrations\Migration;
use \Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $grafanaUser = config('grafana.database_username');
        if (DB::table('pg_roles')->where('rolname', $grafanaUser)->doesntExist()) {
            $grafanaPassword = config('grafana.database_password');
            DB::statement("CREATE USER $grafanaUser WITH PASSWORD '$grafanaPassword';");
        }
        DB::statement("GRANT USAGE ON SCHEMA public TO $grafanaUser;");
        DB::statement("GRANT SELECT ON public.installations TO $grafanaUser;");
        DB::statement("GRANT SELECT ON public.countries TO $grafanaUser;");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $grafanaUser = config('grafana.database_username');
        DB::statement("REVOKE SELECT ON public.countries FROM $grafanaUser;");
        DB::statement("REVOKE SELECT ON public.installations FROM $grafanaUser;");
        DB::statement("REVOKE USAGE ON SCHEMA public FROM $grafanaUser;");
        DB::statement("DROP USER $grafanaUser;");
    }
};

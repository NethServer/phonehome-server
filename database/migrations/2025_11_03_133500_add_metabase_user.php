<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $metabaseUser = config('metabase.username');
        if (DB::table('pg_roles')->where('rolname', $metabaseUser)->doesntExist()) {
            $metabasePassword = config('metabase.password');
            if ($metabasePassword == '') {
                $metabasePassword = \Illuminate\Support\Str::random(32);
                Log::warning("Metabase password is empty, new password is $metabasePassword");
            }
            DB::statement("CREATE USER $metabaseUser WITH PASSWORD '$metabasePassword';");
        }
        DB::statement("GRANT USAGE ON SCHEMA public TO $metabaseUser;");
        DB::statement("GRANT SELECT ON public.installations TO $metabaseUser;");
        DB::statement("GRANT SELECT ON public.countries TO $metabaseUser;");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $metabaseUser = config('metabase.username');
        DB::statement("REVOKE SELECT ON public.countries FROM $metabaseUser;");
        DB::statement("REVOKE SELECT ON public.installations FROM $metabaseUser;");
        DB::statement("REVOKE USAGE ON SCHEMA public FROM $metabaseUser;");
        DB::statement("DROP USER $metabaseUser;");
    }
};

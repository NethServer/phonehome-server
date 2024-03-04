<?php

namespace Database\Seeders;

use App\Models\Installation;
use Illuminate\Database\Seeder;

class InstallationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call(CountrySeeder::class);
        Installation::factory()
            ->count(50)
            ->nethserver()
            ->create();
        Installation::factory()
            ->count(50)
            ->nethsecurity()
            ->create();
    }
}

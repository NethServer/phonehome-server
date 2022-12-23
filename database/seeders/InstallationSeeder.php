<?php

namespace Database\Seeders;

use App\Models\Installation;
use Illuminate\Database\Seeder;

class InstallationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(CountrySeeder::class);
        Installation::factory()
            ->count(50)
            ->nethserver()
            ->create();
        Installation::factory()
            ->count(50)
            ->nextsecurity()
            ->create();
    }
}

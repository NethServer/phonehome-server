<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Installation;
use App\Models\Version;
use Database\Factories\CountryFactory;
use Database\Factories\VersionFactory;
use Illuminate\Database\Eloquent\Factories\Sequence;
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
        $this->call(VersionSeeder::class);
        $this->call(CountrySeeder::class);
        Installation::factory()
            ->count(50)
            ->state(new Sequence(
                fn() => [
                    'country_id' => Country::all()->random()->id,
                    'version_id' => Version::all()->random()->id
                ]
            ))
            ->create();
    }
}

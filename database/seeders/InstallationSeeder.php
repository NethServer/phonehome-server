<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Installation;
use App\Models\Version;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;

use function Pest\version;

class InstallationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $tags = collect([
            '6.10',
            '6.5',
            '7.2.1511',
            '7.9.2009'
        ]);
        $tags->each(function ($item) {
            Version::factory()->create([
                'tag' => $item
            ]);
        });

        $countryIsoCode = collect([
            'IT',
            'US',
            'GB',
            'DE'
        ]);
        $countryIsoCode->each(function ($item) {
            Country::factory()->create([
                'code' => $item
            ]);
        });

        for ($i=0; $i < 50; $i++) {
            Installation::factory()->create([
                'country_id' => Country::all()->random()->id,
                'version_id' => Version::all()->random()->id
            ]);
        }
    }
}

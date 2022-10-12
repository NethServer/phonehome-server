<?php

namespace Database\Seeders;

use App\Models\Installation;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
        $versions = collect([
            '6.10',
            '6.5',
            '7.2.1511',
            '7.9.2009'
        ]);
        Installation::factory()
            ->count(2000)
            ->state(new Sequence(
                fn () => ['release' => $versions->random()]
            ))
            ->create();
    }
}

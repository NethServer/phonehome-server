<?php

namespace Database\Factories;

use App\Models\Country;
use App\Models\Version;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Installation>
 */
class InstallationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'uuid' => fake()->uuid(),
            'type' => fake()->randomElement(['community', 'enterprise', 'subscription']),
            'country_id' => Country::factory(),
            'version_id' => Version::factory()
        ];
    }
}

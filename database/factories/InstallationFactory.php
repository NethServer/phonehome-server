<?php

namespace Database\Factories;

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
            'source_ip' => fake()->ipv4(),
            'country_iso_code' => fake()->countryCode(),
            'release' => fake()->numerify("#.#.####"),
            'type' => fake()->randomElement(['community', 'enterprise', 'subscription'])
        ];
    }
}

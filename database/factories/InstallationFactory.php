<?php

namespace Database\Factories;

use App\Models\Country;
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
            'data' => [
                'uuid' => fake()->uuid(),
                'facts' => [
                    'type' => fake()->randomElement(['community', 'enterprise', 'subscription']),
                    'version' => fake()->numerify('#.#.#'),
                ],
            ],
            'country_id' => Country::factory(),
        ];
    }
}

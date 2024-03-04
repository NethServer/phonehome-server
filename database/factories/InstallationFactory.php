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
    public function definition(): array
    {
        return [
            'data' => [
                'uuid' => fake()->uuid(),
                'installation' => 'nethserver',
                'facts' => [
                    'type' => fake()->randomElement(['community', 'enterprise', 'subscription']),
                    'version' => fake()->numerify('#.#.#'),
                ],
            ],
            'country_id' => Country::factory(),
        ];
    }

    /**
     * Retrieve nethserver 8 installation.
     */
    public function nethserver(): static
    {
        return $this->state(function () {
            return [
                'data' => [
                    'uuid' => fake()->uuid(),
                    'installation' => 'nethserver',
                    'facts' => [
                        'cluster' => [],
                        'nodes' => [
                            '1' => [
                                'distro' => [
                                    'name' => fake()->randomElement(['debian', 'rocky', 'centos']),
                                    'version' => fake()->numerify('#.#'),
                                ],
                                'version' => fake()->numerify('#.#.#'),
                            ],
                        ],
                        'modules' => [],
                    ],
                ],
            ];
        });
    }

    /**
     * Retrieve nethsecurity installation.
     */
    public function nethsecurity(): static
    {
        return $this->state(function () {
            return [
                'data' => [
                    'uuid' => fake()->uuid(),
                    'installation' => 'nethsecurity',
                    'facts' => [
                        'distro' => [
                            'name' => fake()->randomElement(['debian', 'rocky', 'centos']),
                            'version' => fake()->numerify('#.#'),
                        ],
                        'version' => fake()->numerify('#.#.#'),
                    ],
                ],
            ];
        });
    }
}

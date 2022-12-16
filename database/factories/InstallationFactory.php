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
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function nethserver()
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
     * Retrieve nextsecurity installation.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function nextsecurity()
    {
        return $this->state(function () {
            return [
                'data' => [
                    'uuid' => fake()->uuid(),
                    'installation' => 'nextsecurity',
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

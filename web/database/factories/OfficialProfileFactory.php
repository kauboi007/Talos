<?php

namespace Database\Factories;

use App\Models\OfficialProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OfficialProfile>
 */
class OfficialProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'service_number' => fake()->unique()->bothify('OFF-####-???'),
            'name' => fake()->name(),
            'rank' => 'lieutenant',
            'rank_val' => 1,
            'designation' => 'Operations Officer',
            'branch' => 'Army',
            'status' => 'active',
            'clearance_level' => 1,
            'specialization' => 'Command Operations',
            'neo4j_synced_at' => null,
        ];
    }
}

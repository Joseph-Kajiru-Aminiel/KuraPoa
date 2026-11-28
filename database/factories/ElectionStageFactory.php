<?php

namespace Database\Factories;

use App\Models\Election;
use App\Models\ElectionStage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ElectionStage>
 */
class ElectionStageFactory extends Factory
{
    protected $model = ElectionStage::class;

    public function definition(): array
    {
        return [
            'election_id' => Election::factory(),
            'key' => fake()->unique()->word(),
            'title' => fake()->sentence(2),
            'title_en' => fake()->sentence(2),
            'starts_at' => now(),
            'ends_at' => now()->addDay(),
            'sort_order' => 6,
        ];
    }
}

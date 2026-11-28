<?php

namespace Database\Factories;

use App\Models\Election;
use App\Models\Institution;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Election>
 */
class ElectionFactory extends Factory
{
    protected $model = Election::class;

    public function definition(): array
    {
        $startsAt = now()->addDays(2);

        return [
            'title' => fake()->unique()->sentence(4),
            'starts_at' => $startsAt,
            'ends_at' => $startsAt->copy()->addDays(2),
            'status' => 'draft',
            'institution_id' => Institution::factory(),
            'is_public' => true,
            'is_test' => false,
        ];
    }
}

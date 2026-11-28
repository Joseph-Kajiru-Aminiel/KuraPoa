<?php

namespace Database\Factories;

use App\Models\UsefulLink;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UsefulLink>
 */
class UsefulLinkFactory extends Factory
{
    protected $model = UsefulLink::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'description' => fake()->sentence(),
            'url' => fake()->url(),
            'sort_order' => 0,
            'is_active' => true,
        ];
    }
}

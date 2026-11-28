<?php

namespace Database\Factories;

use App\Models\Announcement;
use App\Models\Institution;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Announcement>
 */
class AnnouncementFactory extends Factory
{
    protected $model = Announcement::class;

    public function definition(): array
    {
        return [
            'institution_id' => Institution::factory(),
            'title' => fake()->sentence(5),
            'body' => fake()->paragraphs(2, true),
            'is_pinned' => false,
            'published_at' => now(),
            'created_by' => User::factory(),
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\Complaint;
use App\Models\Institution;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Complaint>
 */
class ComplaintFactory extends Factory
{
    protected $model = Complaint::class;

    public function definition(): array
    {
        return [
            'institution_id' => Institution::factory(),
            'type' => 'malalamiko',
            'ticket_code' => 'MLK-'.Str::upper(Str::random(6)),
            'message' => fake()->paragraph(),
            'status' => 'mpya',
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\Complaint;
use App\Models\ComplaintNote;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ComplaintNote>
 */
class ComplaintNoteFactory extends Factory
{
    protected $model = ComplaintNote::class;

    public function definition(): array
    {
        return [
            'complaint_id' => Complaint::factory(),
            'user_id' => User::factory(),
            'note' => fake()->sentence(),
        ];
    }
}

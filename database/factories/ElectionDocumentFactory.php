<?php

namespace Database\Factories;

use App\Models\Election;
use App\Models\ElectionDocument;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ElectionDocument>
 */
class ElectionDocumentFactory extends Factory
{
    protected $model = ElectionDocument::class;

    public function definition(): array
    {
        return [
            'election_id' => Election::factory(),
            'title' => fake()->sentence(3),
            'type' => 'ratiba',
            'file_path' => 'elections/docs/example.pdf',
            'is_public' => false,
            'uploaded_by' => User::factory(),
        ];
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['election_id', 'key', 'title', 'title_en', 'starts_at', 'ends_at', 'sort_order'])]
class ElectionStage extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return ['starts_at' => 'datetime', 'ends_at' => 'datetime'];
    }

    public function election(): BelongsTo
    {
        return $this->belongsTo(Election::class);
    }

    public static function createDefaultsFor(Election $election): void
    {
        $start = $election->starts_at;
        $end = $election->ends_at;

        foreach ([
            ['key' => 'registration', 'title' => 'Usajili wa wapiga kura', 'title_en' => 'Voter registration', 'starts_at' => $start->copy()->subDays(30), 'ends_at' => $start->copy()->subDays(21)],
            ['key' => 'vetting', 'title' => 'Uhakiki wa wagombea', 'title_en' => 'Candidate vetting', 'starts_at' => $start->copy()->subDays(21), 'ends_at' => $start->copy()->subDays(14)],
            ['key' => 'campaigns', 'title' => 'Kampeni', 'title_en' => 'Campaigns', 'starts_at' => $start->copy()->subDays(14), 'ends_at' => $start],
            ['key' => 'voting', 'title' => 'Upigaji kura', 'title_en' => 'Voting', 'starts_at' => $start, 'ends_at' => $end],
            ['key' => 'results', 'title' => 'Matokeo', 'title_en' => 'Results', 'starts_at' => $end, 'ends_at' => $end->copy()->addDay()],
        ] as $sortOrder => $stage) {
            $election->stages()->create($stage + ['sort_order' => $sortOrder]);
        }
    }
}

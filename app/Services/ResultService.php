<?php

namespace App\Services;

use App\Models\Election;
use Illuminate\Support\Collection;

class ResultService
{
    public function isVisible(Election $election): bool
    {
        return $election->isClosed();
    }

    /**
     * @return array{positions: Collection, turnout: array{registered: int, voted: int, percentage: float}}|null
     */
    public function forElection(Election $election): ?array
    {
        if (! $this->isVisible($election)) {
            return null;
        }

        $positions = $election->positions()
            ->with(['candidates' => fn ($query) => $query->where('status', 'approved')->withCount('votes')->orderBy('name')])
            ->get()
            ->map(function ($position) {
                $totalVotes = $position->candidates->sum('votes_count');
                $highestCount = (int) ($position->candidates->max('votes_count') ?? 0);
                $leaders = $position->candidates->filter(
                    fn ($candidate) => $highestCount > 0 && $candidate->votes_count === $highestCount
                );

                $position->total_votes = $totalVotes;
                $position->is_tied = $leaders->count() > 1;
                $position->candidates = $position->candidates->map(function ($candidate) use ($totalVotes, $highestCount, $leaders) {
                    $candidate->percentage = $totalVotes > 0
                        ? round(($candidate->votes_count / $totalVotes) * 100, 1)
                        : 0;
                    $candidate->is_leader = $highestCount > 0 && $candidate->votes_count === $highestCount;
                    $candidate->is_winner = $candidate->is_leader && $leaders->count() === 1;

                    return $candidate;
                });

                return $position;
            });

        $registered = $election->voters()->count();
        $voted = $election->voters()->where('has_voted', true)->count();

        return [
            'positions' => $positions,
            'turnout' => [
                'registered' => $registered,
                'voted' => $voted,
                'percentage' => $registered > 0 ? round(($voted / $registered) * 100, 1) : 0,
            ],
        ];
    }
}

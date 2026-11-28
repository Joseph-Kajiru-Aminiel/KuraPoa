<?php

namespace App\Services;

use App\Models\BallotParticipation;
use App\Models\Election;
use App\Models\Vote;
use App\Models\Voter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class VotingService
{
    /**
     * @param  array<int|string, int|string>  $choices
     */
    public function validateChoices(Voter $voter, array $choices): void
    {
        $election = $voter->election;

        if (! $election->isVotingOpen()) {
            throw ValidationException::withMessages(['choices' => 'Uchaguzi huu hauko wazi kwa sasa.']);
        }

        $positions = $election->positions()
            ->with(['candidates' => fn ($query) => $query->where('status', 'approved')])
            ->get();
        $expectedPositionIds = $positions->modelKeys();
        $choicePositionIds = array_map('intval', array_keys($choices));
        sort($expectedPositionIds);
        sort($choicePositionIds);

        if ($expectedPositionIds === [] || $choicePositionIds !== $expectedPositionIds) {
            throw ValidationException::withMessages(['choices' => 'Chagua mgombea mmoja kwa kila nafasi.']);
        }

        foreach ($positions as $position) {
            $candidateId = filter_var($choices[$position->id] ?? null, FILTER_VALIDATE_INT);

            if ($candidateId === false || ! $position->candidates->contains('id', $candidateId)) {
                throw ValidationException::withMessages(['choices' => 'Kuna chaguo lisilo sahihi. Kagua kura yako kisha ujaribu tena.']);
            }
        }
    }

    /**
     * @param  array<int|string, int|string>  $choices
     */
    public function cast(Voter $voter, array $choices): string
    {
        DB::transaction(function () use ($voter, $choices): void {
            $lockedVoter = Voter::query()->lockForUpdate()->findOrFail($voter->id);

            if ($lockedVoter->has_voted) {
                throw ValidationException::withMessages(['choices' => 'Tayari umepiga kura katika uchaguzi huu.']);
            }

            $election = Election::query()->lockForUpdate()->findOrFail($lockedVoter->election_id);
            $lockedVoter->setRelation('election', $election);
            $this->validateChoices($lockedVoter, $choices);

            $positions = $election->positions()
                ->with(['candidates' => fn ($query) => $query->where('status', 'approved')])
                ->lockForUpdate()
                ->get();

            foreach ($positions as $position) {
                $candidateId = (int) $choices[$position->id];

                Vote::query()->create([
                    'position_id' => $position->id,
                    'candidate_id' => $candidateId,
                ]);

                BallotParticipation::query()->create([
                    'voter_id' => $lockedVoter->id,
                    'position_id' => $position->id,
                ]);
            }

            $lockedVoter->forceFill([
                'has_voted' => true,
                'voted_at' => now(),
                'access_code' => Hash::make(bin2hex(random_bytes(32))),
                'access_code_fingerprint' => bin2hex(random_bytes(32)),
            ])->save();
        });

        return 'KP-'.strtoupper(bin2hex(random_bytes(3)));
    }
}

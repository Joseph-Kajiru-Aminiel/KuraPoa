<?php

namespace App\Http\Controllers\Voter;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\Position;
use App\Models\Voter;
use App\Services\VotingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class BallotController extends Controller
{
    public function show(Request $request): View
    {
        return $this->renderBallot($request, []);
    }

    public function review(Request $request, VotingService $votingService): View
    {
        $choices = $this->validatedChoices($request);
        $voter = $this->voter($request);
        $votingService->validateChoices($voter, $choices);

        $positions = $voter->election->positions()
            ->with(['candidates' => fn ($query) => $query->where('status', 'approved')])
            ->get()
            ->values()
            ->map(function (Position $position, int $index) use ($choices) {
                $position->selected_candidate = $position->candidates->firstWhere('id', (int) $choices[$position->id]);
                $position->step_index = $index;

                return $position;
            });

        return view('voter.review', [
            'election' => $voter->election,
            'positions' => $positions,
            'choices' => $choices,
        ]);
    }

    public function restore(Request $request, VotingService $votingService): View
    {
        $choices = $this->validatedChoices($request);
        $voter = $this->voter($request);
        $maxStep = max(0, $voter->election->positions()->count() - 1);
        $stepData = $request->validate([
            'return_step' => ['sometimes', 'required', 'integer', 'min:0', 'max:'.$maxStep],
        ]);
        $votingService->validateChoices($voter, $choices);

        return $this->renderBallot($request, $choices, (int) ($stepData['return_step'] ?? 0));
    }

    public function submit(Request $request, VotingService $votingService): RedirectResponse
    {
        $choices = $this->validatedChoices($request);
        $voter = $this->voter($request);
        $reference = $votingService->cast($voter, $choices);
        $electionId = $voter->election_id;

        $request->session()->forget([
            'voter_id',
            'election_id',
            'voter_session_expires_at',
            'ballot_order_'.$electionId,
        ]);
        $request->session()->put('voter_reference', $reference);
        $request->session()->regenerate();
        $request->session()->regenerateToken();

        return redirect()->route('voter.success');
    }

    public function success(Request $request): View|RedirectResponse
    {
        $reference = $request->session()->pull('voter_reference');

        if (! $reference) {
            return redirect()->route('voter.login');
        }

        return view('voter.success', compact('reference'));
    }

    private function renderBallot(Request $request, array $initialChoices, int $initialStep = 0): View
    {
        $voter = $this->voter($request);
        $positions = $voter->election->positions()
            ->with(['candidates' => fn ($query) => $query->where('status', 'approved')])
            ->get();
        $order = $request->session()->get('ballot_order_'.$voter->election_id, []);

        foreach ($positions as $position) {
            $candidateIds = $position->candidates->modelKeys();
            $savedOrder = $order[$position->id] ?? [];

            if (count($savedOrder) !== count($candidateIds) || array_diff($candidateIds, $savedOrder) !== []) {
                $savedOrder = $candidateIds;
                shuffle($savedOrder);
                $order[$position->id] = $savedOrder;
            }

            $candidateMap = $position->candidates->keyBy('id');
            $position->setRelation('candidates', collect($savedOrder)->map(
                fn ($candidateId) => $candidateMap->get($candidateId)
            )->filter()->values());
        }

        $request->session()->put('ballot_order_'.$voter->election_id, $order);
        $initialStep = max(0, min($initialStep, max(0, $positions->count() - 1)));
        $ballotData = $positions->map(fn (Position $position) => [
            'id' => $position->id,
            'title' => $position->title,
            'candidates' => $position->candidates->map(fn (Candidate $candidate) => [
                'id' => $candidate->id,
                'name' => $candidate->name,
                'photo' => $candidate->photo_thumbnail
                    ? asset('storage/'.$candidate->photo_thumbnail)
                    : ($candidate->photo ? asset('storage/'.$candidate->photo) : null),
                'manifesto' => $candidate->manifesto,
            ])->values(),
        ])->values();
        $initialChoices = (object) $initialChoices;

        return view('voter.ballot', compact('voter', 'positions', 'ballotData', 'initialChoices', 'initialStep'));
    }

    /**
     * @return array<int|string, int|string>
     */
    private function validatedChoices(Request $request): array
    {
        $validator = Validator::make($request->all(), [
            'choices' => ['required', 'array'],
            'choices.*' => ['required', 'integer', 'distinct'],
        ]);

        if ($validator->fails()) {
            throw ValidationException::withMessages(['choices' => 'Chagua mgombea mmoja kwa kila nafasi.']);
        }

        return $validator->validated()['choices'];
    }

    private function voter(Request $request): Voter
    {
        return $request->attributes->get('voter');
    }
}

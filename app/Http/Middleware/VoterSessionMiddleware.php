<?php

namespace App\Http\Middleware;

use App\Models\Voter;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VoterSessionMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $voterId = $request->session()->get('voter_id');
        $electionId = $request->session()->get('election_id');
        $expiresAt = $request->session()->get('voter_session_expires_at', 0);

        if (! $voterId || ! $electionId || now()->timestamp >= $expiresAt) {
            $request->session()->forget([
                'voter_id',
                'election_id',
                'voter_session_expires_at',
                'ballot_order_'.$electionId,
            ]);

            return redirect()->route('voter.login')
                ->withErrors(['login' => __('Kikao cha kupiga kura kimeisha. Ingia tena ili kuendelea.')]);
        }

        $voter = $voterId ? Voter::with('election')->find($voterId) : null;

        if ($voter?->has_voted) {
            $request->session()->forget(['voter_id', 'election_id', 'voter_session_expires_at']);

            return redirect()->route('voter.already-voted');
        }

        if (! $voter) {
            $request->session()->forget(['voter_id', 'election_id', 'voter_session_expires_at']);

            return redirect()->route('voter.login');
        }

        if ((int) $request->session()->get('election_id') !== $voter->election_id) {
            $request->session()->forget(['voter_id', 'election_id', 'voter_session_expires_at']);

            return redirect()->route('voter.login');
        }

        if (! $voter->election->isVotingOpen()) {
            $request->session()->forget(['voter_id', 'election_id']);

            return redirect()->route('voter.login')
                ->withErrors(['login' => 'Muda wa kupiga kura kwa uchaguzi huu umeisha au bado haujaanza.']);
        }

        $request->session()->put('voter_session_expires_at', now()->addMinutes(15)->timestamp);
        $request->attributes->set('voter', $voter);

        $response = $next($request);
        $response->headers->set('Cache-Control', 'private, no-store');

        return $response;
    }
}

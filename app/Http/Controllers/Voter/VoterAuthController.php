<?php

namespace App\Http\Controllers\Voter;

use App\Http\Controllers\Controller;
use App\Models\Election;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class VoterAuthController extends Controller
{
    public function create(): View
    {
        $elections = Election::query()
            ->where('status', 'open')
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now())
            ->orderBy('ends_at')
            ->get();

        return view('voter.login', compact('elections'));
    }

    public function store(Request $request): View|RedirectResponse
    {
        $data = $request->validate([
            'election_id' => ['required', 'integer'],
            'reg_number' => ['required', 'string', 'max:100'],
            'access_code' => ['required', 'regex:/^\d{6}$/'],
        ]);
        $data['reg_number'] = Str::upper(trim($data['reg_number']));
        $key = $this->throttleKey($request, $data['election_id'], $data['reg_number']);
        $ipKey = $this->ipThrottleKey($request);

        if (RateLimiter::tooManyAttempts($key, 5) || RateLimiter::tooManyAttempts($ipKey, 5)) {
            throw ValidationException::withMessages([
                'login' => 'Umejaribu mara nyingi. Tafadhali subiri dakika moja kisha ujaribu tena.',
            ]);
        }

        $election = Election::find($data['election_id']);
        $voter = $election?->voters()->where('reg_number', $data['reg_number'])->first();
        $codeIsValid = $voter
            && $voter->access_code
            && Hash::check($data['access_code'], $voter->access_code);

        if (! $election || ! $voter || ! $codeIsValid) {
            RateLimiter::hit($key, 60);
            RateLimiter::hit($ipKey, 60);

            throw ValidationException::withMessages([
                'login' => 'Namba ya usajili au msimbo si sahihi, au uchaguzi haupatikani.',
            ]);
        }

        RateLimiter::clear($key);
        RateLimiter::clear($ipKey);

        if ($voter->has_voted) {
            return view('voter.already-voted', compact('election'));
        }

        if (! $voter->is_active || ! $voter->verified_at) {
            RateLimiter::hit($key, 60);
            RateLimiter::hit($ipKey, 60);

            throw ValidationException::withMessages([
                'login' => __('Akaunti yako haijathibitishwa'),
            ]);
        }

        if (! $election->isVotingOpen()) {
            RateLimiter::hit($key, 60);
            RateLimiter::hit($ipKey, 60);

            throw ValidationException::withMessages([
                'login' => 'Namba ya usajili au msimbo si sahihi, au uchaguzi haupatikani.',
            ]);
        }

        $request->session()->regenerate();
        $request->session()->put([
            'voter_id' => $voter->id,
            'election_id' => $election->id,
            'voter_session_expires_at' => now()->addMinutes(15)->timestamp,
        ]);

        return redirect()->route('voter.ballot');
    }

    public function alreadyVoted(): View
    {
        return view('voter.already-voted', ['election' => null]);
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget([
            'voter_id',
            'election_id',
            'voter_session_expires_at',
            'ballot_order_'.(string) $request->session()->get('election_id'),
        ]);
        $request->session()->regenerateToken();

        return redirect()->route('voter.login');
    }

    private function throttleKey(Request $request, int|string $electionId, string $registrationNumber): string
    {
        return 'voter-login:'.hash('sha256', Str::lower($request->ip().'|'.$electionId.'|'.trim($registrationNumber)));
    }

    private function ipThrottleKey(Request $request): string
    {
        return 'voter-login-ip:'.hash('sha256', (string) $request->ip());
    }
}

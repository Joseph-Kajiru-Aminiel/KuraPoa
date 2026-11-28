<?php

namespace Tests\Feature;

use App\Models\Candidate;
use App\Models\Election;
use App\Models\Position;
use App\Models\User;
use App\Models\Vote;
use App\Models\Voter;
use App\Services\VotingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class VotingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        RateLimiter::clear('voter-login-ip:'.hash('sha256', '127.0.0.1'));
    }

    public function test_a_voter_cannot_cast_a_second_ballot(): void
    {
        [$election, $positions, $choices] = $this->makeBallot();
        $voter = $this->makeVoter($election);
        $service = app(VotingService::class);

        $reference = $service->cast($voter, $choices);

        $this->assertMatchesRegularExpression('/^KP-[A-F0-9]{6}$/', $reference);
        $this->assertTrue($voter->fresh()->has_voted);
        $this->assertFalse(Hash::check('100000', $voter->fresh()->access_code));
        $this->assertDatabaseCount('votes', count($positions));
        $this->assertDatabaseCount('ballot_participations', count($positions));

        try {
            $service->cast($voter->fresh(), $choices);
            $this->fail('A second ballot should be rejected.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('choices', $exception->errors());
        }

        $this->assertDatabaseCount('votes', count($positions));
    }

    public function test_voting_is_rejected_when_closed_or_not_started(): void
    {
        foreach ([
            ['status' => 'closed', 'starts_at' => now()->subHour(), 'ends_at' => now()->addHour()],
            ['status' => 'open', 'starts_at' => now()->addHour(), 'ends_at' => now()->addDays(2)],
        ] as $attributes) {
            [$election, , $choices] = $this->makeBallot($attributes);
            $voter = $this->makeVoter($election, 'MOCU/'.str()->random(8));

            try {
                app(VotingService::class)->cast($voter, $choices);
                $this->fail('Voting outside the election window should be rejected.');
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey('choices', $exception->errors());
            }
        }

        $this->assertDatabaseCount('votes', 0);
        $this->assertDatabaseCount('ballot_participations', 0);
    }

    public function test_candidate_from_another_position_is_rejected(): void
    {
        [$election, $positions, $choices] = $this->makeBallot();
        $voter = $this->makeVoter($election);
        $choices[$positions[0]->id] = $choices[$positions[1]->id];

        $this->expectException(ValidationException::class);
        app(VotingService::class)->validateChoices($voter, $choices);
    }

    public function test_unapproved_candidates_are_rejected(): void
    {
        [$election, $positions, $choices] = $this->makeBallot();
        $pending = Candidate::create([
            'position_id' => $positions[0]->id,
            'name' => 'Mgombea Hajathibitishwa',
            'status' => 'pending',
        ]);
        $choices[$positions[0]->id] = $pending->id;

        $this->expectException(ValidationException::class);
        app(VotingService::class)->validateChoices($this->makeVoter($election), $choices);
    }

    public function test_voter_session_expires_after_fifteen_minutes_of_inactivity(): void
    {
        [$election] = $this->makeBallot();
        $voter = $this->makeVoter($election);
        $this->travelTo(now());
        $this->withSession([
            'voter_id' => $voter->id,
            'election_id' => $election->id,
            'voter_session_expires_at' => now()->addMinutes(15)->timestamp,
        ]);
        Carbon::setTestNow(now()->addMinutes(16));

        $this->get(route('voter.ballot'))
            ->assertRedirect(route('voter.login'))
            ->assertSessionMissing('voter_id');

        Carbon::setTestNow();
    }

    public function test_unverified_or_inactive_voter_receives_the_same_generic_error(): void
    {
        [$election] = $this->makeBallot();
        $code = '456789';

        foreach ([
            ['verified_at' => null, 'is_active' => true],
            ['verified_at' => now(), 'is_active' => false],
        ] as $index => $eligibility) {
            $voter = Voter::create(array_merge([
                'election_id' => $election->id,
                'reg_number' => 'MOCU/2026/90'.$index,
                'name' => 'Mpiga Kura',
                'access_code' => Hash::make($code),
                'access_code_fingerprint' => hash_hmac('sha256', $code.$index, (string) config('app.key')),
            ], $eligibility));

            $response = $this->from('/kura')->post(route('voter.login.store'), [
                'election_id' => $election->id,
                'reg_number' => $voter->reg_number,
                'access_code' => $code,
            ]);

            $response->assertSessionHasErrors(['login' => 'Akaunti yako haijathibitishwa']);
            $this->assertFalse($this->app['session.store']->has('voter_id'));
        }
    }

    public function test_voter_can_review_submit_and_receive_a_non_choice_reference(): void
    {
        [$election, $positions, $choices] = $this->makeBallot();
        $voter = $this->makeVoter($election);
        $this->withSession([
            'voter_id' => $voter->id,
            'election_id' => $election->id,
            'voter_session_expires_at' => now()->addMinutes(15)->timestamp,
        ]);

        $this->get(route('voter.ballot'))
            ->assertOk()
            ->assertSee('Chagua viongozi wako')
            ->assertSee('Nafasi');

        $this->post(route('voter.review'), ['choices' => $choices])
            ->assertOk()
            ->assertSee('Hakiki chaguo lako')
            ->assertSee('Asha Rais')
            ->assertHeader('Cache-Control', 'no-store, private');

        $this->post(route('voter.submit'), ['choices' => $choices])
            ->assertRedirect(route('voter.success'));

        $this->assertDatabaseCount('votes', count($positions));
        $this->assertFalse($this->app['session.store']->has('voter_id'));
        $this->assertFalse($this->app['session.store']->has('choices'));

        $success = $this->get(route('voter.success'));
        $success->assertOk()
            ->assertSee('Kura yako imepokelewa')
            ->assertSee('Chapisha uthibitisho');
        $this->assertMatchesRegularExpression('/KP-[A-F0-9]{6}/', $success->getContent());

        $this->assertFalse($this->app['session.store']->has('voter_reference'));
    }

    public function test_ballot_review_and_receipt_render_in_english(): void
    {
        [$election, , $choices] = $this->makeBallot();
        $voter = $this->makeVoter($election);
        $this->withSession([
            'locale' => 'en',
            'voter_id' => $voter->id,
            'election_id' => $election->id,
            'voter_session_expires_at' => now()->addMinutes(15)->timestamp,
        ]);

        $this->get(route('voter.ballot'))
            ->assertOk()
            ->assertSee('<html lang="en">', false)
            ->assertSee('Choose your leaders')
            ->assertSee('Position :current of :total');

        $this->post(route('voter.review'), ['choices' => $choices])
            ->assertOk()
            ->assertSee('Review your choices')
            ->assertSee('Position 1 of 2')
            ->assertSee('Confirm and submit ballot')
            ->assertSee('Go back and edit choices');

        $this->post(route('voter.submit'), ['choices' => $choices])
            ->assertRedirect(route('voter.success'));

        $this->get(route('voter.success'))
            ->assertOk()
            ->assertSee('Your ballot has been received')
            ->assertSee('Reference number')
            ->assertSee('Print receipt')
            ->assertDontSee('Asha Rais');
    }

    public function test_voter_can_return_to_the_selected_position_from_review(): void
    {
        [$election, $positions, $choices] = $this->makeBallot();
        $voter = $this->makeVoter($election);
        $this->withSession([
            'voter_id' => $voter->id,
            'election_id' => $election->id,
            'voter_session_expires_at' => now()->addMinutes(15)->timestamp,
        ]);

        $this->post(route('voter.review'), ['choices' => $choices])
            ->assertOk()
            ->assertSee('Badilisha')
            ->assertSee('name="return_step" value="1"', false);

        $this->post(route('voter.ballot.restore'), [
            'choices' => $choices,
            'return_step' => 1,
        ])->assertOk()
            ->assertSee('data-initial-step="1"', false)
            ->assertSee('x-data="ballotWizard(', false)
            ->assertDontSee('{ ...ballotWizard', false);

        $this->assertFalse($this->app['session.store']->has('choices'));

        $this->post(route('voter.ballot.restore'), [
            'choices' => $choices,
            'return_step' => count($positions),
        ])->assertSessionHasErrors('return_step');
    }

    public function test_votes_have_no_voter_reference_or_timestamps(): void
    {
        $columns = Schema::getColumnListing('votes');

        $this->assertNotContains('voter_id', $columns);
        $this->assertNotContains('created_at', $columns);
        $this->assertNotContains('updated_at', $columns);
        $this->assertFalse((new Vote)->usesTimestamps());
        $this->assertSame(['id', 'position_id', 'candidate_id'], $columns);
    }

    public function test_public_results_are_hidden_until_election_closes(): void
    {
        [$election, $positions, $choices] = $this->makeBallot();
        $candidate = Candidate::findOrFail($choices[$positions[0]->id]);
        $candidate->update(['photo_thumbnail' => 'candidates/thumb.jpg']);
        Vote::create(['position_id' => $positions[0]->id, 'candidate_id' => $candidate->id]);

        $this->get(route('results.show', $election))->assertNotFound();

        $election->update(['status' => 'closed']);
        $this->get(route('results.show', $election))
            ->assertOk()
            ->assertSee($candidate->name)
            ->assertSee(asset('storage/candidates/thumb.jpg'))
            ->assertSee('Matokeo rasmi');
    }

    public function test_non_admin_cannot_access_admin_routes(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)->get('/admin')->assertForbidden();
    }

    public function test_voter_login_is_throttled_after_five_failed_attempts(): void
    {
        $payload = [
            'election_id' => 999,
            'reg_number' => 'MOCU/2026/999',
            'access_code' => '123456',
        ];

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post(route('voter.login.store'), $payload)->assertSessionHasErrors('login');
        }

        $response = $this->post(route('voter.login.store'), $payload);
        $response->assertSessionHasErrors('login');
        $this->assertSame(
            'Umejaribu mara nyingi. Tafadhali subiri dakika moja kisha ujaribu tena.',
            session('errors')->first('login'),
        );
        $this->assertFalse($this->app['session.store']->has('access_code'));
    }

    public function test_voter_login_page_renders_six_accessible_code_boxes(): void
    {
        $this->makeBallot();

        $this->get(route('voter.login'))
            ->assertOk()
            ->assertSee('role="group"', false)
            ->assertSee('name="access_code"', false)
            ->assertSee('data-code-index="5"', false)
            ->assertSee('Ingiza msimbo wa tarakimu sita. Unaweza pia kuubandika.');
    }

    public function test_valid_voter_login_uses_hashed_code_and_never_flashes_it(): void
    {
        [$election, $positions] = $this->makeBallot();
        $code = '654321';
        $voter = Voter::create([
            'election_id' => $election->id,
            'reg_number' => 'MOCU/2026/054',
            'name' => 'Mpiga Kura',
            'access_code' => Hash::make($code),
            'access_code_fingerprint' => hash_hmac('sha256', $code, (string) config('app.key')),
            'verified_at' => now(),
            'is_active' => true,
        ]);

        $this->post(route('voter.login.store'), [
            'election_id' => $election->id,
            'reg_number' => $voter->reg_number,
            'access_code' => $code,
        ])->assertRedirect(route('voter.ballot'));

        $this->assertSame($voter->id, $this->app['session.store']->get('voter_id'));
        $this->assertFalse($this->app['session.store']->has('access_code'));
        $this->get(route('voter.ballot'))->assertOk();
        $this->assertCount(count($positions), $this->app['session.store']->get('ballot_order_'.$election->id, []));
    }

    public function test_voter_registration_number_is_trimmed_and_normalized_to_uppercase(): void
    {
        [$election] = $this->makeBallot();
        $code = '135790';
        $voter = Voter::create([
            'election_id' => $election->id,
            'reg_number' => 'MOCU/2026/055',
            'name' => 'Mpiga Kura',
            'access_code' => Hash::make($code),
            'access_code_fingerprint' => hash_hmac('sha256', $code, (string) config('app.key')),
            'verified_at' => now(),
            'is_active' => true,
        ]);

        $this->post(route('voter.login.store'), [
            'election_id' => $election->id,
            'reg_number' => ' mocu/2026/055 ',
            'access_code' => $code,
        ])->assertRedirect(route('voter.ballot'));

        $this->assertSame($voter->id, $this->app['session.store']->get('voter_id'));
    }

    /**
     * @return array{Election, array<int, Position>, array<int, int>}
     */
    private function makeBallot(array $electionAttributes = []): array
    {
        $election = Election::create(array_merge([
            'title' => 'Uchaguzi wa majaribio',
            'starts_at' => now()->subHour(),
            'ends_at' => now()->addDay(),
            'status' => 'open',
        ], $electionAttributes));

        $positions = [];
        $choices = [];

        foreach (['Rais', 'Katibu'] as $title) {
            $position = Position::create([
                'election_id' => $election->id,
                'title' => $title,
                'sort_order' => count($positions),
            ]);
            $first = Candidate::create(['position_id' => $position->id, 'name' => 'Asha '.$title, 'status' => 'approved']);
            Candidate::create(['position_id' => $position->id, 'name' => 'Juma '.$title, 'status' => 'approved']);

            $positions[] = $position;
            $choices[$position->id] = $first->id;
        }

        return [$election, $positions, $choices];
    }

    private function makeVoter(Election $election, string $registrationNumber = 'MOCU/2026/001'): Voter
    {
        $code = str_pad((string) (100000 + Voter::count()), 6, '0', STR_PAD_LEFT);

        return Voter::create([
            'election_id' => $election->id,
            'reg_number' => $registrationNumber,
            'name' => 'Mpiga Kura',
            'access_code' => bcrypt($code),
            'access_code_fingerprint' => hash_hmac('sha256', $code, (string) config('app.key')),
            'verified_at' => now(),
            'is_active' => true,
        ]);
    }
}

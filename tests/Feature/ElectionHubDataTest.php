<?php

namespace Tests\Feature;

use App\Models\Election;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ElectionHubDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_elections_receive_unique_slugs_and_the_five_default_stages(): void
    {
        $election = Election::factory()->create([
            'title' => 'Student Leaders Election',
            'status' => 'scheduled',
        ]);
        $secondElection = Election::factory()->create(['title' => 'Student Leaders Election']);

        $this->assertSame('student-leaders-election', $election->slug);
        $this->assertSame('student-leaders-election-2', $secondElection->slug);
        $this->assertSame(
            ['registration', 'vetting', 'campaigns', 'voting', 'results'],
            $election->stages->pluck('key')->all(),
        );

        $votingStage = $election->stages->firstWhere('key', 'voting');
        $this->assertEquals($election->starts_at, $votingStage->starts_at);
        $this->assertEquals($election->ends_at, $votingStage->ends_at);
    }

    public function test_public_election_scope_excludes_drafts_and_private_elections(): void
    {
        $publicElection = Election::factory()->create(['status' => 'open', 'is_public' => true]);
        $draftElection = Election::factory()->create(['status' => 'draft', 'is_public' => true]);
        $privateElection = Election::factory()->create(['status' => 'closed', 'is_public' => false]);

        $this->assertSame(
            [$publicElection->id],
            Election::query()->publiclyVisible()->pluck('id')->all(),
        );
        $this->assertFalse($draftElection->isPubliclyVisible());
        $this->assertFalse($privateElection->isPubliclyVisible());
    }

    public function test_observer_assignment_relationship_is_separate_from_admin_flags(): void
    {
        $observer = User::factory()->create([
            'is_admin' => false,
            'role' => 'observer',
            'institution_id' => null,
        ]);
        $election = Election::factory()->create();

        $observer->assignedElections()->attach($election);

        $this->assertTrue($observer->isObserver());
        $this->assertFalse($observer->isAdministrator());
        $this->assertSame([$election->id], $observer->assignedElections->modelKeys());
    }
}

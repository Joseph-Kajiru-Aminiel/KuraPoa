<?php

namespace Tests\Feature;

use App\Models\Election;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ElectionStatusSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_scheduled_elections_open_and_expired_elections_close_automatically(): void
    {
        $scheduled = Election::create([
            'title' => 'Uchaguzi uliopangwa',
            'starts_at' => now()->subMinute(),
            'ends_at' => now()->addDay(),
            'status' => 'scheduled',
        ]);
        $expired = Election::create([
            'title' => 'Uchaguzi uliokwisha',
            'starts_at' => now()->subDays(2),
            'ends_at' => now()->subMinute(),
            'status' => 'open',
        ]);

        $this->artisan('elections:sync-statuses')->assertSuccessful();

        $this->assertSame('open', $scheduled->fresh()->status);
        $this->assertSame('closed', $expired->fresh()->status);
        $this->assertDatabaseHas('audit_logs', ['action' => 'election.opened_automatically']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'election.closed_automatically']);
    }

    public function test_test_election_cleanup_is_dry_run_by_default_and_never_targets_production(): void
    {
        $oldTestElection = Election::create([
            'title' => 'Uchaguzi wa majaribio wa zamani',
            'starts_at' => now()->subDays(40),
            'ends_at' => now()->subDays(39),
            'status' => 'closed',
            'is_test' => true,
        ]);
        $productionElection = Election::create([
            'title' => 'Uchaguzi wa kawaida',
            'starts_at' => now()->subDays(40),
            'ends_at' => now()->subDays(39),
            'status' => 'closed',
            'is_test' => false,
        ]);

        $this->artisan('elections:cleanup-test')->assertSuccessful();
        $this->assertDatabaseHas('elections', ['id' => $oldTestElection->id]);

        $this->artisan('elections:cleanup-test', ['--force' => true])->assertSuccessful();
        $this->assertDatabaseMissing('elections', ['id' => $oldTestElection->id]);
        $this->assertDatabaseHas('elections', ['id' => $productionElection->id]);
    }
}

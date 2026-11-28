<?php

namespace Tests\Feature;

use App\Models\Candidate;
use App\Models\Election;
use App\Models\Institution;
use App\Models\Position;
use App\Models\User;
use App\Models\Voter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_and_election_management_pages_render(): void
    {
        $institution = Institution::create(['name' => 'Chuo cha Jaribio', 'code' => 'TEST-1']);
        $admin = User::factory()->create(['is_admin' => true, 'institution_id' => $institution->id]);
        $election = Election::create([
            'title' => 'Uchaguzi wa majaribio',
            'starts_at' => now()->subHour(),
            'ends_at' => now()->addDay(),
            'status' => 'draft',
            'institution_id' => $institution->id,
        ]);
        $position = Position::create(['election_id' => $election->id, 'title' => 'Rais', 'sort_order' => 0]);
        Candidate::create(['position_id' => $position->id, 'name' => 'Asha Mussa', 'manifesto' => 'Uwazi na uwajibikaji.', 'status' => 'approved']);
        Candidate::create(['position_id' => $position->id, 'name' => 'Juma Paulo', 'manifesto' => 'Huduma bora kwa wanafunzi.', 'status' => 'approved']);
        Voter::create([
            'election_id' => $election->id,
            'reg_number' => 'MOCU/2026/001',
            'name' => 'Mwanafunzi',
            'access_code' => bcrypt('123456'),
            'access_code_fingerprint' => hash_hmac('sha256', '123456', (string) config('app.key')),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Karibu')
            ->assertSee('Wapiga kura');

        $this->get(route('admin.elections.index'))->assertOk()->assertSee($election->title);
        $this->get(route('admin.elections.create'))->assertOk()->assertSee('Unda uchaguzi mpya');
        $this->get(route('admin.elections.edit', $election))->assertOk()->assertSee('Hariri uchaguzi');
        $this->get(route('admin.elections.show', $election))
            ->assertOk()
            ->assertSee('Asha Mussa')
            ->assertSee('Nafasi na wagombea')
            ->assertSee('Wapiga kura');
        $this->get(route('admin.results.index'))->assertOk();
        $this->get(route('admin.results.show', $election))
            ->assertOk()
            ->assertSee('Matokeo bado yamefichwa');
    }

    public function test_voter_login_page_lists_active_elections(): void
    {
        $election = Election::create([
            'title' => 'Uchaguzi unaoendelea',
            'starts_at' => now()->subHour(),
            'ends_at' => now()->addDay(),
            'status' => 'open',
        ]);

        $this->get(route('voter.login'))
            ->assertOk()
            ->assertSee($election->title)
            ->assertSee('Namba ya usajili')
            ->assertSee('Msimbo wa kupigia kura');
    }

    public function test_election_list_uses_swahili_pagination_controls(): void
    {
        $institution = Institution::create(['name' => 'Chuo cha Jaribio', 'code' => 'TEST-2']);
        $admin = User::factory()->create(['is_admin' => true, 'institution_id' => $institution->id]);

        for ($index = 1; $index <= 11; $index++) {
            Election::create([
                'title' => 'Uchaguzi '.$index,
                'starts_at' => now()->addDay(),
                'ends_at' => now()->addDays(2),
                'status' => 'draft',
                'institution_id' => $institution->id,
            ]);
        }

        $this->actingAs($admin)
            ->get(route('admin.elections.index'))
            ->assertOk()
            ->assertSee('Urambazaji wa kurasa')
            ->assertSee('Inayofuata')
            ->assertSee('Inaonyesha');
    }

    public function test_normal_admin_is_scoped_to_their_institution(): void
    {
        $ownInstitution = Institution::create(['name' => 'Chuo A', 'code' => 'SCOPE-A']);
        $otherInstitution = Institution::create(['name' => 'Chuo B', 'code' => 'SCOPE-B']);
        $ownElection = Election::create([
            'title' => 'Uchaguzi wa Chuo A',
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addDays(2),
            'status' => 'draft',
            'institution_id' => $ownInstitution->id,
        ]);
        $otherElection = Election::create([
            'title' => 'Uchaguzi wa Chuo B',
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addDays(2),
            'status' => 'draft',
            'institution_id' => $otherInstitution->id,
        ]);
        $admin = User::factory()->create(['is_admin' => true, 'institution_id' => $ownInstitution->id]);

        $this->actingAs($admin)
            ->get(route('admin.elections.index'))
            ->assertOk()
            ->assertSee($ownElection->title)
            ->assertDontSee($otherElection->title);

        $this->get(route('admin.elections.show', $otherElection))->assertNotFound();
    }

    public function test_audit_log_is_limited_to_super_administrators(): void
    {
        $institution = Institution::create(['name' => 'Chuo cha Jaribio', 'code' => 'AUDIT-1']);
        $admin = User::factory()->create(['is_admin' => true, 'institution_id' => $institution->id]);
        $superAdmin = User::factory()->create([
            'is_admin' => true,
            'is_super_admin' => true,
            'institution_id' => $institution->id,
        ]);

        $this->actingAs($admin)->get(route('admin.audit.index'))->assertForbidden();
        $this->actingAs($superAdmin)->get(route('admin.audit.index'))->assertOk()->assertSee('Kumbukumbu za ukaguzi');
    }
}

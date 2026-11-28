<?php

namespace Tests\Feature;

use App\Models\Institution;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LanguageTest extends TestCase
{
    use RefreshDatabase;

    public function test_voter_language_switch_preserves_voting_session(): void
    {
        $this->withSession(['voter_id' => 17, 'election_id' => 4])
            ->post(route('language.update', 'en'), ['return_path' => '/kura'])
            ->assertRedirect('/kura')
            ->assertSessionHas('locale', 'en')
            ->assertSessionHas('voter_id', 17)
            ->assertSessionHas('election_id', 4);

        $this->get('/kura')
            ->assertOk()
            ->assertSee('<html lang="en">', false)
            ->assertSee('Log in to vote');
    }

    public function test_admin_language_is_saved_and_unsupported_locale_is_rejected(): void
    {
        $institution = Institution::create(['name' => 'Chuo cha Jaribio', 'code' => 'LANG-1']);
        $admin = User::factory()->create([
            'is_admin' => true,
            'institution_id' => $institution->id,
            'locale' => null,
        ]);

        $this->actingAs($admin)
            ->post(route('language.update', 'en'), ['return_path' => '/login'])
            ->assertRedirect('/login');

        $this->assertSame('en', $admin->fresh()->locale);
        $this->post('/lang/fr', ['return_path' => '/'])->assertNotFound();
        $this->post(route('language.update', 'sw'), ['return_path' => '//outside.example'])->assertUnprocessable();
    }
}

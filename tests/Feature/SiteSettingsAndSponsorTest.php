<?php

namespace Tests\Feature;

use App\Models\Institution;
use App\Models\Sponsor;
use App\Models\User;
use App\Services\SponsorLogoService;
use App\Support\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class SiteSettingsAndSponsorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        SiteSetting::clearCache();
    }

    public function test_landing_page_has_one_vote_now_button_and_partner_cards(): void
    {
        Sponsor::create([
            'name' => 'Tume ya Taifa ya Uchaguzi',
            'url' => 'https://example.test',
            'sort_order' => 0,
            'is_active' => true,
        ]);

        $response = $this->get(route('home'))->assertOk();

        $this->assertSame(1, substr_count($response->getContent(), 'Piga kura sasa'));
        $response->assertSee('Tume ya Taifa ya Uchaguzi')
            ->assertSee('Tembelea')
            ->assertSee('id="jinsi-inavyofanya-kazi"', false)
            ->assertSee('id="washirika"', false)
            ->assertDontSee('aria-label="Tufuate YouTube"', false)
            ->assertDontSee('aria-label="Tufuate Instagram"', false)
            ->assertDontSee('Wasiliana nasi kupitia WhatsApp')
            ->assertDontSee('Ingia Kupiga Kura')
            ->assertDontSee('Ingia kupiga kura');
    }

    public function test_only_super_admin_can_manage_site_settings(): void
    {
        $institution = Institution::create(['name' => 'Chuo cha Jaribio', 'code' => 'SETTINGS-1']);
        $admin = User::factory()->create(['is_admin' => true, 'institution_id' => $institution->id]);
        $superAdmin = User::factory()->create([
            'is_admin' => true,
            'is_super_admin' => true,
            'institution_id' => $institution->id,
        ]);

        $this->actingAs($admin)->get(route('admin.settings.edit'))->assertForbidden();
        $this->actingAs($superAdmin)->get(route('admin.settings.edit'))->assertOk()->assertSee('Mipangilio');
    }

    public function test_settings_are_validated_cached_and_rendered_on_the_landing_page(): void
    {
        $institution = Institution::create(['name' => 'Chuo cha Jaribio', 'code' => 'SETTINGS-2']);
        $superAdmin = User::factory()->create([
            'is_admin' => true,
            'is_super_admin' => true,
            'institution_id' => $institution->id,
        ]);

        $this->assertNull(SiteSetting::get('whatsapp_number'));

        $this->actingAs($superAdmin)
            ->put(route('admin.settings.update'), [
                'youtube_url' => 'https://youtube.com/@kurapoa',
                'instagram_url' => 'https://instagram.com/kurapoa',
                'whatsapp_number' => '255712345678',
                'contact_email' => 'hello@kurapoa.test',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame('255712345678', SiteSetting::get('whatsapp_number'));
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('https://wa.me/255712345678')
            ->assertSee('https://youtube.com/@kurapoa')
            ->assertSee('https://instagram.com/kurapoa')
            ->assertSee('aria-label="Tufuate YouTube"', false)
            ->assertSee('aria-label="Tufuate Instagram"', false)
            ->assertSee('aria-label="Wasiliana nasi kupitia WhatsApp"', false)
            ->assertSee('hello@kurapoa.test');
    }

    public function test_http_social_urls_and_invalid_whatsapp_numbers_are_rejected(): void
    {
        $institution = Institution::create(['name' => 'Chuo cha Jaribio', 'code' => 'SETTINGS-3']);
        $superAdmin = User::factory()->create([
            'is_admin' => true,
            'is_super_admin' => true,
            'institution_id' => $institution->id,
        ]);

        $this->actingAs($superAdmin)
            ->put(route('admin.settings.update'), [
                'youtube_url' => 'http://youtube.com/kurapoa',
                'whatsapp_number' => '+255712345678',
            ])
            ->assertSessionHasErrors(['youtube_url', 'whatsapp_number']);
    }

    public function test_sponsor_logo_service_keeps_safe_svg_and_rejects_active_content(): void
    {
        Storage::fake('public');
        $service = app(SponsorLogoService::class);
        $safe = UploadedFile::fake()->createWithContent(
            'mark.svg',
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 10 10"><circle cx="5" cy="5" r="4"/></svg>',
        );

        $path = $service->store($safe);

        Storage::disk('public')->assertExists($path);
        $this->assertStringContainsString('<circle', Storage::disk('public')->get($path));

        $unsafe = UploadedFile::fake()->createWithContent(
            'unsafe.svg',
            '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>',
        );

        try {
            $service->store($unsafe);
            $this->fail('SVG script elements must be rejected.');
        } catch (ValidationException) {
            $this->assertTrue(true);
        }
    }

    public function test_super_admin_can_upload_a_sanitized_svg_partner_logo(): void
    {
        Storage::fake('public');
        $institution = Institution::create(['name' => 'Chuo cha Jaribio', 'code' => 'SETTINGS-4']);
        $superAdmin = User::factory()->create([
            'is_admin' => true,
            'is_super_admin' => true,
            'institution_id' => $institution->id,
        ]);
        $logo = UploadedFile::fake()->createWithContent(
            'alama.svg',
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 10 10"><circle cx="5" cy="5" r="4"/></svg>',
        );

        $this->actingAs($superAdmin)->post(route('admin.sponsors.store'), [
            'name' => 'Mshirika wa Jaribio',
            'url' => 'https://example.test',
            'sort_order' => 1,
            'is_active' => 1,
            'logo' => $logo,
        ])->assertRedirect()->assertSessionHasNoErrors();

        $sponsor = Sponsor::query()->where('name', 'Mshirika wa Jaribio')->firstOrFail();
        Storage::disk('public')->assertExists($sponsor->logo);
        $this->assertStringContainsString('<circle', Storage::disk('public')->get($sponsor->logo));
    }
}

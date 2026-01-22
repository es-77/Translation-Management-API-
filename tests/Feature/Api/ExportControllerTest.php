<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Translation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ExportControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Sanctum::actingAs(User::factory()->create());
    }

    public function test_can_export_all_translations(): void
    {
        Translation::factory()->create(['key' => 'common.welcome', 'locale' => 'en', 'value' => 'Welcome']);
        Translation::factory()->create(['key' => 'common.goodbye', 'locale' => 'en', 'value' => 'Goodbye']);
        Translation::factory()->create(['key' => 'common.welcome', 'locale' => 'fr', 'value' => 'Bienvenue']);

        $response = $this->getJson('/api/export');

        $response->assertOk();

        $data = $response->json('data');
        $this->assertArrayHasKey('en', $data);
        $this->assertArrayHasKey('fr', $data);
        $this->assertEquals('Welcome', $data['en']['common.welcome']);
        $this->assertEquals('Goodbye', $data['en']['common.goodbye']);
        $this->assertEquals('Bienvenue', $data['fr']['common.welcome']);
    }

    public function test_can_export_translations_by_locale(): void
    {
        Translation::factory()->create(['key' => 'common.welcome', 'locale' => 'en', 'value' => 'Welcome']);
        Translation::factory()->create(['key' => 'common.goodbye', 'locale' => 'en', 'value' => 'Goodbye']);
        Translation::factory()->create(['key' => 'common.welcome', 'locale' => 'fr', 'value' => 'Bienvenue']);

        $response = $this->getJson('/api/export?locale=en');

        $response->assertOk();

        $data = $response->json('data');
        $this->assertCount(2, $data);
        $this->assertEquals('Welcome', $data['common.welcome']);
        $this->assertEquals('Goodbye', $data['common.goodbye']);
    }

    public function test_export_returns_empty_for_no_translations(): void
    {
        $response = $this->getJson('/api/export');

        $response->assertOk()
            ->assertJson(['data' => []]);
    }

    public function test_export_requires_authentication(): void
    {
        // Create a new client without authentication
        $this->app['auth']->forgetGuards();

        $response = $this->getJson('/api/export');

        $response->assertUnauthorized();
    }

    public function test_export_always_returns_fresh_data(): void
    {
        Translation::factory()->create(['key' => 'test.key', 'locale' => 'en', 'value' => 'Original']);

        $response1 = $this->getJson('/api/export');
        $data1 = $response1->json('data');
        $this->assertEquals('Original', $data1['en']['test.key']);

        // Update the translation
        Translation::where('key', 'test.key')->update(['value' => 'Updated']);

        $response2 = $this->getJson('/api/export');
        $data2 = $response2->json('data');
        $this->assertEquals('Updated', $data2['en']['test.key']);
    }
}

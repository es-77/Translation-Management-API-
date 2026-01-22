<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Tag;
use App\Models\Translation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TranslationControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Sanctum::actingAs(User::factory()->create());
    }

    public function test_can_list_translations(): void
    {
        Translation::factory()->count(5)->create();

        $response = $this->getJson('/api/translations');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'key', 'locale', 'value', 'created_at', 'updated_at'],
                ],
            ])
            ->assertJsonCount(5, 'data');
    }

    public function test_can_list_translations_with_pagination(): void
    {
        Translation::factory()->count(20)->create();

        $response = $this->getJson('/api/translations?per_page=10');

        $response->assertOk()
            ->assertJsonCount(10, 'data');
    }

    public function test_can_create_translation(): void
    {
        $data = [
            'key' => 'test.create',
            'locale' => 'en',
            'value' => 'Test creation',
        ];

        $response = $this->postJson('/api/translations', $data);

        $response->assertCreated()
            ->assertJsonFragment($data);

        $this->assertDatabaseHas('translations', $data);
    }

    public function test_can_create_translation_with_tags(): void
    {
        $tag1 = Tag::factory()->create(['name' => 'mobile']);
        $tag2 = Tag::factory()->create(['name' => 'desktop']);

        $data = [
            'key' => 'test.with_tags',
            'locale' => 'en',
            'value' => 'Test with tags',
            'tags' => [$tag1->id, $tag2->id],
        ];

        $response = $this->postJson('/api/translations', $data);

        $response->assertCreated()
            ->assertJsonPath('data.tags.0.name', 'mobile')
            ->assertJsonPath('data.tags.1.name', 'desktop');
    }

    public function test_create_translation_validates_required_fields(): void
    {
        $response = $this->postJson('/api/translations', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['key', 'locale', 'value']);
    }

    public function test_can_view_single_translation(): void
    {
        $translation = Translation::factory()->create([
            'key' => 'view.test',
        ]);

        $response = $this->getJson("/api/translations/{$translation->id}");

        $response->assertOk()
            ->assertJsonPath('data.key', 'view.test');
    }

    public function test_view_returns_404_for_nonexistent_translation(): void
    {
        $response = $this->getJson('/api/translations/9999');

        $response->assertNotFound();
    }

    public function test_can_update_translation(): void
    {
        $translation = Translation::factory()->create();

        $response = $this->putJson("/api/translations/{$translation->id}", [
            'value' => 'Updated value',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.value', 'Updated value');
    }

    public function test_can_update_translation_tags(): void
    {
        $translation = Translation::factory()->create();
        $tag = Tag::factory()->create(['name' => 'web']);

        $response = $this->putJson("/api/translations/{$translation->id}", [
            'tags' => [$tag->id],
        ]);

        $response->assertOk()
            ->assertJsonPath('data.tags.0.name', 'web');
    }

    public function test_update_returns_404_for_nonexistent_translation(): void
    {
        $response = $this->putJson('/api/translations/9999', [
            'value' => 'Test',
        ]);

        $response->assertNotFound();
    }

    public function test_can_delete_translation(): void
    {
        $translation = Translation::factory()->create();

        $response = $this->deleteJson("/api/translations/{$translation->id}");

        $response->assertNoContent();
        $this->assertDatabaseMissing('translations', ['id' => $translation->id]);
    }

    public function test_delete_returns_404_for_nonexistent_translation(): void
    {
        $response = $this->deleteJson('/api/translations/9999');

        $response->assertNotFound();
    }

    public function test_can_search_translations_by_key(): void
    {
        Translation::factory()->create(['key' => 'common.welcome']);
        Translation::factory()->create(['key' => 'common.goodbye']);
        Translation::factory()->create(['key' => 'auth.login']);

        $response = $this->getJson('/api/translations/search?key=common');

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_can_search_translations_by_locale(): void
    {
        Translation::factory()->create(['locale' => 'en']);
        Translation::factory()->create(['locale' => 'en']);
        Translation::factory()->create(['locale' => 'fr']);

        $response = $this->getJson('/api/translations/search?locale=en');

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_can_search_translations_by_content(): void
    {
        Translation::factory()->create(['value' => 'Welcome to the app']);
        Translation::factory()->create(['value' => 'Hello world']);

        $response = $this->getJson('/api/translations/search?content=Welcome');

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_can_search_translations_by_tags(): void
    {
        $tag = Tag::factory()->create(['name' => 'mobile']);
        $translation = Translation::factory()->create();
        $translation->tags()->attach($tag);

        Translation::factory()->create(); // Without tag

        $response = $this->getJson("/api/translations/search?tags[]={$tag->id}");

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_search_returns_empty_when_no_matches(): void
    {
        Translation::factory()->create(['key' => 'test.key']);

        $response = $this->getJson('/api/translations/search?key=nonexistent');

        $response->assertOk()
            ->assertJsonCount(0, 'data');
    }
}

<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TagControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Sanctum::actingAs(User::factory()->create());
    }

    public function test_can_list_tags(): void
    {
        Tag::factory()->count(3)->create();

        $response = $this->getJson('/api/tags');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'created_at', 'updated_at'],
                ],
            ])
            ->assertJsonCount(3, 'data');
    }

    public function test_can_create_tag(): void
    {
        $response = $this->postJson('/api/tags', ['name' => 'mobile']);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'mobile');

        $this->assertDatabaseHas('tags', ['name' => 'mobile']);
    }

    public function test_create_tag_validates_required_name(): void
    {
        $response = $this->postJson('/api/tags', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_create_tag_validates_unique_name(): void
    {
        Tag::factory()->create(['name' => 'mobile']);

        $response = $this->postJson('/api/tags', ['name' => 'mobile']);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_can_view_single_tag(): void
    {
        $tag = Tag::factory()->create(['name' => 'desktop']);

        $response = $this->getJson("/api/tags/{$tag->id}");

        $response->assertOk()
            ->assertJsonPath('data.name', 'desktop');
    }

    public function test_view_returns_404_for_nonexistent_tag(): void
    {
        $response = $this->getJson('/api/tags/9999');

        $response->assertNotFound();
    }

    public function test_can_update_tag(): void
    {
        $tag = Tag::factory()->create(['name' => 'old_name']);

        $response = $this->putJson("/api/tags/{$tag->id}", ['name' => 'new_name']);

        $response->assertOk()
            ->assertJsonPath('data.name', 'new_name');
    }

    public function test_update_validates_unique_name(): void
    {
        Tag::factory()->create(['name' => 'existing']);
        $tag = Tag::factory()->create(['name' => 'to_update']);

        $response = $this->putJson("/api/tags/{$tag->id}", ['name' => 'existing']);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_update_allows_same_name_for_same_tag(): void
    {
        $tag = Tag::factory()->create(['name' => 'mobile']);

        $response = $this->putJson("/api/tags/{$tag->id}", ['name' => 'mobile']);

        $response->assertOk();
    }

    public function test_update_returns_404_for_nonexistent_tag(): void
    {
        $response = $this->putJson('/api/tags/9999', ['name' => 'test']);

        $response->assertNotFound();
    }

    public function test_can_delete_tag(): void
    {
        $tag = Tag::factory()->create();

        $response = $this->deleteJson("/api/tags/{$tag->id}");

        $response->assertNoContent();
        $this->assertDatabaseMissing('tags', ['id' => $tag->id]);
    }

    public function test_delete_returns_404_for_nonexistent_tag(): void
    {
        $response = $this->deleteJson('/api/tags/9999');

        $response->assertNotFound();
    }
}

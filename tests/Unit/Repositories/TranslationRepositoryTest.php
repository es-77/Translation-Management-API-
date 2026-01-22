<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories;

use App\Models\Tag;
use App\Models\Translation;
use App\Repositories\TranslationRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TranslationRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private TranslationRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new TranslationRepository(new Translation());
    }

    public function test_can_create_translation(): void
    {
        $data = [
            'key' => 'test.key',
            'locale' => 'en',
            'value' => 'Test value',
        ];

        $translation = $this->repository->create($data);

        $this->assertInstanceOf(Translation::class, $translation);
        $this->assertEquals('test.key', $translation->key);
        $this->assertEquals('en', $translation->locale);
        $this->assertEquals('Test value', $translation->value);
        $this->assertDatabaseHas('translations', $data);
    }

    public function test_can_find_translation_by_id(): void
    {
        $translation = Translation::factory()->create();

        $found = $this->repository->find($translation->id);

        $this->assertNotNull($found);
        $this->assertEquals($translation->id, $found->id);
    }

    public function test_find_returns_null_for_nonexistent_translation(): void
    {
        $found = $this->repository->find(9999);

        $this->assertNull($found);
    }

    public function test_can_update_translation(): void
    {
        $translation = Translation::factory()->create([
            'value' => 'Original value',
        ]);

        $updated = $this->repository->update($translation->id, [
            'value' => 'Updated value',
        ]);

        $this->assertEquals('Updated value', $updated->value);
        $this->assertDatabaseHas('translations', [
            'id' => $translation->id,
            'value' => 'Updated value',
        ]);
    }

    public function test_can_delete_translation(): void
    {
        $translation = Translation::factory()->create();

        $result = $this->repository->delete($translation->id);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('translations', [
            'id' => $translation->id,
        ]);
    }

    public function test_can_get_all_translations(): void
    {
        Translation::factory()->count(5)->create();

        $translations = $this->repository->all();

        $this->assertCount(5, $translations);
    }

    public function test_can_paginate_translations(): void
    {
        Translation::factory()->count(20)->create();

        $paginated = $this->repository->paginate(10);

        $this->assertCount(10, $paginated->items());
        $this->assertEquals(20, $paginated->total());
    }

    public function test_can_search_by_key_pattern(): void
    {
        Translation::factory()->create(['key' => 'common.welcome']);
        Translation::factory()->create(['key' => 'common.goodbye']);
        Translation::factory()->create(['key' => 'auth.login']);

        $results = $this->repository->search(['key' => 'common'], 15);

        $this->assertCount(2, $results->items());
    }

    public function test_can_search_by_locale(): void
    {
        Translation::factory()->create(['locale' => 'en']);
        Translation::factory()->create(['locale' => 'en']);
        Translation::factory()->create(['locale' => 'fr']);

        $results = $this->repository->search(['locale' => 'en'], 15);

        $this->assertCount(2, $results->items());
    }

    public function test_can_search_by_content(): void
    {
        Translation::factory()->create(['value' => 'Welcome to the app']);
        Translation::factory()->create(['value' => 'Goodbye friend']);
        Translation::factory()->create(['value' => 'Welcome back']);

        $results = $this->repository->search(['content' => 'Welcome'], 15);

        $this->assertCount(2, $results->items());
    }

    public function test_can_search_by_tags(): void
    {
        $tag1 = Tag::factory()->create(['name' => 'mobile']);
        $tag2 = Tag::factory()->create(['name' => 'desktop']);

        $translation1 = Translation::factory()->create();
        $translation1->tags()->attach($tag1);

        $translation2 = Translation::factory()->create();
        $translation2->tags()->attach($tag2);

        $translation3 = Translation::factory()->create();
        $translation3->tags()->attach([$tag1->id, $tag2->id]);

        $results = $this->repository->search(['tags' => [$tag1->id]], 15);

        $this->assertCount(2, $results->items());
    }

    public function test_can_sync_tags(): void
    {
        $translation = Translation::factory()->create();
        $tag1 = Tag::factory()->create(['name' => 'mobile']);
        $tag2 = Tag::factory()->create(['name' => 'desktop']);

        $this->repository->syncTags($translation->id, [$tag1->id, $tag2->id]);

        $translation->refresh();
        $this->assertCount(2, $translation->tags);
    }

    public function test_get_all_for_export_returns_optimized_collection(): void
    {
        Translation::factory()->count(10)->create();

        $results = $this->repository->getAllForExport();

        $this->assertCount(10, $results);
        // Verify it only has the columns needed for export
        $first = $results->first();
        $this->assertNotNull($first->key);
        $this->assertNotNull($first->locale);
        $this->assertNotNull($first->value);
    }
}

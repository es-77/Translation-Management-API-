<?php

namespace Tests\Unit\Repositories;

use App\Models\Tag;
use App\Repositories\TagRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private TagRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new TagRepository(new Tag());
    }

    public function test_it_can_get_all_tags()
    {
        Tag::factory()->count(3)->create();
        $this->assertCount(3, $this->repository->all());
    }

    public function test_it_can_find_tag_by_id()
    {
        $tag = Tag::factory()->create();
        $found = $this->repository->find($tag->id);
        $this->assertTrue($found->is($tag));
    }

    public function test_it_can_find_tag_by_name()
    {
        $tag = Tag::factory()->create(['name' => 'unique-name']);
        $found = $this->repository->findByName('unique-name');
        $this->assertTrue($found->is($tag));
    }

    public function test_it_returns_null_when_tag_not_found_by_name()
    {
        $this->assertNull($this->repository->findByName('non-existent'));
    }

    public function test_it_can_create_tag()
    {
        $tag = $this->repository->create(['name' => 'new-tag']);
        $this->assertDatabaseHas('tags', ['name' => 'new-tag']);
    }

    public function test_it_can_update_tag()
    {
        $tag = Tag::factory()->create();
        $updated = $this->repository->update($tag->id, ['name' => 'updated-name']);
        $this->assertEquals('updated-name', $updated->name);
        $this->assertDatabaseHas('tags', ['name' => 'updated-name']);
    }

    public function test_it_can_delete_tag()
    {
        $tag = Tag::factory()->create();
        $this->repository->delete($tag->id);
        $this->assertDatabaseMissing('tags', ['id' => $tag->id]);
    }
}

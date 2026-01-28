<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Tag;
use App\Models\Translation;
use App\Repositories\Contracts\TranslationRepositoryInterface;
use App\Services\TranslationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class TranslationServiceTest extends TestCase
{
    use RefreshDatabase;

    private TranslationService $service;
    private MockInterface $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = Mockery::mock(TranslationRepositoryInterface::class);
        $this->service = new TranslationService($this->repository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_all_delegates_to_repository(): void
    {
        $collection = new \Illuminate\Database\Eloquent\Collection();

        $this->repository
            ->shouldReceive('all')
            ->once()
            ->andReturn($collection);

        $result = $this->service->all();

        $this->assertSame($collection, $result);
    }

    public function test_find_delegates_to_repository(): void
    {
        $translation = new Translation([
            'id' => 1,
            'key' => 'test.key',
            'locale' => 'en',
            'value' => 'Test',
        ]);

        $this->repository
            ->shouldReceive('find')
            ->once()
            ->with(1)
            ->andReturn($translation);

        $result = $this->service->find(1);

        $this->assertInstanceOf(Translation::class, $result);
        $this->assertEquals('test.key', $result->key);
    }

    public function test_create_without_tags(): void
    {
        $data = ['key' => 'test.key', 'locale' => 'en', 'value' => 'Test'];
        $translation = new Translation($data + ['id' => 1]);

        $this->repository
            ->shouldReceive('create')
            ->once()
            ->with($data)
            ->andReturn($translation);

        $result = $this->service->create($data);

        $this->assertInstanceOf(Translation::class, $result);
    }

    public function test_create_with_tags(): void
    {
        $data = ['key' => 'test.key', 'locale' => 'en', 'value' => 'Test'];
        $tagIds = [1, 2];
        $translation = Mockery::mock(Translation::class)->makePartial();
        $translation->id = 1;
        $translation->key = 'test.key';

        $this->repository
            ->shouldReceive('create')
            ->once()
            ->with($data)
            ->andReturn($translation);

        $this->repository
            ->shouldReceive('syncTags')
            ->once()
            ->with(1, $tagIds);

        $translation->shouldReceive('load')
            ->once()
            ->with('tags')
            ->andReturnSelf();

        $result = $this->service->create($data, $tagIds);

        $this->assertEquals(1, $result->id);
    }

    public function test_update_delegates_to_repository(): void
    {
        $data = ['value' => 'Updated'];
        $translation = new Translation(['id' => 1] + $data);

        $this->repository
            ->shouldReceive('update')
            ->once()
            ->with(1, $data)
            ->andReturn($translation);

        $result = $this->service->update(1, $data);

        $this->assertEquals('Updated', $result->value);
    }

    public function test_update_with_tags(): void
    {
        $data = ['value' => 'Updated'];
        $tagIds = [1, 2];
        $translation = Mockery::mock(Translation::class)->makePartial();
        $translation->id = 1;
        $translation->fill($data);

        $this->repository
            ->shouldReceive('update')
            ->once()
            ->with(1, $data)
            ->andReturn($translation);

        $this->repository
            ->shouldReceive('syncTags')
            ->once()
            ->with(1, $tagIds);

        $translation->shouldReceive('load')
            ->once()
            ->with('tags')
            ->andReturnSelf();

        $result = $this->service->update(1, $data, $tagIds);

        $this->assertEquals('Updated', $result->value);
    }

    public function test_delete_delegates_to_repository(): void
    {
        $this->repository
            ->shouldReceive('delete')
            ->once()
            ->with(1)
            ->andReturn(true);

        $result = $this->service->delete(1);

        $this->assertTrue($result);
    }

    public function test_search_delegates_to_repository(): void
    {
        $filters = ['key' => 'common'];
        $paginator = new LengthAwarePaginator([], 0, 15);

        $this->repository
            ->shouldReceive('search')
            ->once()
            ->with($filters, 15)
            ->andReturn($paginator);

        $result = $this->service->search($filters, 15);

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
    }

    public function test_paginate_delegates_to_repository(): void
    {
        $paginator = new LengthAwarePaginator([], 0, 10);

        $this->repository
            ->shouldReceive('paginate')
            ->once()
            ->with(10)
            ->andReturn($paginator);

        $result = $this->service->paginate(10);

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
    }
}

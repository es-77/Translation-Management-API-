<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Tag;
use App\Repositories\Contracts\TagRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * Repository implementation for Tag model operations.
 */
class TagRepository implements TagRepositoryInterface
{
    /**
     * Create a new repository instance.
     */
    public function __construct(
        private readonly Tag $model
    ) {}

    /**
     * {@inheritdoc}
     */
    public function all(): Collection
    {
        return $this->model->all();
    }

    /**
     * {@inheritdoc}
     */
    public function find(int $id): ?Tag
    {
        return $this->model->find($id);
    }

    /**
     * {@inheritdoc}
     */
    public function findByName(string $name): ?Tag
    {
        return $this->model->where('name', $name)->first();
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $data): Tag
    {
        return $this->model->create($data);
    }

    /**
     * {@inheritdoc}
     */
    public function update(int $id, array $data): Tag
    {
        $tag = $this->model->findOrFail($id);
        $tag->update($data);

        return $tag->fresh();
    }

    /**
     * {@inheritdoc}
     */
    public function delete(int $id): bool
    {
        $tag = $this->model->findOrFail($id);

        return $tag->delete();
    }
}

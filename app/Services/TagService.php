<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Tag;
use App\Repositories\Contracts\TagRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * Service class for tag business logic.
 */
class TagService
{
    /**
     * Create a new service instance.
     */
    public function __construct(
        private readonly TagRepositoryInterface $repository
    ) {}

    /**
     * Get all tags.
     *
     * @return Collection<int, Tag>
     */
    public function all(): Collection
    {
        return $this->repository->all();
    }

    /**
     * Find a tag by ID.
     */
    public function find(int $id): ?Tag
    {
        return $this->repository->find($id);
    }

    /**
     * Create a new tag.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): Tag
    {
        return $this->repository->create($data);
    }

    /**
     * Update an existing tag.
     *
     * @param array<string, mixed> $data
     */
    public function update(int $id, array $data): Tag
    {
        return $this->repository->update($id, $data);
    }

    /**
     * Delete a tag.
     */
    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }
}

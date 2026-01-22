<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Collection;

/**
 * Interface for Tag repository operations.
 */
interface TagRepositoryInterface
{
    /**
     * Get all tags.
     *
     * @return Collection<int, Tag>
     */
    public function all(): Collection;

    /**
     * Find a tag by ID.
     */
    public function find(int $id): ?Tag;

    /**
     * Find a tag by name.
     */
    public function findByName(string $name): ?Tag;

    /**
     * Create a new tag.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): Tag;

    /**
     * Update an existing tag.
     *
     * @param array<string, mixed> $data
     */
    public function update(int $id, array $data): Tag;

    /**
     * Delete a tag.
     */
    public function delete(int $id): bool;
}

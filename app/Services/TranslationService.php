<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Translation;
use App\Repositories\Contracts\TranslationRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * Service class for translation business logic.
 */
class TranslationService
{
    /**
     * Create a new service instance.
     */
    public function __construct(
        private readonly TranslationRepositoryInterface $repository
    ) {}

    /**
     * Get all translations.
     *
     * @return Collection<int, Translation>
     */
    public function all(): Collection
    {
        return $this->repository->all();
    }

    /**
     * Get paginated translations.
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginate($perPage);
    }

    /**
     * Find a translation by ID.
     */
    public function find(int $id): ?Translation
    {
        return $this->repository->find($id);
    }

    /**
     * Create a new translation with optional tags.
     *
     * @param array<string, mixed> $data
     * @param array<int> $tagIds
     */
    public function create(array $data, array $tagIds = []): Translation
    {
        $translation = $this->repository->create($data);

        if (!empty($tagIds)) {
            $this->repository->syncTags($translation->id, $tagIds);
            $translation->load('tags');
        }

        return $translation;
    }

    /**
     * Update an existing translation with optional tags.
     *
     * @param array<string, mixed> $data
     * @param array<int>|null $tagIds
     */
    public function update(int $id, array $data, ?array $tagIds = null): Translation
    {
        $translation = $this->repository->update($id, $data);

        if ($tagIds !== null) {
            $this->repository->syncTags($id, $tagIds);
            $translation->load('tags');
        }

        return $translation;
    }

    /**
     * Delete a translation.
     */
    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }

    /**
     * Search translations with filters.
     *
     * @param array<string, mixed> $filters
     */
    public function search(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->search($filters, $perPage);
    }
}

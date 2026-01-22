<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Translation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * Interface for Translation repository operations.
 */
interface TranslationRepositoryInterface
{
    /**
     * Get all translations.
     *
     * @return Collection<int, Translation>
     */
    public function all(): Collection;

    /**
     * Find a translation by ID.
     */
    public function find(int $id): ?Translation;

    /**
     * Create a new translation.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): Translation;

    /**
     * Update an existing translation.
     *
     * @param array<string, mixed> $data
     */
    public function update(int $id, array $data): Translation;

    /**
     * Delete a translation.
     */
    public function delete(int $id): bool;

    /**
     * Get paginated translations.
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    /**
     * Search translations with filters.
     *
     * @param array<string, mixed> $filters
     */
    public function search(array $filters, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get all translations optimized for export.
     *
     * @return Collection<int, Translation>
     */
    public function getAllForExport(): Collection;

    /**
     * Sync tags for a translation.
     *
     * @param array<int> $tagIds
     */
    public function syncTags(int $translationId, array $tagIds): void;
}

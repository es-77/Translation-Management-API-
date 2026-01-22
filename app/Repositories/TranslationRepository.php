<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Translation;
use App\Repositories\Contracts\TranslationRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * Repository implementation for Translation model operations.
 */
class TranslationRepository implements TranslationRepositoryInterface
{
    /**
     * Create a new repository instance.
     */
    public function __construct(
        private readonly Translation $model
    ) {}

    /**
     * {@inheritdoc}
     */
    public function all(): Collection
    {
        return $this->model->with('tags')->get();
    }

    /**
     * {@inheritdoc}
     */
    public function find(int $id): ?Translation
    {
        return $this->model->with('tags')->find($id);
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $data): Translation
    {
        return $this->model->create($data);
    }

    /**
     * {@inheritdoc}
     */
    public function update(int $id, array $data): Translation
    {
        $translation = $this->model->findOrFail($id);
        $translation->update($data);

        return $translation->fresh('tags');
    }

    /**
     * {@inheritdoc}
     */
    public function delete(int $id): bool
    {
        $translation = $this->model->findOrFail($id);

        return $translation->delete();
    }

    /**
     * {@inheritdoc}
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->with('tags')->paginate($perPage);
    }

    /**
     * {@inheritdoc}
     */
    public function search(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->with('tags');

        if (!empty($filters['key'])) {
            $query->byKeyPattern($filters['key']);
        }

        if (!empty($filters['locale'])) {
            $query->byLocale($filters['locale']);
        }

        if (!empty($filters['content'])) {
            $query->byContent($filters['content']);
        }

        if (!empty($filters['tags'])) {
            $query->byTags($filters['tags']);
        }

        return $query->paginate($perPage);
    }

    /**
     * {@inheritdoc}
     */
    public function getAllForExport(): Collection
    {
        // Optimized query: only select required columns
        return $this->model
            ->select(['id', 'key', 'locale', 'value'])
            ->orderBy('locale')
            ->orderBy('key')
            ->get();
    }

    /**
     * {@inheritdoc}
     */
    public function syncTags(int $translationId, array $tagIds): void
    {
        $translation = $this->model->findOrFail($translationId);
        $translation->tags()->sync($tagIds);
    }
}

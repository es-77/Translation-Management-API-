<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\Contracts\TranslationRepositoryInterface;
use Illuminate\Support\Collection;

/**
 * Service class for exporting translations as JSON.
 * Optimized for handling 100k+ records with response time under 500ms.
 */
class ExportService
{
    /**
     * Create a new service instance.
     */
    public function __construct(
        private readonly TranslationRepositoryInterface $repository
    ) {}

    /**
     * Export all translations grouped by locale and key.
     *
     * Returns a nested array structure:
     * [
     *     'en' => [
     *         'common.welcome' => 'Welcome',
     *         'common.goodbye' => 'Goodbye',
     *     ],
     *     'fr' => [
     *         'common.welcome' => 'Bienvenue',
     *         'common.goodbye' => 'Au revoir',
     *     ],
     * ]
     *
     * @return array<string, array<string, string>>
     */
    public function export(): array
    {
        $translations = $this->repository->getAllForExport();

        return $translations
            ->groupBy('locale')
            ->map(function (Collection $localeTranslations) {
                return $localeTranslations->pluck('value', 'key')->toArray();
            })
            ->toArray();
    }

    /**
     * Export translations for a specific locale.
     *
     * @return array<string, string>
     */
    public function exportByLocale(string $locale): array
    {
        $translations = $this->repository->getAllForExport();

        return $translations
            ->where('locale', $locale)
            ->pluck('value', 'key')
            ->toArray();
    }
}

<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Tag;
use App\Models\Translation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Artisan command to seed translations for performance testing.
 */
class SeedTranslationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translations:seed 
                            {--count=100000 : Number of translations to create}
                            {--batch=1000 : Batch size for inserts}
                            {--with-tags : Attach random tags to translations}
                            {--truncate : Truncate the table before seeding}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed the database with translation records for performance testing';

    /**
     * Available locales for seeding.
     *
     * @var array<string>
     */
    private const LOCALES = ['en', 'fr', 'es', 'de', 'it', 'pt', 'nl', 'ru', 'zh', 'ja'];

    /**
     * Common translation key prefixes.
     *
     * @var array<string>
     */
    private const KEY_PREFIXES = [
        'common',
        'auth',
        'validation',
        'messages',
        'errors',
        'buttons',
        'labels',
        'placeholders',
        'notifications',
        'pages',
        'forms',
        'modals',
        'menu',
        'footer',
        'header',
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $count = (int) $this->option('count');
        $batchSize = (int) $this->option('batch');
        $withTags = $this->option('with-tags');
        $truncate = $this->option('truncate');

        if ($truncate) {
            $this->warn('Truncating translations table...');
            Schema::disableForeignKeyConstraints();
            Translation::truncate();
            DB::table('tag_translation')->truncate();
            Schema::enableForeignKeyConstraints();
        }

        $this->info("Seeding {$count} translations...");

        // Create default tags if needed
        $tagIds = [];
        if ($withTags) {
            $this->createDefaultTags();
            $tagIds = Tag::pluck('id')->toArray();
        }

        // Disable query logging for performance
        DB::disableQueryLog();

        $progressBar = $this->output->createProgressBar($count);
        $progressBar->start();

        $batches = (int) ceil($count / $batchSize);
        $now = now();

        for ($batch = 0; $batch < $batches; $batch++) {
            $records = [];
            $currentBatchSize = min($batchSize, $count - ($batch * $batchSize));

            for ($i = 0; $i < $currentBatchSize; $i++) {
                $prefix = self::KEY_PREFIXES[array_rand(self::KEY_PREFIXES)];
                $locale = self::LOCALES[array_rand(self::LOCALES)];
                $uniqueId = ($batch * $batchSize) + $i;

                $records[] = [
                    'key' => "{$prefix}.key_{$uniqueId}",
                    'locale' => $locale,
                    'value' => $this->generateFakeValue($prefix),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            // Batch insert for performance
            Translation::upsert($records, ['key', 'locale'], ['value', 'updated_at']);

            // Attach tags if requested (simplified - attaches to last batch only for performance)
            if ($withTags && $batch === $batches - 1) {
                $this->attachTagsToSampleTranslations($tagIds);
            }

            $progressBar->advance($currentBatchSize);
        }

        $progressBar->finish();
        $this->newLine();
        $this->info("Successfully seeded {$count} translations!");

        if ($withTags) {
            $this->info('Tags attached to sample translations.');
        }

        return Command::SUCCESS;
    }

    /**
     * Create default tags for tagging translations.
     */
    private function createDefaultTags(): void
    {
        $tags = ['mobile', 'desktop', 'web', 'ios', 'android', 'api', 'admin', 'public'];

        foreach ($tags as $tagName) {
            Tag::firstOrCreate(['name' => $tagName]);
        }

        $this->info('Default tags created.');
    }

    /**
     * Attach random tags to sample translations.
     *
     * @param array<int> $tagIds
     */
    private function attachTagsToSampleTranslations(array $tagIds): void
    {
        $total = Translation::count();
        // Attach tags to a sample (10% or max 1000), or all if small dataset
        $sampleSize = $total < 100 ? $total : min(1000, (int) ceil($total * 0.1));
        $translations = Translation::inRandomOrder()->limit($sampleSize)->pluck('id');

        $pivotRecords = [];
        foreach ($translations as $translationId) {
            // Attach 1-3 random tags
            $randomTags = array_rand(array_flip($tagIds), min(rand(1, 3), count($tagIds)));
            $randomTags = is_array($randomTags) ? $randomTags : [$randomTags];

            foreach ($randomTags as $tagId) {
                $pivotRecords[] = [
                    'translation_id' => $translationId,
                    'tag_id' => $tagId,
                ];
            }
        }

        // Batch insert pivot records
        DB::table('tag_translation')->insertOrIgnore($pivotRecords);
    }

    /**
     * Generate a fake translation value based on the key prefix.
     */
    private function generateFakeValue(string $prefix): string
    {
        $phrases = [
            'common' => ['Welcome', 'Hello', 'Goodbye', 'Thank you', 'Please wait'],
            'auth' => ['Login', 'Logout', 'Register', 'Password', 'Email'],
            'validation' => ['This field is required', 'Invalid email', 'Password too short'],
            'messages' => ['Success!', 'Error occurred', 'Please try again', 'Loading...'],
            'errors' => ['Something went wrong', 'Not found', 'Access denied', 'Session expired'],
            'buttons' => ['Submit', 'Cancel', 'Save', 'Delete', 'Edit', 'Create'],
            'labels' => ['Name', 'Email', 'Phone', 'Address', 'Description'],
            'placeholders' => ['Enter your name', 'Enter email', 'Type here...'],
            'notifications' => ['New message', 'Update available', 'Task completed'],
            'pages' => ['Home', 'About', 'Contact', 'Dashboard', 'Settings'],
            'forms' => ['Fill in the form', 'Required fields', 'Optional'],
            'modals' => ['Confirm action', 'Are you sure?', 'Close'],
            'menu' => ['Profile', 'Settings', 'Logout', 'Help'],
            'footer' => ['Terms of Service', 'Privacy Policy', 'Contact Us'],
            'header' => ['Welcome back', 'Navigation', 'Search'],
        ];

        $options = $phrases[$prefix] ?? $phrases['common'];

        return $options[array_rand($options)] . ' ' . rand(1, 1000);
    }
}

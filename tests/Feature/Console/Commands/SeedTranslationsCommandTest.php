<?php

namespace Tests\Feature\Console\Commands;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SeedTranslationsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_seed_translations_successfully()
    {
        // Run with small count to be fast
        $this->artisan('translations:seed', ['--count' => 10, '--batch' => 5])
            ->assertSuccessful()
            ->expectsOutput('Seeding 10 translations...')
            ->expectsOutput('Successfully seeded 10 translations!');

        $this->assertDatabaseCount('translations', 10);
    }

    public function test_it_can_seed_translations_with_tags()
    {
        $this->artisan('translations:seed', ['--count' => 50, '--with-tags' => true])
            ->assertSuccessful()
            ->expectsOutput('Default tags created.')
            ->expectsOutput('Tags attached to sample translations.');

        $this->assertDatabaseCount('translations', 50);
        $this->assertDatabaseCount('tags', 8); // 8 is the number of default tags
        // Check that at least one pivot record exists
        $this->assertGreaterThan(0, \Illuminate\Support\Facades\DB::table('tag_translation')->count());
    }

    public function test_it_can_truncate_table_before_seeding()
    {
        // Seed first
        $this->artisan('translations:seed', ['--count' => 5]);
        $this->assertDatabaseCount('translations', 5);

        // Seed again with truncate
        $this->artisan('translations:seed', ['--count' => 10, '--truncate' => true])
            ->assertSuccessful()
            ->expectsOutput('Truncating translations table...')
            ->expectsOutput('Successfully seeded 10 translations!');

        $this->assertDatabaseCount('translations', 10);
    }
}

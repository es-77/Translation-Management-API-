<?php

declare(strict_types=1);

namespace Tests\Feature\Performance;

use App\Models\Translation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EndpointPerformanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Sanctum::actingAs(User::factory()->create());
    }

    public function test_list_translations_responds_under_200ms(): void
    {
        // Create a moderate dataset
        Translation::factory()->count(100)->create();

        $start = microtime(true);
        $response = $this->getJson('/api/translations?per_page=50');
        $duration = (microtime(true) - $start) * 1000;

        $response->assertOk();
        $this->assertLessThan(200, $duration, "Response time was {$duration}ms, expected under 200ms");
    }

    public function test_search_translations_responds_under_200ms(): void
    {
        Translation::factory()->count(100)->create();

        $start = microtime(true);
        $response = $this->getJson('/api/translations/search?key=common');
        $duration = (microtime(true) - $start) * 1000;

        $response->assertOk();
        $this->assertLessThan(200, $duration, "Response time was {$duration}ms, expected under 200ms");
    }

    public function test_create_translation_responds_under_200ms(): void
    {
        $start = microtime(true);
        $response = $this->postJson('/api/translations', [
            'key' => 'perf.test',
            'locale' => 'en',
            'value' => 'Performance test',
        ]);
        $duration = (microtime(true) - $start) * 1000;

        $response->assertCreated();
        $this->assertLessThan(200, $duration, "Response time was {$duration}ms, expected under 200ms");
    }

    public function test_view_translation_responds_under_200ms(): void
    {
        $translation = Translation::factory()->create();

        $start = microtime(true);
        $response = $this->getJson("/api/translations/{$translation->id}");
        $duration = (microtime(true) - $start) * 1000;

        $response->assertOk();
        $this->assertLessThan(200, $duration, "Response time was {$duration}ms, expected under 200ms");
    }

    public function test_update_translation_responds_under_200ms(): void
    {
        $translation = Translation::factory()->create();

        $start = microtime(true);
        $response = $this->putJson("/api/translations/{$translation->id}", [
            'value' => 'Updated value',
        ]);
        $duration = (microtime(true) - $start) * 1000;

        $response->assertOk();
        $this->assertLessThan(200, $duration, "Response time was {$duration}ms, expected under 200ms");
    }

    public function test_delete_translation_responds_under_200ms(): void
    {
        $translation = Translation::factory()->create();

        $start = microtime(true);
        $response = $this->deleteJson("/api/translations/{$translation->id}");
        $duration = (microtime(true) - $start) * 1000;

        $response->assertNoContent();
        $this->assertLessThan(200, $duration, "Response time was {$duration}ms, expected under 200ms");
    }

    public function test_export_with_1000_records_responds_under_500ms(): void
    {
        // Seed 1000 records for export test
        $this->seedTranslationsEfficiently(1000);

        $start = microtime(true);
        $response = $this->getJson('/api/export');
        $duration = (microtime(true) - $start) * 1000;

        $response->assertOk();
        $this->assertLessThan(500, $duration, "Export response time was {$duration}ms, expected under 500ms");
    }

    public function test_list_tags_responds_under_200ms(): void
    {
        $start = microtime(true);
        $response = $this->getJson('/api/tags');
        $duration = (microtime(true) - $start) * 1000;

        $response->assertOk();
        $this->assertLessThan(200, $duration, "Response time was {$duration}ms, expected under 200ms");
    }

    public function test_login_responds_under_200ms(): void
    {
        $user = User::factory()->create([
            'email' => 'perf@test.com',
            'password' => bcrypt('password'),
        ]);

        // Temporarily logout to test login
        $this->app['auth']->forgetGuards();

        $start = microtime(true);
        $response = $this->postJson('/api/login', [
            'email' => 'perf@test.com',
            'password' => 'password',
        ]);
        $duration = (microtime(true) - $start) * 1000;

        $response->assertOk();
        $this->assertLessThan(200, $duration, "Login response time was {$duration}ms, expected under 200ms");
    }

    /**
     * Efficiently seed translations without using factory for performance.
     */
    private function seedTranslationsEfficiently(int $count): void
    {
        $locales = ['en', 'fr', 'es', 'de', 'it'];
        $now = now();
        $records = [];

        for ($i = 0; $i < $count; $i++) {
            $records[] = [
                'key' => "perf.key_{$i}",
                'locale' => $locales[$i % count($locales)],
                'value' => "Performance test value {$i}",
                'created_at' => $now,
                'updated_at' => $now,
            ];

            // Insert in batches of 500
            if (count($records) >= 500) {
                DB::table('translations')->insert($records);
                $records = [];
            }
        }

        // Insert remaining records
        if (!empty($records)) {
            DB::table('translations')->insert($records);
        }
    }
}

<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Translation;
use App\Repositories\Contracts\TranslationRepositoryInterface;
use App\Services\ExportService;
use Illuminate\Database\Eloquent\Collection;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class ExportServiceTest extends TestCase
{
    private ExportService $service;
    private MockInterface $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = Mockery::mock(TranslationRepositoryInterface::class);
        $this->service = new ExportService($this->repository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_export_groups_translations_by_locale_and_key(): void
    {
        $translations = new Collection([
            new Translation(['key' => 'common.welcome', 'locale' => 'en', 'value' => 'Welcome']),
            new Translation(['key' => 'common.goodbye', 'locale' => 'en', 'value' => 'Goodbye']),
            new Translation(['key' => 'common.welcome', 'locale' => 'fr', 'value' => 'Bienvenue']),
            new Translation(['key' => 'common.goodbye', 'locale' => 'fr', 'value' => 'Au revoir']),
        ]);

        $this->repository
            ->shouldReceive('getAllForExport')
            ->once()
            ->andReturn($translations);

        $result = $this->service->export();

        $this->assertArrayHasKey('en', $result);
        $this->assertArrayHasKey('fr', $result);
        $this->assertEquals('Welcome', $result['en']['common.welcome']);
        $this->assertEquals('Goodbye', $result['en']['common.goodbye']);
        $this->assertEquals('Bienvenue', $result['fr']['common.welcome']);
        $this->assertEquals('Au revoir', $result['fr']['common.goodbye']);
    }

    public function test_export_by_locale_filters_correctly(): void
    {
        $translations = new Collection([
            new Translation(['key' => 'common.welcome', 'locale' => 'en', 'value' => 'Welcome']),
            new Translation(['key' => 'common.goodbye', 'locale' => 'en', 'value' => 'Goodbye']),
            new Translation(['key' => 'common.welcome', 'locale' => 'fr', 'value' => 'Bienvenue']),
        ]);

        $this->repository
            ->shouldReceive('getAllForExport')
            ->once()
            ->andReturn($translations);

        $result = $this->service->exportByLocale('en');

        $this->assertCount(2, $result);
        $this->assertEquals('Welcome', $result['common.welcome']);
        $this->assertEquals('Goodbye', $result['common.goodbye']);
        $this->assertArrayNotHasKey('common.welcome.fr', $result);
    }

    public function test_export_handles_empty_collection(): void
    {
        $this->repository
            ->shouldReceive('getAllForExport')
            ->once()
            ->andReturn(new Collection());

        $result = $this->service->export();

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }
}

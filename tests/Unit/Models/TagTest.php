<?php

namespace Tests\Unit\Models;

use App\Models\Tag;
use App\Models\Translation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_has_fillable_attributes()
    {
        $tag = new Tag();
        $this->assertEquals(['name'], $tag->getFillable());
    }

    public function test_it_has_translations_relationship()
    {
        $tag = Tag::factory()->create();
        $translation = Translation::factory()->create();

        $tag->translations()->attach($translation);

        $this->assertTrue($tag->translations->contains($translation));
    }
}

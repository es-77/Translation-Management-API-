<?php

namespace Tests\Feature\Http\Controllers;

use Tests\TestCase;

class DocsControllerTest extends TestCase
{
    public function test_it_serves_swagger_ui()
    {
        $response = $this->get('/api/docs');

        $response->assertStatus(200)
            ->assertSee('Translation API Documentation')
            ->assertSee('<div id="swagger-ui"></div>', false);
    }
}

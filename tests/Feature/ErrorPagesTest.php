<?php

namespace Tests\Feature;

use Tests\TestCase;

class ErrorPagesTest extends TestCase
{
    public function test_404_error_page_renders_branded_view(): void
    {
        $response = $this->get('/non-existent-route-999');

        $response->assertStatus(404);
        $response->assertSee('Lost in Space');
        $response->assertSee('404');
    }
}

<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_the_application_returns_a_successful_response()
    {
        // La home redirige al login del panel
        $response = $this->get('/');
        $response->assertRedirect('/admin/login');
    }
}

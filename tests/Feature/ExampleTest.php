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
        // Accede directamente a la ruta de login del panel
        $response = $this->get('/admin/login');

        // Debe devolver 200 OK
        $response->assertStatus(200);
    }
}

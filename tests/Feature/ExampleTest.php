<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /** @test */
    public function test_the_application_returns_a_successful_response()
    {
        $response = $this->withoutMiddleware()->get('/contact'); // ajustado a formulario de contacto
        $response->assertStatus(200);
    }
}

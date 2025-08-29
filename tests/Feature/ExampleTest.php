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
        // Usa la ruta de contacto que sí existe
        $response = $this->get('/contact');
        $response->assertStatus(200);
    }
}

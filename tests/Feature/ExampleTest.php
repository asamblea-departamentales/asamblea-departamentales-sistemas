<?php
namespace Tests\Feature;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Clear caches before test
        $this->artisan('route:clear');
        $this->artisan('config:clear');
    }

    /** @test */
    public function test_the_application_returns_a_successful_response()
    {
        $response = $this->get('/contact');
        $response->assertStatus(200);
        $response->assertSeeText('Contact GET works');
    }
}

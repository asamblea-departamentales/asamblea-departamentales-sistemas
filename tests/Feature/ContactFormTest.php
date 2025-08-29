<?php
namespace Tests\Feature;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactFormTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function contact_form_can_be_submitted()
    {
        $this->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);
        
        $response = $this->post('/contact', [
            'firstname' => 'John',
            'lastname'  => 'Doe',
            'email'     => 'john@example.com',
            'subject'   => 'Hello',
            'message'   => 'This is a test message with more than 10 characters.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    /** @test */
    public function validation_errors_are_returned_when_form_is_incomplete()
    {
        $this->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);
        
        $response = $this->post('/contact', []);

        $response->assertSessionHasErrors([
            'firstname',
            'lastname',
            'email',
            'subject',
            'message',
        ]);
    }
}
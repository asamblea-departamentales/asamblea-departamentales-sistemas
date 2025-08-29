namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_form_can_be_submitted()
    {
        $response = $this->withMiddleware()->post('/contact', [
            'firstname' => 'John',
            'lastname' => 'Doe',
            'email' => 'john@example.com',
            'subject' => 'Hello',
            'message' => 'This is a test message.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    public function test_validation_errors_are_returned_when_form_is_incomplete()
    {
        $response = $this->withMiddleware()->post('/contact', []);

        $response->assertSessionHasErrors([
            'firstname', 'lastname', 'email', 'subject', 'message'
        ]);
    }
}

class ExampleTest extends TestCase
{
    public function test_the_application_redirects_home()
    {
        $response = $this->get('/');
        $response->assertRedirect('/admin/login');
    }
}

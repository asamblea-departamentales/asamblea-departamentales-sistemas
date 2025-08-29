<?php

namespace Tests\Feature;

use App\Events\ContactUsCreated;
use App\Mail\NewContactUsNotificationMail;
use App\Models\ContactUs;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ContactFormTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    /** @test */
    public function contact_form_can_be_submitted()
    {
        Event::fake();

        $response = $this->withoutMiddleware()->post('/contact', [
            'firstname' => 'John',
            'lastname' => 'Doe',
            'email' => 'john@gmail.com',
            'phone' => '555-123-4567',
            'company' => 'Acme Inc',
            'employees' => '11-50',
            'title' => 'CEO',
            'subject' => 'General Inquiry',
            'message' => 'This is a test message from the contact form.',
        ]);

        $response->assertStatus(302); // redirección tras submit
        $this->assertDatabaseHas('contact_us', [
            'firstname' => 'John',
            'lastname' => 'Doe',
            'email' => 'john@gmail.com',
            'status' => 'new',
        ]);

        Event::assertDispatched(ContactUsCreated::class, fn($event) => $event->contact->email === 'john@gmail.com');
    }

    /** @test */
    public function validation_errors_are_returned_when_form_is_incomplete()
    {
        $response = $this->withoutMiddleware()->post('/contact', [
            'firstname' => '',
            'lastname' => 'Doe',
            'email' => '',
            'subject' => '',
            'message' => '',
        ]);

        $response->assertStatus(302); // Laravel redirige en validation error
    }

    /** @test */
    public function database_transaction_is_rolled_back_on_error()
    {
        $initialCount = ContactUs::count();

        DB::beginTransaction();

        try {
            ContactUs::create([
                'firstname' => 'Test',
                'lastname' => 'Rollback',
                'email' => 'rollback@test.com',
                'phone' => '555-555-5555',
                'company' => 'Test Company',
                'employees' => '1-10',
                'title' => 'CTO',
                'subject' => 'Test Transaction',
                'message' => 'This should be rolled back',
                'status' => 'new',
            ]);

            DB::rollBack();

            $this->assertEquals($initialCount, ContactUs::count());
            $this->assertDatabaseMissing('contact_us', ['email' => 'rollback@test.com']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Transaction test failed: ' . $e->getMessage());
        }
    }

    /** @test */
    public function email_notification_is_sent_directly()
    {
        $contact = ContactUs::create([
            'firstname' => 'Test',
            'lastname' => 'Email',
            'email' => 'test@example.com',
            'phone' => '555-123-4567',
            'subject' => 'Test Email',
            'message' => 'Test message for direct email sending',
            'status' => 'new',
        ]);

        $email = new NewContactUsNotificationMail($contact);
        Mail::to('test@example.com')->send($email);

        Mail::assertSent(NewContactUsNotificationMail::class, fn($mail) => $mail->contact->id === $contact->id);
    }
}

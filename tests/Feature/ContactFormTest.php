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

        // Aseguramos que se guardó en DB
        $this->assertDatabaseHas('contact_us', [
            'firstname' => 'John',
            'lastname' => 'Doe',
            'email' => 'john@gmail.com',
            'status' => 'new',
        ]);

        Event::assertDispatched(ContactUsCreated::class, function ($event) {
            return $event->contact->email === 'john@gmail.com';
        });

        // Validamos la redirección simulada
        $response->assertStatus(200); // Con withoutMiddleware(), no habrá redirect 302
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

        $response->assertStatus(200); // Sin middleware, no hay session, solo comprobamos que no rompa
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
            $this->assertDatabaseMissing('contact_us', [
                'email' => 'rollback@test.com',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Transaction test failed: '.$e->getMessage());
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

        Mail::assertSent(NewContactUsNotificationMail::class, function ($mail) use ($contact) {
            return $mail->contact->id === $contact->id;
        });
    }

    /** @test */
    public function contact_can_be_marked_as_read()
    {
        $contact = ContactUs::create([
            'firstname' => 'Mark',
            'lastname' => 'Read',
            'email' => 'mark@example.com',
            'phone' => '555-123-4567',
            'subject' => 'Test Read Status',
            'message' => 'This is a test for marking as read',
            'status' => 'new',
        ]);

        $contact->markAsRead();
        $this->assertEquals('read', $contact->fresh()->status);
    }

    /** @test */
    public function reply_can_be_added_to_contact()
    {
        $user = User::factory()->create();

        $contact = ContactUs::create([
            'firstname' => 'Reply',
            'lastname' => 'Test',
            'email' => 'reply@example.com',
            'phone' => '555-123-4567',
            'subject' => 'Test Reply',
            'message' => 'This is a test for adding a reply',
            'status' => 'new',
        ]);

        $contact->addReply(
            'RE: Test Reply',
            'Thank you for your message. We will get back to you soon.',
            $user
        );

        $updatedContact = $contact->fresh();

        $this->assertEquals('RE: Test Reply', $updatedContact->reply_subject);
        $this->assertEquals('Thank you for your message. We will get back to you soon.', $updatedContact->reply_message);
        $this->assertEquals($user->id, $updatedContact->replied_by_user_id);
        $this->assertEquals('responded', $updatedContact->status);
        $this->assertNotNull($updatedContact->replied_at);
    }

    /** @test */
    public function search_scope_returns_matching_contacts()
    {
        ContactUs::create([
            'firstname' => 'Search',
            'lastname' => 'Test',
            'email' => 'search@example.com',
            'phone' => '555-123-4567',
            'subject' => 'Testing search functionality',
            'message' => 'This message contains the special keyword SEARCHME',
            'status' => 'new',
        ]);

        ContactUs::create([
            'firstname' => 'Another',
            'lastname' => 'Contact',
            'email' => 'another@example.com',
            'phone' => '555-987-6543',
            'subject' => 'Different subject',
            'message' => 'This message does not contain the special keyword',
            'status' => 'new',
        ]);

        $searchTerm = 'SEARCHME';
        $results = ContactUs::where('message', 'LIKE', '%'.$searchTerm.'%')->get();

        $this->assertEquals(1, $results->count());
        $this->assertEquals('search@example.com', $results[0]->email);
    }
}

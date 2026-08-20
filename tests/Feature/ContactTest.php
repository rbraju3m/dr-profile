<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_visitor_can_send_a_message(): void
    {
        $response = $this->post('/en/contact', [
            'name' => 'Karim Ahmed',
            'phone' => '01712345678',
            'email' => 'karim@example.com',
            'subject' => 'Appointment query',
            'message' => 'I would like to know the chamber timings for next week.',
        ]);

        $response->assertRedirect('/en/contact')->assertSessionHas('success');

        $this->assertDatabaseHas('contact_messages', [
            'name' => 'Karim Ahmed',
            'phone' => '01712345678',
            'is_read' => false,
        ]);
    }

    public function test_the_honeypot_blocks_automated_submissions(): void
    {
        $this->post('/en/contact', [
            'name' => 'Spam Bot',
            'phone' => '01712345678',
            'message' => 'Buy cheap things from this link right now.',
            'website' => 'http://spam.example',
        ])->assertSessionHasErrors('website');

        $this->assertSame(0, ContactMessage::count());
    }

    public function test_a_short_message_is_rejected(): void
    {
        $this->post('/en/contact', [
            'name' => 'Karim Ahmed',
            'phone' => '01712345678',
            'message' => 'hi',
        ])->assertSessionHasErrors('message');

        $this->assertSame(0, ContactMessage::count());
    }
}

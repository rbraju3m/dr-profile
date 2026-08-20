<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * The language is not a property here: Mailable already declares $locale, and
 * redeclaring it — even via constructor promotion — is a fatal at class-load
 * time. Callers set it with Mail::to(...)->locale(...) instead.
 */
class AppointmentConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Appointment $appointment) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('site.booking.success_heading').' — '.$this->appointment->appointment_no,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.appointment-confirmation',
            with: ['appointment' => $this->appointment->loadMissing('chamber')],
        );
    }
}

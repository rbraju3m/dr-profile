<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AppointmentConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Appointment $appointment, public string $locale) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('site.booking.success_heading', [], $this->locale).' — '.$this->appointment->appointment_no,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.appointment-confirmation',
            with: ['appointment' => $this->appointment->loadMissing('chamber')],
        );
    }
}

<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeUserMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $trialDays;

    /**
     * Create a new message instance.
     */
    public function __construct($user, $trialDays = 14)
    {
        $this->user = $user;
        $this->trialDays = $trialDays;
    }

    /**
     * Email Subject
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome to stockflowkp - Your Duka Management System',
        );
    }

    /**
     * Email Content
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.welcome',   // our email blade file
            with: [
                'user' => $this->user,
                'trialDays' => $this->trialDays,
            ],
        );
    }

    /**
     * Attachments
     */
    public function attachments(): array
    {
        return [];
    }
}

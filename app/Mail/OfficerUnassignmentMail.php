<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OfficerUnassignmentMail extends Mailable
{
    use Queueable, SerializesModels;

    public $officer;
    public $duka;
    public $tenant;

    /**
     * Create a new message instance.
     */
    public function __construct($officer, $duka, $tenant)
    {
        $this->officer = $officer;
        $this->duka = $duka;
        $this->tenant = $tenant;
    }

    /**
     * Email Subject
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'You have been unassigned from a Duka - stockflowkp',
        );
    }

    /**
     * Email Content
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.officer-unassignment',
            with: [
                'officer' => $this->officer,
                'duka' => $this->duka,
                'tenant' => $this->tenant,
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

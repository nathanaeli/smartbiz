<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OfficerAssignmentMail extends Mailable
{
    use Queueable, SerializesModels;

    public $officer;
    public $duka;
    public $assignment;
    public $tenant;

    /**
     * Create a new message instance.
     */
    public function __construct($officer, $duka, $assignment, $tenant)
    {
        $this->officer = $officer;
        $this->duka = $duka;
        $this->assignment = $assignment;
        $this->tenant = $tenant;
    }

    /**
     * Email Subject
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'You have been assigned to a Duka - stockflowkp',
        );
    }

    /**
     * Email Content
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.officer-assignment',
            with: [
                'officer' => $this->officer,
                'duka' => $this->duka,
                'assignment' => $this->assignment,
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

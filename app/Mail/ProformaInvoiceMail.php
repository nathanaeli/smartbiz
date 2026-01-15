<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProformaInvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public $proformaInvoice;

    /**
     * Create a new message instance.
     */
    public function __construct($proformaInvoice)
    {
        $this->proformaInvoice = $proformaInvoice;
    }

    /**
     * Email Subject
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Proforma Invoice - ' . $this->proformaInvoice->invoice_number,
        );
    }

    /**
     * Email Content
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.proforma-invoice',
            with: [
                'proformaInvoice' => $this->proformaInvoice,
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

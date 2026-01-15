<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LoanReminder extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $loanData;
    public $messageType;

    /**
     * Create a new message instance.
     */
    public function __construct($loanData, $messageType = 'reminder')
    {
        $this->loanData = $loanData;
        $this->messageType = $messageType;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->getSubject();
        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.loan-reminder',
            with: [
                'loanData' => $this->loanData,
                'messageType' => $this->messageType,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }

    private function getSubject()
    {
        switch ($this->messageType) {
            case 'overdue_warning':
                return 'Urgent: Your Loan Payment is Overdue - ' . $this->loanData['duka_name'];
            case 'final_notice':
                return 'FINAL NOTICE: Immediate Payment Required - ' . $this->loanData['duka_name'];
            case 'payment_confirmation':
                return 'Payment Confirmation - ' . $this->loanData['duka_name'];
            default:
                return 'Loan Payment Reminder - ' . $this->loanData['duka_name'];
        }
    }
}

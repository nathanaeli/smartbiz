<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class LoginNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $ip;
    public $userAgent;
    public $location;
    public $device;
    public $isNewDevice;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, $ip, $userAgent, $isNewDevice = false)
    {
        $this->user = $user;
        $this->ip = $ip;
        $this->userAgent = $userAgent;
        $this->isNewDevice = $isNewDevice;

        // Parse user agent for device info
        $this->parseUserAgent();

        // Get location from IP (you might want to use a service like ipapi.co)
        $this->location = $this->getLocationFromIP($ip);
    }

    protected function parseUserAgent()
    {
        $ua = $this->userAgent;

        if (strpos($ua, 'Mobile') !== false) {
            $this->device = 'Mobile Device';
        } elseif (strpos($ua, 'Tablet') !== false) {
            $this->device = 'Tablet';
        } else {
            $this->device = 'Desktop Computer';
        }

        // You can enhance this with a proper user agent parser library
    }

    protected function getLocationFromIP($ip)
    {
        // For demo purposes, return a placeholder
        // In production, use a service like ipapi.co or maxmind
        return 'Unknown Location';
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->isNewDevice ?
            'New Device Login Alert - stockflowkp' :
            'Login Notification - stockflowkp';

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
            view: 'emails.login_notification',
            with: [
                'user' => $this->user,
                'ip' => $this->ip,
                'device' => $this->device,
                'location' => $this->location,
                'isNewDevice' => $this->isNewDevice,
                'loginTime' => now(),
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
}

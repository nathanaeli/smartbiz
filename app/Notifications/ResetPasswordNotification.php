<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;

class ResetPasswordNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $token;
    public $isApiRequest;

    /**
     * Create a new notification instance.
     */
    public function __construct($token, $isApiRequest = false)
    {
        $this->token = $token;
        $this->isApiRequest = $isApiRequest;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $email = $notifiable->getEmailForPasswordReset();

        if ($this->isApiRequest) {
            // API-based reset link with frontend URL
            $frontendUrl = config('app.frontend_url');

            // Fallback to APP_URL if FRONTEND_URL is not set or is a placeholder
            if (!$frontendUrl || strpos($frontendUrl, 'your-frontend-domain.com') !== false) {
                $frontendUrl = config('app.url') . '/reset-password';
            }

            $resetUrl = $frontendUrl . '?token=' . $this->token . '&email=' . urlencode($email);
        } else {
            // Web-based reset link
            $resetUrl = url(route('password.reset', [
                'token' => $this->token,
                'email' => $email,
            ], false));
        }

        $greeting = 'Hello ' . ($notifiable->name ?? 'User') . '!';
        $subject = 'Reset Your SmartBiz Password';
        $actionText = 'Reset Password';
        $introLine = 'You are receiving this email because we received a password reset request for your SmartBiz account.';
        $expiryLine = 'This password reset link will expire in 60 minutes.';
        $securityLine = 'If you did not request a password reset, no further action is required.';
        $salutation = 'Best regards, SmartBiz Team';

        return (new MailMessage)
            ->subject($subject)
            ->greeting($greeting)
            ->line($introLine)
            ->action($actionText, $resetUrl)
            ->line($expiryLine)
            ->line($securityLine)
            ->salutation($salutation);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'token' => $this->token,
            'is_api_request' => $this->isApiRequest,
        ];
    }
}

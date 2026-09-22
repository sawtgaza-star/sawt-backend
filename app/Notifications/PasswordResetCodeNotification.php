<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Password-reset OTP email.
 * Template: emails/auth/password-reset.blade.php
 */
class PasswordResetCodeNotification extends Notification
{
    public function __construct(
        public string $code,
        public int $expiresInSeconds = 60,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $minutes = max(1, (int) ceil($this->expiresInSeconds / 60));

        return (new MailMessage)
            ->subject('رمز إعادة تعيين كلمة المرور — صوت')
            ->view('emails.auth.password-reset', [
                'name' => $notifiable->name ?? '',
                'code' => $this->code,
                'minutes' => $minutes,
            ]);
    }
}

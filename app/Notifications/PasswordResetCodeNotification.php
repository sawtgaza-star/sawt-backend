<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

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
            ->greeting('مرحباً '.($notifiable->name ?: ''))
            ->line('رمز التحقق لإعادة تعيين كلمة المرور هو:')
            ->line('**'.$this->code.'**')
            ->line('ينتهي الرمز خلال '.$minutes.' دقيقة.')
            ->line('إذا لم تطلب إعادة تعيين كلمة المرور، يمكنك تجاهل هذه الرسالة.');
    }
}

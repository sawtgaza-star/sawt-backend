<?php

namespace App\Notifications;

use App\Models\CreatorJoinRequest;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CreatorJoinAcceptedNotification extends Notification
{
    public function __construct(
        public CreatorJoinRequest $joinRequest,
        public ?string $temporaryPassword = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $name = $notifiable->name ?: $this->joinRequest->full_name;

        $mail = (new MailMessage)
            ->subject('تم قبول طلب انضمامك كصانع محتوى — صوت')
            ->greeting('مرحباً '.$name)
            ->line('يسعدنا إخبارك بأنه تم قبول طلب انضمامك كصانع محتوى في منصة صوت.')
            ->line('يمكنك الآن تسجيل الدخول إلى حسابك.');

        if ($this->temporaryPassword) {
            $mail->line('البريد الإلكتروني: '.$notifiable->email)
                ->line('كلمة المرور المؤقتة: '.$this->temporaryPassword)
                ->line('يُفضَّل تغيير كلمة المرور بعد أول تسجيل دخول.');
        } else {
            $mail->line('استخدم البريد الإلكتروني وكلمة المرور الحاليين لتسجيل الدخول.');
        }

        return $mail
            ->action('تسجيل الدخول', url('/login'))
            ->line('شكراً لانضمامك إلى منصة صوت.');
    }
}

<?php

namespace App\Notifications;

use App\Models\MediaConsultationRequest;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Email sent when admin accepts a media consultation booking.
 */
class MediaConsultationAcceptedNotification extends Notification
{
    public function __construct(
        public MediaConsultationRequest $request,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $name = $this->request->name ?: 'عزيزي العميل';
        $service = $this->request->service_title ?: 'الخدمة المطلوبة';

        return (new MailMessage)
            ->subject('تم قبول طلب استشارتك — صوت ميديا')
            ->greeting('مرحباً '.$name)
            ->line('يسعدنا إخبارك بأنه تم قبول طلب حجز الاستشارة الخاص بك.')
            ->line('الخدمة: '.$service)
            ->line('رقم الطلب: '.$this->request->uuid)
            ->line('سيتواصل معك فريق صوت ميديا قريباً لتحديد الموعد والتفاصيل.')
            ->line('شكراً لثقتك بصوت ميديا.');
    }
}

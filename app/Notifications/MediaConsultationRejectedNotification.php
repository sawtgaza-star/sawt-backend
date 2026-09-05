<?php

namespace App\Notifications;

use App\Models\MediaConsultationRequest;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Email sent when admin rejects a media consultation booking.
 */
class MediaConsultationRejectedNotification extends Notification
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
        $reason = trim((string) $this->request->admin_note);

        $mail = (new MailMessage)
            ->subject('بخصوص طلب استشارتك — صوت ميديا')
            ->greeting('مرحباً '.$name)
            ->line('نشكرك على اهتمامك بصوت ميديا.')
            ->line('نأسف لإبلاغك بأنه تعذّر قبول طلب حجز الاستشارة في الوقت الحالي.')
            ->line('الخدمة: '.$service)
            ->line('رقم الطلب: '.$this->request->uuid);

        if ($reason !== '') {
            $mail->line('السبب: '.$reason);
        }

        return $mail->line('يمكنك التواصل معنا لاحقاً أو تقديم طلب جديد. نتمنى لك التوفيق.');
    }
}

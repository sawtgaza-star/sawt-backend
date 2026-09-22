<?php

namespace App\Notifications;

use App\Models\MediaConsultationRequest;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Email sent when admin accepts a media consultation booking.
 * Template: emails/media/accepted.blade.php
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
            ->view('emails.media.accepted', [
                'name' => $name,
                'service' => $service,
                'requestUuid' => (string) $this->request->uuid,
            ]);
    }
}

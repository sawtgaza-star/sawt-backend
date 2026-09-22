<?php

namespace App\Notifications;

use App\Models\MediaConsultationRequest;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Email sent when admin rejects a media consultation booking.
 * Template: emails/media/rejected.blade.php
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
        $reason = trim((string) ($this->request->admin_note ?? ''));

        return (new MailMessage)
            ->subject('بخصوص طلب استشارتك — صوت ميديا')
            ->view('emails.media.rejected', [
                'name' => $name,
                'service' => $service,
                'requestUuid' => (string) $this->request->uuid,
                'reason' => $reason !== '' ? $reason : null,
            ]);
    }
}

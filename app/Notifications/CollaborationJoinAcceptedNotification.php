<?php

namespace App\Notifications;

use App\Models\CollaborationJoinRequest;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CollaborationJoinAcceptedNotification extends Notification
{
    public function __construct(
        public CollaborationJoinRequest $request,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $name = $this->request->company_name ?: 'شريكنا';

        $typeLabel = $this->request->type instanceof \App\Enums\CollaborationTypeKey
            ? $this->request->type->labelAr()
            : (string) $this->request->type;

        return (new MailMessage)
            ->subject('تم قبول طلب التعاون — منصة صوت')
            ->greeting('مرحباً '.$name)
            ->line('يسعدنا إخبارك بأنه تم قبول طلب التعاون الخاص بكم ('.$typeLabel.').')
            ->line('سيتواصل معكم فريق منصة صوت قريباً لمتابعة الخطوات التالية.')
            ->line('رقم الطلب: '.$this->request->uuid)
            ->line('شكراً لاهتمامكم بالتعاون مع منصة صوت.');
    }
}

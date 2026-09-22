<?php

namespace App\Notifications;

use App\Support\FrontendUrl;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Email when admin accepts a creator join request.
 * Template: emails/creators/accepted.blade.php
 *
 * Does not require CreatorJoinRequest — that row is deleted on approve.
 */
class CreatorJoinAcceptedNotification extends Notification
{
    public function __construct(
        public string $applicantName,
        public ?string $temporaryPassword = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $name = $this->applicantName ?: ($notifiable->name ?? 'عزيزي صانع المحتوى');

        return (new MailMessage)
            ->subject('تم قبول طلب انضمامك كصانع محتوى — صوت')
            ->view('emails.creators.accepted', [
                'name' => $name,
                'email' => $notifiable->email ?? null,
                'temporaryPassword' => $this->temporaryPassword,
                'loginUrl' => FrontendUrl::login(),
            ]);
    }
}

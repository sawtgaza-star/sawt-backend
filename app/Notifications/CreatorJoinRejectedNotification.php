<?php

namespace App\Notifications;

use App\Models\CreatorJoinRequest;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Email when admin rejects a creator join request.
 * Template: emails/creators/rejected.blade.php
 */
class CreatorJoinRejectedNotification extends Notification
{
    public function __construct(
        public CreatorJoinRequest $joinRequest,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $name = $notifiable->name
            ?: $this->joinRequest->full_name
            ?: 'عزيزي صانع المحتوى';

        $reason = trim((string) ($this->joinRequest->admin_note ?? ''));

        return (new MailMessage)
            ->subject('بخصوص طلب انضمامك كصانع محتوى — صوت')
            ->view('emails.creators.rejected', [
                'name' => $name,
                'reason' => $reason !== '' ? $reason : null,
            ]);
    }
}

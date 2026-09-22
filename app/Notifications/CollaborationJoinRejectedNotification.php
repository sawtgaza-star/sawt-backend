<?php

namespace App\Notifications;

use App\Models\CollaborationJoinRequest;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Email when admin rejects a collaboration join request.
 * Template: emails/collaboration/rejected.blade.php
 */
class CollaborationJoinRejectedNotification extends Notification
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

        $reason = trim((string) ($this->request->admin_note ?? ''));

        return (new MailMessage)
            ->subject('بخصوص طلب التعاون — منصة صوت')
            ->view('emails.collaboration.rejected', [
                'name' => $name,
                'typeLabel' => $typeLabel,
                'requestUuid' => (string) $this->request->uuid,
                'reason' => $reason !== '' ? $reason : null,
            ]);
    }
}

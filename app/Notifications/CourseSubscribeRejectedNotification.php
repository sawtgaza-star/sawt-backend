<?php

namespace App\Notifications;

use App\Models\CourseSubscribeRequest;
use App\Support\FrontendUrl;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Email sent when admin rejects a guest course subscribe request.
 * Template: emails/courses/subscribe/rejected.blade.php
 */
class CourseSubscribeRejectedNotification extends Notification
{
    public function __construct(public CourseSubscribeRequest $subscribeRequest) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $course = $this->subscribeRequest->course;
        $title = method_exists($course, 'getTranslation')
            ? ($course->getTranslation('title', 'ar') ?: $course->getTranslation('title', 'en') ?: $course->slug)
            : (string) ($course->title ?? $course->slug);

        $name = $this->subscribeRequest->full_name ?: 'عزيزي المتدرب';
        $reason = trim((string) ($this->subscribeRequest->admin_notes ?? ''));

        return (new MailMessage)
            ->subject('بخصوص طلب اشتراكك في الدورة — '.$title)
            ->view('emails.courses.subscribe.rejected', [
                'name' => $name,
                'courseTitle' => $title,
                'reason' => $reason !== '' ? $reason : null,
                'incubatorUrl' => FrontendUrl::incubator(),
            ]);
    }
}

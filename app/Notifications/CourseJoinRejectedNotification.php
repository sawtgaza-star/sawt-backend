<?php

namespace App\Notifications;

use App\Models\CourseJoinRequest;
use App\Support\FrontendUrl;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Email body for a rejected course join / waitlist request.
 * Sent from SendCourseJoinStatusEmailJob — CTA links use FRONTEND_URL (production site).
 */
class CourseJoinRejectedNotification extends Notification
{
    public function __construct(public CourseJoinRequest $joinRequest) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $course = $this->joinRequest->course;
        $title = method_exists($course, 'getTranslation')
            ? ($course->getTranslation('title', 'ar') ?: $course->getTranslation('title', 'en') ?: $course->slug)
            : (string) ($course->title ?? $course->slug);

        $name = $notifiable->name
            ?: $this->joinRequest->full_name
            ?: 'عزيزي المتدرب';

        $reason = trim((string) ($this->joinRequest->admin_notes ?? ''));

        return (new MailMessage)
            ->subject('بخصوص طلب انضمامك للكورس — '.$title)
            ->view('emails.course-join-rejected', [
                'name' => $name,
                'courseTitle' => $title,
                'reason' => $reason !== '' ? $reason : null,
                'incubatorUrl' => FrontendUrl::incubator(),
            ]);
    }
}

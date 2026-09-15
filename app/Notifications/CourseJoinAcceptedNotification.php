<?php

namespace App\Notifications;

use App\Models\CourseJoinRequest;
use App\Support\FrontendUrl;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Email body for an accepted course join / waitlist request.
 * Sent from SendCourseJoinStatusEmailJob — CTA links use FRONTEND_URL (production site).
 */
class CourseJoinAcceptedNotification extends Notification
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
            ?? $this->joinRequest->full_name
            ?? 'عزيزي';

        $location = $course->location
            ? $course->location.($course->location_details ? ' — '.$course->location_details : '')
            : null;

        $startsAt = $course->starts_at
            ? $course->starts_at->timezone(config('app.timezone'))->format('Y-m-d H:i')
            : null;

        $slug = (string) ($course->slug ?: $course->uuid);

        return (new MailMessage)
            ->subject('تم قبول طلب انضمامك للكورس — '.$title)
            ->view('emails.course-join-accepted', [
                'name' => $name,
                'courseTitle' => $title,
                'location' => $location,
                'startsAt' => $startsAt,
                'courseUrl' => FrontendUrl::course($slug),
                'incubatorUrl' => FrontendUrl::incubator(),
            ]);
    }
}

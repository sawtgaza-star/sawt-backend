<?php

namespace App\Notifications;

use App\Models\CourseSubscribeRequest;
use App\Support\FrontendUrl;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Email sent when admin accepts a guest course subscribe request.
 * Template: emails/courses/subscribe/accepted.blade.php
 */
class CourseSubscribeAcceptedNotification extends Notification
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

        $name = $this->subscribeRequest->full_name ?: 'عزيزي';

        $location = $course->location
            ? $course->location.($course->location_details ? ' — '.$course->location_details : '')
            : null;

        $startsAt = $course->starts_at
            ? $course->starts_at->timezone(config('app.timezone'))->format('Y-m-d H:i')
            : null;

        $slug = (string) ($course->slug ?: $course->uuid);

        return (new MailMessage)
            ->subject('تم قبول اشتراكك في الدورة — '.$title)
            ->view('emails.courses.subscribe.accepted', [
                'name' => $name,
                'courseTitle' => $title,
                'location' => $location,
                'startsAt' => $startsAt,
                'courseUrl' => FrontendUrl::course($slug),
                'incubatorUrl' => FrontendUrl::incubator(),
            ]);
    }
}

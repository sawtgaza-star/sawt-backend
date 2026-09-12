<?php

namespace App\Notifications;

use App\Models\CourseJoinRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Email when admin rejects a course join / waitlist request.
 */
class CourseJoinRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

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

        $mail = (new MailMessage)
            ->subject('بخصوص طلب انضمامك للكورس — '.$title)
            ->greeting('مرحباً '.$name)
            ->line('نشكرك على اهتمامك بحاضنة صوت.')
            ->line('نأسف لإبلاغك بأنه تعذّر قبول طلب انضمامك إلى الكورس في الوقت الحالي.')
            ->line('الكورس: '.$title);

        if ($reason !== '') {
            $mail->line('السبب: '.$reason);
        }

        return $mail
            ->line('يمكنك تصفح كورسات أخرى أو تقديم طلب جديد لاحقاً.')
            ->line('نتمنى لك التوفيق — فريق صوت.');
    }
}

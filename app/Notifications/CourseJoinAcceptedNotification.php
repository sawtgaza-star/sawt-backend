<?php

namespace App\Notifications;

use App\Models\CourseJoinRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CourseJoinAcceptedNotification extends Notification implements ShouldQueue
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

        $mail = (new MailMessage)
            ->subject('تم قبول طلب انضمامك للكورس — '.$title)
            ->greeting('مرحباً '.($notifiable->name ?? ''))
            ->line('تم قبول طلب انضمامك إلى الكورس: '.$title)
            ->line('يمكنك التواصل مع فريق صوت لمعرفة تفاصيل الحضور والمواعيد.');

        if ($course->location) {
            $mail->line('المكان: '.$course->location.($course->location_details ? ' — '.$course->location_details : ''));
        }

        if ($course->starts_at) {
            $mail->line('تاريخ البدء: '.$course->starts_at->format('Y-m-d H:i'));
        }

        return $mail
            ->action('عرض الكورس', url('/courses/'.$course->uuid))
            ->line('شكراً لانضمامك إلى منصة صوت.');
    }
}

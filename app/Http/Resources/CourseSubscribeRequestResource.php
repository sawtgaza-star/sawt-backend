<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Success payload after guest course subscribe — confirmation modal shape.
 */
class CourseSubscribeRequestResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $course = $this->course;
        $userName = (string) ($this->full_name ?: '');
        $email = (string) ($this->email ?: '');
        $masked = $this->maskEmail($email);
        $firstName = $this->firstName($userName);

        return [
            'uuid' => $this->uuid,
            'status' => $this->status,
            'full_name' => $this->full_name,
            'phone' => $this->phone,
            'phone_country_code' => $this->phone_country_code,
            'email' => $this->email,
            'email_masked' => $masked,
            'academic_level' => $this->academic_level,
            'attended_similar_course' => (bool) $this->attended_similar_course,
            'goals_interests' => $this->goals_interests,
            'join_goal' => $this->join_goal,
            'additional_notes' => $this->additional_notes,
            'course' => $course ? [
                'uuid' => $course->uuid,
                'slug' => $course->slug,
                'title' => $course->getTranslations('title'),
            ] : null,
            'created_at' => $this->created_at?->toIso8601String(),
            'confirmation' => [
                'title' => 'مرحباً '.$firstName.'، تم إرسال طلب اشتراكك بنجاح',
                'subtitle' => 'طلبك قيد المراجعة من فريق صوت، وسنخبرك بالنتيجة عبر بريدك الإلكتروني عند القبول.',
                'user_name' => $userName,
                'course_name' => $course?->getTranslations('title') ?? ['ar' => '', 'en' => ''],
                'course_status' => [
                    'key' => 'pending_review',
                    'label' => ['ar' => 'بانتظار المراجعة', 'en' => 'Pending review'],
                ],
                'email_notice' => $masked
                    ? 'سيصلك إشعار على بريدك عند القبول: '.$masked
                    : 'سيصلك إشعار على بريدك الإلكتروني عند قبول الطلب.',
                'email_masked' => $masked,
                'cta' => [
                    'key' => 'browse_courses',
                    'label' => [
                        'ar' => 'تصفح كورسات ثانية',
                        'en' => 'Browse other courses',
                    ],
                    'path' => '/api/v1/pages/courses',
                    'url' => '/incubator',
                ],
            ],
        ];
    }

    protected function firstName(string $fullName): string
    {
        $fullName = trim($fullName);
        if ($fullName === '') {
            return 'عزيزي';
        }

        $parts = preg_split('/\s+/u', $fullName) ?: [];

        return $parts[0] ?: $fullName;
    }

    protected function maskEmail(string $email): ?string
    {
        $email = trim($email);
        if ($email === '' || ! str_contains($email, '@')) {
            return null;
        }

        [$local, $domain] = explode('@', $email, 2);
        if ($local === '' || $domain === '') {
            return null;
        }

        return mb_substr($local, 0, 1).'***@'.$domain;
    }
}

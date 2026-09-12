<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Success payload after waitlist/enroll — shaped for the confirmation modal.
 *
 * Front fields: greeting title, course name/status cards, masked email notice, browse CTA.
 */
class CourseJoinRequestResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $course = $this->course;
        $comingSoon = (bool) ($course?->is_coming_soon);
        $userName = (string) ($this->full_name ?: $this->user?->name ?: '');
        $email = (string) ($this->email ?: $this->user?->email ?: '');
        $masked = $this->maskEmail($email);

        $firstName = $this->firstName($userName);

        // Modal copy matches design for waitlist; enroll uses review-pending wording
        $title = $comingSoon
            ? 'مرحباً '.$firstName.'، تم تسجيلك بقائمة الانتظار بنجاح'
            : 'مرحباً '.$firstName.'، تم إرسال طلب انضمامك بنجاح';

        $subtitle = $comingSoon
            ? 'الكورس حالياً قيد الإعداد، تم إضافتك إلى قائمة الانتظار وسنتواصل معك فور توفره.'
            : 'طلبك قيد المراجعة من فريق صوت، وسنخبرك بالنتيجة عبر بريدك الإلكتروني.';

        $courseStatus = $comingSoon
            ? [
                'key' => 'preparing',
                'label' => ['ar' => 'قيد الإعداد', 'en' => 'Under preparation'],
            ]
            : [
                'key' => 'pending_review',
                'label' => ['ar' => 'بانتظار المراجعة', 'en' => 'Pending review'],
            ];

        return [
            'uuid' => $this->uuid,
            'status' => $this->status,
            'full_name' => $this->full_name,
            'phone' => $this->phone,
            'email' => $this->email,
            'email_masked' => $masked,
            'message' => $this->message,
            'course' => $course ? [
                'uuid' => $course->uuid,
                'slug' => $course->slug,
                'title' => $course->getTranslations('title'),
                'is_coming_soon' => $comingSoon,
            ] : null,
            'created_at' => $this->created_at?->toIso8601String(),

            // Confirmation modal (design: تأكيد الحجز / قائمة الانتظار)
            'confirmation' => [
                'title' => $title,
                'subtitle' => $subtitle,
                'user_name' => $userName,
                'course_name' => $course?->getTranslations('title') ?? ['ar' => '', 'en' => ''],
                'course_status' => $courseStatus,
                'email_notice' => $masked
                    ? 'سيصلك إشعار على بريدك: '.$masked
                    : 'سيصلك إشعار على بريدك الإلكتروني عند توفر المستجدات.',
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

    /** First token of the display name for the greeting line. */
    protected function firstName(string $fullName): string
    {
        $fullName = trim($fullName);
        if ($fullName === '') {
            return 'عزيزي';
        }

        $parts = preg_split('/\s+/u', $fullName) ?: [];

        return $parts[0] ?: $fullName;
    }

    /** Mask like U***@Email.Com for the modal banner. */
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

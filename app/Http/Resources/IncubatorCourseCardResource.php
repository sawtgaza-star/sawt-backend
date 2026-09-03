<?php

namespace App\Http\Resources;

use App\Support\MediaUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Lean course card for incubator listing / courses index (not full detail).
 *
 * Cover `image` is the incubator card image only; detail page visuals are front-owned.
 * CTA switches to waitlist when `is_coming_soon` is true.
 */
class IncubatorCourseCardResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $level = (string) ($this->level ?? 'beginner');
        $comingSoon = (bool) ($this->is_coming_soon ?? false);

        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'slug' => $this->slug,
            'title' => $this->getTranslations('title'),
            'description' => $this->getTranslations('description'),
            'image_url' => MediaUrl::make($this->image),
            'category' => $this->whenLoaded('courseCategory', fn () => $this->courseCategory
                ? $this->courseCategory->getTranslations('name')
                : null),
            'trainer' => $this->whenLoaded('trainer', fn () => $this->trainer ? [
                'name' => $this->trainer->getTranslations('name'),
                'avatar_url' => $this->trainer->avatar_url,
            ] : null),
            'level' => match ($level) {
                'intermediate' => ['ar' => 'متوسط', 'en' => 'Intermediate'],
                'advanced' => ['ar' => 'متقدم', 'en' => 'Advanced'],
                default => ['ar' => 'مبتدئ', 'en' => 'Beginner'],
            },
            'duration_hours' => $this->duration_hours,
            'sessions_hours' => $this->sessions_hours,
            'rating' => $this->rating !== null ? (float) $this->rating : null,
            'is_coming_soon' => $comingSoon,
            'cta' => $comingSoon
                ? [
                    'key' => 'waitlist',
                    'label' => [
                        'ar' => 'انضم لقائمة الانتظار',
                        'en' => 'Join the waitlist',
                    ],
                ]
                : [
                    'key' => 'details',
                    'label' => [
                        'ar' => 'تفاصيل الكورس',
                        'en' => 'Course details',
                    ],
                ],
        ];
    }
}

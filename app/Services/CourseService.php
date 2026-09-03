<?php

namespace App\Services;

use App\Models\Course;
use App\Support\MediaUrl;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Maps Course models to public API shapes (detail page + helpers).
 * Listing cards use IncubatorCourseCardResource; this service owns full detail.
 * All courses are offline-only in the API payload.
 */
class CourseService
{
    /**
     * Published course detail by slug or uuid (front may pass either).
     *
     * @return array<string, mixed>
     */
    public function detailBySlug(string $slugOrUuid): array
    {
        $course = Course::query()
            ->published()
            ->with(['courseCategory', 'trainer'])
            ->where(function ($query) use ($slugOrUuid) {
                $query->where('slug', $slugOrUuid)
                    ->orWhere('uuid', $slugOrUuid);
            })
            ->first();

        if (! $course) {
            throw (new ModelNotFoundException)->setModel(Course::class, [$slugOrUuid]);
        }

        return $this->mapDetail($course);
    }

    /**
     * Full course detail payload (objectives, modules, outcomes, trainer, …).
     * Cover image is intentionally omitted — detail visual is front-static; card image is listing-only.
     *
     * @return array<string, mixed>
     */
    public function mapDetail(Course $course): array
    {
        $level = (string) ($course->level ?? 'beginner');
        $modules = is_array($course->modules) ? array_values($course->modules) : [];

        return [
            'uuid' => $course->uuid,
            'slug' => $course->slug,
            'title' => $course->getTranslations('title'),
            'description' => $course->getTranslations('description'),
            'delivery' => [
                'mode' => 'offline',
                'label' => [
                    'ar' => 'حضوري (أوفلاين)',
                    'en' => 'In person (offline)',
                ],
            ],
            'location' => [
                'name' => $course->location,
                'details' => $course->location_details,
            ],
            'category' => $course->courseCategory ? [
                'uuid' => $course->courseCategory->uuid,
                'slug' => $course->courseCategory->slug,
                'name' => $course->courseCategory->getTranslations('name'),
            ] : null,
            'level' => [
                'key' => $level,
                'label' => match ($level) {
                    'intermediate' => ['ar' => 'متوسط', 'en' => 'Intermediate'],
                    'advanced' => ['ar' => 'متقدم', 'en' => 'Advanced'],
                    default => ['ar' => 'مبتدئ', 'en' => 'Beginner'],
                },
            ],
            'rating' => $course->rating !== null ? (float) $course->rating : null,
            'is_coming_soon' => (bool) $course->is_coming_soon,
            'card' => [
                'duration_hours' => $course->duration_hours,
                'sessions_hours' => $course->sessions_hours,
            ],
            'schedule' => [
                'starts_at' => optional($course->starts_at)?->toIso8601String(),
                'ends_at' => optional($course->ends_at)?->toIso8601String(),
                'registration_ends_at' => optional($course->registration_ends_at)?->toIso8601String(),
                'duration_weeks' => $course->duration_weeks,
                'duration_label' => $course->duration_weeks
                    ? [
                        'ar' => $course->duration_weeks.' أسابيع',
                        'en' => $course->duration_weeks.' weeks',
                    ]
                    : null,
                'modules_count' => count($modules),
                'max_seats' => $course->max_seats,
            ],
            'objectives' => $this->mapTitledItems($course->objectives),
            'modules' => collect($modules)->values()->map(fn (array $module, int $index) => [
                'title' => [
                    'ar' => (string) ($module['title_ar'] ?? ''),
                    'en' => (string) ($module['title_en'] ?? ''),
                ],
                'lessons' => collect(array_values($module['lessons'] ?? []))
                    ->filter(fn ($lesson) => is_array($lesson))
                    ->values()
                    ->map(fn (array $lesson) => [
                        'title' => [
                            'ar' => (string) ($lesson['title_ar'] ?? ''),
                            'en' => (string) ($lesson['title_en'] ?? ''),
                        ],
                        'duration' => filled($lesson['duration'] ?? null) ? (string) $lesson['duration'] : null,
                    ])
                    ->all(),
                'sort_order' => $index,
            ])->all(),
            'outcomes' => [
                'before' => $this->mapTextItems($course->outcomes_before),
                'after' => $this->mapTextItems($course->outcomes_after),
            ],
            'benefits' => $this->mapIconTextItems($course->benefits),
            'requirements' => $this->mapRequirements($course->requirements),
            'selection_steps' => $this->mapTitledItems($course->selection_steps),
            'trainer' => $this->mapTrainer($course),
            'cta' => $course->is_coming_soon
                ? [
                    'key' => 'waitlist',
                    'label' => [
                        'ar' => 'انضم لقائمة الانتظار',
                        'en' => 'Join the waitlist',
                    ],
                ]
                : [
                    'key' => 'enroll',
                    'label' => [
                        'ar' => 'اشترك الآن',
                        'en' => 'Enroll now',
                    ],
                ],
        ];
    }

    /**
     * @param  mixed  $items
     * @return list<array{icon_url: string|null, title: array{ar: string, en: string}, description: array{ar: string, en: string}, sort_order: int}>
     */
    protected function mapTitledItems(mixed $items): array
    {
        if (! is_array($items)) {
            return [];
        }

        return collect(array_values($items))
            ->filter(fn ($item) => is_array($item))
            ->values()
            ->map(fn (array $item, int $index) => [
                'icon_url' => MediaUrl::make($item['icon'] ?? null),
                'title' => [
                    'ar' => (string) ($item['title_ar'] ?? $item['ar'] ?? ''),
                    'en' => (string) ($item['title_en'] ?? $item['en'] ?? ''),
                ],
                'description' => [
                    'ar' => (string) ($item['desc_ar'] ?? $item['description_ar'] ?? ''),
                    'en' => (string) ($item['desc_en'] ?? $item['description_en'] ?? ''),
                ],
                'sort_order' => $index,
            ])
            ->all();
    }

    /**
     * @param  mixed  $items
     * @return list<array{icon_url: string|null, ar: string, en: string}>
     */
    protected function mapIconTextItems(mixed $items): array
    {
        if (! is_array($items)) {
            return [];
        }

        return collect(array_values($items))
            ->map(function ($item) {
                if (is_string($item)) {
                    return ['icon_url' => null, 'ar' => $item, 'en' => ''];
                }
                if (! is_array($item)) {
                    return null;
                }

                return [
                    'icon_url' => MediaUrl::make($item['icon'] ?? null),
                    'ar' => (string) ($item['ar'] ?? $item['text_ar'] ?? ''),
                    'en' => (string) ($item['en'] ?? $item['text_en'] ?? ''),
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @param  mixed  $items
     * @return list<array{ar: string, en: string}>
     */
    protected function mapTextItems(mixed $items): array
    {
        if (! is_array($items)) {
            return [];
        }

        return collect(array_values($items))
            ->map(function ($item) {
                if (is_string($item)) {
                    return ['ar' => $item, 'en' => ''];
                }
                if (! is_array($item)) {
                    return null;
                }

                return [
                    'ar' => (string) ($item['ar'] ?? $item['text_ar'] ?? ''),
                    'en' => (string) ($item['en'] ?? $item['text_en'] ?? ''),
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @param  mixed  $items
     * @return list<array{ar: string, en: string}>
     */
    protected function mapRequirements(mixed $items): array
    {
        return $this->mapTextItems($items);
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function mapTrainer(Course $course): ?array
    {
        $trainer = $course->trainer;
        if (! $trainer) {
            return null;
        }

        $socials = is_array($trainer->socials) ? array_values($trainer->socials) : [];

        return [
            'uuid' => $trainer->uuid,
            'name' => $trainer->name,
            'avatar_url' => $trainer->avatar_url,
            'title' => $trainer->getTranslations('title'),
            'experience' => $trainer->getTranslations('experience'),
            'bio' => $trainer->getTranslations('bio'),
            'phone' => $trainer->phone,
            'email' => $trainer->email,
            'socials' => collect($socials)
                ->filter(fn ($item) => is_array($item) && filled($item['url'] ?? null))
                ->values()
                ->map(fn (array $social) => [
                    'platform' => (string) ($social['platform'] ?? 'other'),
                    'url' => (string) $social['url'],
                ])
                ->all(),
        ];
    }
}

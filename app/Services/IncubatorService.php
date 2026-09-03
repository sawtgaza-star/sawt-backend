<?php

namespace App\Services;

use App\Models\Course;
use App\Models\CourseTrainer;
use App\Repositories\Contracts\SettingRepositoryInterface;
use App\Support\MediaUrl;
use Illuminate\Support\Carbon;

/**
 * Builds the public Incubator landing API payload from Setting keys (+ published courses / trainers).
 *
 * Sections: hero, stats, why, courses, sponsor, events, gallery, experts, faq, employers, join_cta, testimonials.
 * Navbar/footer live in LayoutService (GET /api/v1/layout/incubator).
 */
class IncubatorService
{
    public function __construct(
        protected SettingRepositoryInterface $settings,
        protected LayoutService $layout,
    ) {}

    /**
     * Full incubator landing page (no course detail; no chrome).
     *
     * @return array<string, mixed>
     */
    public function page(): array
    {
        return [
            'hero' => $this->hero(),
            'stats' => $this->stats(),
            'why' => $this->why(),
            'courses' => $this->coursesSection(),
            'sponsor' => $this->sponsor(),
            'events' => $this->events(),
            'gallery' => $this->gallery(),
            'experts' => $this->experts(),
            'faq' => $this->faq(),
            'employers' => $this->employers(),
            'join_cta' => $this->joinCta(),
            'testimonials' => $this->testimonials(),
        ];
    }

    /**
     * Incubator-site navbar + footer (separate from main platform layout).
     *
     * @return array{navbar: array<string, mixed>, footer: array<string, mixed>}
     */
    public function layout(): array
    {
        return [
            'navbar' => $this->layout->incubatorNavbar(),
            'footer' => $this->layout->incubatorFooter(),
        ];
    }

    /**
     * Hero: background + foreground images, overlay badges, title/CTA (i18n).
     *
     * @return array<string, mixed>
     */
    protected function hero(): array
    {
        return [
            'background_url' => MediaUrl::make($this->settings->get('incubator_hero_image')),
            'foreground_url' => MediaUrl::make($this->settings->get('incubator_hero_foreground')),
            // Alias: background (kept for older front clients)
            'image_url' => MediaUrl::make($this->settings->get('incubator_hero_image')),
            'badges' => [
                'top' => [
                    'value' => (string) ($this->settings->get('incubator_hero_badge_top_value', '1,247') ?? '1,247'),
                    'label' => $this->settings->i18n(
                        'incubator_hero_badge_top_label',
                        'هذا الشهر',
                        'This month'
                    ),
                ],
                'bottom' => [
                    'value' => (string) ($this->settings->get('incubator_hero_badge_bottom_value', '+340') ?? '+340'),
                    'label' => $this->settings->i18n(
                        'incubator_hero_badge_bottom_label',
                        'قصة وثقت',
                        'Stories documented'
                    ),
                ],
            ],
            'title' => $this->settings->i18n(
                'incubator_hero_title',
                'حوّل قصتك إلى محتوى يصنع أثرًا',
                'Turn your story into content that creates impact'
            ),
            'description' => $this->settings->i18n(
                'incubator_hero_desc',
                'انضم إلى بيئة تدريبية تجمع بين التعلم العملي، والإرشاد، والمشاريع الواقعية لتساعدك على صناعة محتوى يترك أثرًا.',
                'Join a training environment that combines hands-on learning, mentorship, and real projects to help you create impactful content.'
            ),
            'cta' => [
                'label' => $this->settings->i18n(
                    'incubator_hero_cta',
                    'ابدأ رحلتك التعليمية',
                    'Start your learning journey'
                ),
            ],
        ];
    }

    /**
     * Stats strip under the hero (JSON repeater from settings).
     *
     * @return list<array{key: string, value: string, label: array{ar: string, en: string}}>
     */
    protected function stats(): array
    {
        $items = $this->settings->get('incubator_stats', []);
        if (! is_array($items)) {
            $items = [];
        }

        return collect(array_values($items))
            ->filter(fn ($item) => is_array($item) && (filled($item['value'] ?? null) || filled($item['label_ar'] ?? null)))
            ->values()
            ->map(fn (array $item, int $index) => [
                'key' => (string) ($item['key'] ?? "stat_{$index}"),
                'value' => (string) ($item['value'] ?? ''),
                'label' => [
                    'ar' => (string) ($item['label_ar'] ?? ''),
                    'en' => (string) ($item['label_en'] ?? ''),
                ],
                'sort_order' => $index,
            ])
            ->all();
    }

    /**
     * «لماذا حاضنة صوت؟» — title/subtitle, one image, fixed feature items.
     *
     * @return array<string, mixed>
     */
    protected function why(): array
    {
        $items = $this->settings->get('incubator_why_items', []);
        if (! is_array($items)) {
            $items = [];
        }

        return [
            'title' => $this->settings->i18n('incubator_why_title', 'لماذا حاضنة صوت؟', 'Why Sawt Incubator?'),
            'subtitle' => $this->settings->i18n(
                'incubator_why_subtitle',
                'حاضنة صوت ليست مجرد منصة تدريبية، بل رحلة متكاملة تساعدك على تحويل أفكارك وقصصك إلى محتوى مؤثر.',
                'Sawt Incubator is not just a training platform — it is a complete journey to turn your ideas into impactful content.'
            ),
            'image_url' => MediaUrl::make($this->settings->get('incubator_why_image')),
            'items' => collect(array_values($items))->map(fn (array $item, int $index) => [
                'icon_url' => MediaUrl::make($item['icon'] ?? null),
                'title' => [
                    'ar' => (string) ($item['title_ar'] ?? ''),
                    'en' => (string) ($item['title_en'] ?? ''),
                ],
                'description' => [
                    'ar' => (string) ($item['desc_ar'] ?? ''),
                    'en' => (string) ($item['desc_en'] ?? ''),
                ],
                'sort_order' => $index,
            ])->all(),
        ];
    }

    /**
     * Featured courses chrome + published Course models (card resource applied in controller).
     *
     * @return array<string, mixed>
     */
    protected function coursesSection(): array
    {
        $limit = (int) ($this->settings->get('incubator_courses_limit', 6) ?: 6);
        $limit = max(1, min(24, $limit));

        $courses = Course::query()
            ->published()
            ->with(['courseCategory', 'trainer'])
            ->orderByDesc('id')
            ->limit($limit)
            ->get();

        return [
            'title' => $this->settings->i18n('incubator_courses_title', 'دوراتنا الأكثر شهرة', 'Our most popular courses'),
            'subtitle' => $this->settings->i18n(
                'incubator_courses_subtitle',
                'دورات تدريبية شاملة، تعتمد على التطبيق والتنفيذ العملي، نبدأ معك من الصفر حتى تصل إلى الاحتراف.',
                'Comprehensive courses built on practice — from zero to professional readiness.'
            ),
            'items' => $courses,
        ];
    }

    /**
     * Latest events / workshops («استكشف أحدث فعالياتنا»).
     * Category filter counts are derived from items; `all` is prepended.
     * Prefers `starts_at`; falls back to date + 12h AM/PM fields if needed.
     *
     * @return array<string, mixed>
     */
    protected function events(): array
    {
        $categories = $this->settings->get('incubator_events_categories', []);
        if (! is_array($categories)) {
            $categories = [];
        }

        $items = $this->settings->get('incubator_events_items', []);
        if (! is_array($items)) {
            $items = [];
        }

        $deliveryLabels = [
            'in_person' => ['ar' => 'وجاهي', 'en' => 'In person'],
            'online' => ['ar' => 'أونلاين', 'en' => 'Online'],
        ];
        $formatLabels = [
            'workshop' => ['ar' => 'ورشة عمل', 'en' => 'Workshop'],
            'seminar' => ['ar' => 'ندوة', 'en' => 'Seminar'],
        ];

        $mappedItems = collect(array_values($items))
            ->filter(fn ($item) => is_array($item) && filled($item['title_ar'] ?? null))
            ->values()
            ->map(function (array $item, int $index) use ($deliveryLabels, $formatLabels) {
                // Prefer composed 24h datetime; rebuild from AM/PM fields if missing.
                $startsAt = null;
                if (filled($item['starts_at'] ?? null)) {
                    try {
                        $startsAt = Carbon::parse($item['starts_at']);
                    } catch (\Throwable) {
                        $startsAt = null;
                    }
                }

                if (! $startsAt && filled($item['starts_date'] ?? null)) {
                    try {
                        $hour12 = max(1, min(12, (int) ($item['time_hour'] ?? 12)));
                        $minute = max(0, min(59, (int) ($item['time_minute'] ?? 0)));
                        $period = strtoupper((string) ($item['time_period'] ?? 'AM')) === 'PM' ? 'PM' : 'AM';
                        $hour24 = $hour12 % 12;
                        if ($period === 'PM') {
                            $hour24 += 12;
                        }
                        $startsAt = Carbon::parse(sprintf(
                            '%s %02d:%02d:00',
                            $item['starts_date'],
                            $hour24,
                            $minute
                        ));
                    } catch (\Throwable) {
                        $startsAt = null;
                    }
                }

                $delivery = (string) ($item['delivery'] ?? 'in_person');
                $format = (string) ($item['format'] ?? 'workshop');

                return [
                    'image_url' => MediaUrl::make($item['image'] ?? null),
                    'category_key' => (string) ($item['category_key'] ?? ''),
                    'title' => [
                        'ar' => (string) ($item['title_ar'] ?? ''),
                        'en' => (string) ($item['title_en'] ?? ''),
                    ],
                    'description' => [
                        'ar' => (string) ($item['desc_ar'] ?? ''),
                        'en' => (string) ($item['desc_en'] ?? ''),
                    ],
                    'starts_at' => $startsAt?->toIso8601String(),
                    'date_badge' => [
                        'day' => $startsAt?->format('j'),
                        'month' => [
                            'ar' => $startsAt?->locale('ar')->translatedFormat('F'),
                            'en' => $startsAt?->locale('en')->translatedFormat('F'),
                        ],
                    ],
                    'date_label' => [
                        'ar' => $startsAt?->locale('ar')->translatedFormat('l d/m/Y'),
                        'en' => $startsAt?->locale('en')->translatedFormat('l d/m/Y'),
                    ],
                    'time_label' => [
                        'ar' => $startsAt?->locale('ar')->translatedFormat('h:i A'),
                        'en' => $startsAt?->locale('en')->translatedFormat('h:i A'),
                    ],
                    'delivery' => [
                        'key' => $delivery,
                        'label' => $deliveryLabels[$delivery] ?? ['ar' => $delivery, 'en' => $delivery],
                    ],
                    'format' => [
                        'key' => $format,
                        'label' => $formatLabels[$format] ?? ['ar' => $format, 'en' => $format],
                    ],
                    'tags' => [
                        'ar' => trim(($deliveryLabels[$delivery]['ar'] ?? '').'، '.($formatLabels[$format]['ar'] ?? ''), '، '),
                        'en' => trim(($deliveryLabels[$delivery]['en'] ?? '').', '.($formatLabels[$format]['en'] ?? ''), ', '),
                    ],
                    'sort_order' => $index,
                ];
            });

        $countsByCategory = $mappedItems
            ->groupBy(fn (array $item) => $item['category_key'] ?: '_')
            ->map->count();

        $categoryFilters = collect(array_values($categories))
            ->filter(fn ($cat) => is_array($cat) && filled($cat['key'] ?? null))
            ->values()
            ->map(fn (array $cat, int $index) => [
                'key' => (string) $cat['key'],
                'label' => [
                    'ar' => (string) ($cat['label_ar'] ?? ''),
                    'en' => (string) ($cat['label_en'] ?? ''),
                ],
                'count' => (int) ($countsByCategory[(string) $cat['key']] ?? 0),
                'sort_order' => $index + 1,
            ])
            ->all();

        array_unshift($categoryFilters, [
            'key' => 'all',
            'label' => $this->settings->i18n('incubator_events_all_label', 'الكل', 'All'),
            'count' => $mappedItems->count(),
            'sort_order' => 0,
        ]);

        return [
            'title' => $this->settings->i18n(
                'incubator_events_title',
                'استكشف أحدث فعالياتنا',
                'Explore our latest events'
            ),
            'subtitle' => $this->settings->i18n(
                'incubator_events_subtitle',
                'أرقام حقيقية تعكس قوة مجتمعنا',
                'Real numbers that reflect the strength of our community'
            ),
            'categories' => $categoryFilters,
            'items' => $mappedItems->all(),
        ];
    }

    /**
     * Sponsor students («ساعد طلاب…»): packages, waiting list, impact stats — all settings JSON.
     *
     * @return array<string, mixed>
     */
    protected function sponsor(): array
    {
        $packages = $this->settings->get('incubator_sponsor_packages', []);
        if (! is_array($packages)) {
            $packages = [];
        }

        $students = $this->settings->get('incubator_sponsor_waiting_students', []);
        if (! is_array($students)) {
            $students = [];
        }

        $stats = $this->settings->get('incubator_sponsor_impact_stats', []);
        if (! is_array($stats)) {
            $stats = [];
        }

        return [
            'title' => $this->settings->i18n(
                'incubator_sponsor_title',
                'ساعد طلاب في الانضمام للحاضنة',
                'Help students join the incubator'
            ),
            'subtitle' => $this->settings->i18n(
                'incubator_sponsor_subtitle',
                'مبلغ بسيط يفتح باب المعرفة أمام شاب في غزة — تبرعك يصل مباشرة لتغطية تكاليف التدريب',
                'A small amount opens the door to knowledge for a young person in Gaza — your donation covers training costs directly.'
            ),
            'packages' => collect(array_values($packages))
                ->filter(fn ($item) => is_array($item) && filled($item['title_ar'] ?? null))
                ->values()
                ->map(fn (array $item, int $index) => [
                    'title' => [
                        'ar' => (string) ($item['title_ar'] ?? ''),
                        'en' => (string) ($item['title_en'] ?? ''),
                    ],
                    'description' => [
                        'ar' => (string) ($item['desc_ar'] ?? ''),
                        'en' => (string) ($item['desc_en'] ?? ''),
                    ],
                    'duration' => [
                        'ar' => (string) ($item['duration_ar'] ?? ''),
                        'en' => (string) ($item['duration_en'] ?? ''),
                    ],
                    'seats' => [
                        'ar' => (string) ($item['seats_ar'] ?? ''),
                        'en' => (string) ($item['seats_en'] ?? ''),
                    ],
                    'price' => (string) ($item['price'] ?? ''),
                    'currency' => (string) ($item['currency'] ?? '$'),
                    'cta' => [
                        'label' => [
                            'ar' => (string) ($item['cta_ar'] ?? ''),
                            'en' => (string) ($item['cta_en'] ?? ''),
                        ],
                    ],
                    'sort_order' => $index,
                ])
                ->all(),
            'waiting' => [
                'title' => $this->settings->i18n(
                    'incubator_sponsor_waiting_title',
                    'طلاب ينتظرون داعماً',
                    'Students waiting for a sponsor'
                ),
                'more_label' => $this->settings->i18n(
                    'incubator_sponsor_waiting_more',
                    '+28 طالباً آخرين',
                    '+28 more students'
                ),
                'students' => collect(array_values($students))
                    ->filter(fn ($item) => is_array($item) && filled($item['name'] ?? null))
                    ->values()
                    ->map(fn (array $item, int $index) => [
                        'name' => (string) ($item['name'] ?? ''),
                        'meta' => [
                            'ar' => (string) ($item['meta_ar'] ?? ''),
                            'en' => (string) ($item['meta_en'] ?? ''),
                        ],
                        'avatar_url' => MediaUrl::make($item['avatar'] ?? null),
                        'sort_order' => $index,
                    ])
                    ->all(),
            ],
            'impact' => [
                'title' => $this->settings->i18n(
                    'incubator_sponsor_impact_title',
                    'أثر البرنامج',
                    'Program impact'
                ),
                'stats' => collect(array_values($stats))
                    ->filter(fn ($item) => is_array($item) && (filled($item['value'] ?? null) || filled($item['label_ar'] ?? null)))
                    ->values()
                    ->map(fn (array $item, int $index) => [
                        'value' => (string) ($item['value'] ?? ''),
                        'label' => [
                            'ar' => (string) ($item['label_ar'] ?? ''),
                            'en' => (string) ($item['label_en'] ?? ''),
                        ],
                        'sort_order' => $index,
                    ])
                    ->all(),
            ],
        ];
    }

    /**
     * Photo/video album («الحاضنة بيتك الثاني ، البوم الحاضنة»).
     * Admin only uploads media + captions; type/slot are derived (video if video_url set; slot by order).
     *
     * @return array<string, mixed>
     */
    protected function gallery(): array
    {
        $items = $this->settings->get('incubator_gallery_items', []);
        if (! is_array($items)) {
            $items = [];
        }

        // Fixed grid positions matching the live incubator album layout (by repeater order).
        $slots = ['left_top', 'left_bottom', 'center_top', 'center_bottom', 'right_tall'];

        return [
            'title' => $this->settings->i18n(
                'incubator_gallery_title',
                'الحاضنة بيتك الثاني ، البوم الحاضنة',
                'The incubator is your second home — album'
            ),
            'subtitle' => $this->settings->i18n(
                'incubator_gallery_subtitle',
                'مبلغ بسيط يفتح باب المعرفة أمام شاب في غزة — تبرعك يصل مباشرة لتغطية تكاليف التدريب',
                'A small amount opens the door to knowledge for a young person in Gaza — your donation covers training costs directly.'
            ),
            'items' => collect(array_values($items))
                ->filter(fn ($item) => is_array($item) && (filled($item['image'] ?? null) || filled($item['video_url'] ?? null) || filled($item['caption_ar'] ?? null)))
                ->values()
                ->map(function (array $item, int $index) use ($slots) {
                    $videoUrl = filled($item['video_url'] ?? null) ? (string) $item['video_url'] : null;

                    return [
                        'slot' => $slots[$index] ?? "item_{$index}",
                        'type' => $videoUrl ? 'video' : 'image',
                        'image_url' => MediaUrl::make($item['image'] ?? null),
                        'video_url' => $videoUrl,
                        'caption' => [
                            'ar' => (string) ($item['caption_ar'] ?? ''),
                            'en' => (string) ($item['caption_en'] ?? ''),
                        ],
                        'subtitle' => [
                            'ar' => (string) ($item['subtitle_ar'] ?? ''),
                            'en' => (string) ($item['subtitle_en'] ?? ''),
                        ],
                        'sort_order' => $index,
                    ];
                })
                ->all(),
        ];
    }

    /**
     * Experts team («فريق خبراء متخصص») — chrome from settings, cards from active CourseTrainer rows.
     *
     * @return array<string, mixed>
     */
    protected function experts(): array
    {
        $limit = (int) ($this->settings->get('incubator_experts_limit', 8) ?: 8);
        $limit = max(1, min(24, $limit));

        $trainers = CourseTrainer::query()
            ->active()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->limit($limit)
            ->get();

        return [
            'title' => $this->settings->i18n(
                'incubator_experts_title',
                'فريق خبراء متخصص',
                'Specialized expert team'
            ),
            'subtitle' => $this->settings->i18n(
                'incubator_experts_subtitle',
                'أرقام حقيقية تعكس قوة مجتمعنا',
                'Real numbers that reflect the strength of our community'
            ),
            'items' => $trainers->values()->map(function (CourseTrainer $trainer, int $index) {
                $socials = is_array($trainer->socials) ? array_values($trainer->socials) : [];
                $firstUrl = null;
                foreach ($socials as $social) {
                    if (is_array($social) && filled($social['url'] ?? null)) {
                        $firstUrl = (string) $social['url'];
                        break;
                    }
                }

                return [
                    "id" => $trainer->id,
                    'uuid' => $trainer->uuid,
                    'name' => $trainer->getTranslations('name'),
                    'title' => $trainer->getTranslations('title'),
                    'experience' => $trainer->getTranslations('experience'),
                    'bio' => $trainer->getTranslations('bio'),
                    'avatar_url' => $trainer->avatar_url,
                    'link_url' => $firstUrl,
                    'socials' => collect($socials)
                        ->filter(fn ($s) => is_array($s) && filled($s['url'] ?? null))
                        ->values()
                        ->map(fn (array $s) => [
                            'platform' => (string) ($s['platform'] ?? ''),
                            'url' => (string) $s['url'],
                        ])
                        ->all(),
                    'sort_order' => $index,
                ];
            })->all(),
        ];
    }

    /**
     * FAQ accordion («الأسئلة التي تدور ببالك؟») + side image + optional “more questions” blurb.
     *
     * @return array<string, mixed>
     */
    protected function faq(): array
    {
        $items = $this->settings->get('incubator_faq_items', []);
        if (! is_array($items)) {
            $items = [];
        }

        return [
            'title' => $this->settings->i18n(
                'incubator_faq_title',
                'الأسئلة التي تدور ببالك؟',
                'Questions on your mind?'
            ),
            'subtitle' => $this->settings->i18n(
                'incubator_faq_subtitle',
                'أرقام حقيقية تعكس قوة مجتمعنا',
                'Real numbers that reflect the strength of our community'
            ),
            'image_url' => MediaUrl::make($this->settings->get('incubator_faq_image')),
            'items' => collect(array_values($items))
                ->filter(fn ($item) => is_array($item) && filled($item['question_ar'] ?? null))
                ->values()
                ->map(fn (array $item, int $index) => [
                    'question' => [
                        'ar' => (string) ($item['question_ar'] ?? ''),
                        'en' => (string) ($item['question_en'] ?? ''),
                    ],
                    'answer' => [
                        'ar' => (string) ($item['answer_ar'] ?? ''),
                        'en' => (string) ($item['answer_en'] ?? ''),
                    ],
                    'sort_order' => $index,
                ])
                ->all(),
            'more' => [
                'title' => $this->settings->i18n(
                    'incubator_faq_more_title',
                    'لديك سؤال آخر؟',
                    'Have another question?'
                ),
                'description' => $this->settings->i18n(
                    'incubator_faq_more_desc',
                    'فريقنا جاهز للإجابة — سنردّ عليك خلال ساعات',
                    'Our team is ready to help — we usually reply within hours'
                ),
            ],
        ];
    }

    /**
     * Employers / trusted orgs logos («يعمل خريجونا لدى جهات موثوقة»).
     *
     * @return array<string, mixed>
     */
    protected function employers(): array
    {
        $logos = $this->settings->get('incubator_employers_logos', []);
        if (! is_array($logos)) {
            $logos = [];
        }

        return [
            'title' => $this->settings->i18n(
                'incubator_employers_title',
                'يعمل خريجونا لدى جهات موثوقة',
                'Our graduates work at trusted organizations'
            ),
            'subtitle' => $this->settings->i18n(
                'incubator_employers_subtitle',
                'نفخر بتميز خريجينا وحصولهم على وظائف مرموقة في جهات عالمية',
                'We are proud of our graduates and the prestigious roles they hold worldwide'
            ),
            'items' => collect(array_values($logos))
                ->filter(fn ($item) => is_array($item) && (filled($item['logo'] ?? null) || filled($item['name'] ?? null)))
                ->values()
                ->map(fn (array $item, int $index) => [
                    'name' => filled($item['name'] ?? null) ? (string) $item['name'] : null,
                    'logo_url' => MediaUrl::make($item['logo'] ?? null),
                    'url' => filled($item['url'] ?? null) ? (string) $item['url'] : null,
                    'sort_order' => $index,
                ])
                ->all(),
        ];
    }

    /**
     * Bottom join banner (image + title/description/button labels).
     *
     * @return array<string, mixed>
     */
    protected function joinCta(): array
    {
        return [
            'image_url' => MediaUrl::make($this->settings->get('incubator_join_cta_bg')),
            'title' => $this->settings->i18n(
                'incubator_join_cta_title',
                'ابدأ رحلتك مع حاضنة صوت',
                'Start your journey with Sawt Incubator'
            ),
            'description' => $this->settings->i18n(
                'incubator_join_cta_desc',
                'حوّل فكرتك إلى محتوى مؤثر، وطوّر مهاراتك من خلال التدريب العملي والإرشاد المتخصص.',
                'Turn your idea into impactful content and grow through hands-on training and specialized mentorship.'
            ),
            'button' => [
                'label' => $this->settings->i18n('incubator_join_cta_button', 'انضم إلى الحاضنة', 'Join the incubator'),
            ],
        ];
    }

    /**
     * Graduate testimonials carousel («شهادات وتجارب خريجينا») — last landing section.
     *
     * @return array<string, mixed>
     */
    protected function testimonials(): array
    {
        $items = $this->settings->get('incubator_testimonials_items', []);
        if (! is_array($items)) {
            $items = [];
        }

        return [
            'title' => $this->settings->i18n(
                'incubator_testimonials_title',
                'شهادات وتجارب خريجينا',
                'Graduate testimonials'
            ),
            'subtitle' => $this->settings->i18n(
                'incubator_testimonials_subtitle',
                'اكتشف كيف غيّرت حاضنة صوت حياة المئات من الطلاب الذين بدأوا رحلتهم من الصفر وأصبحوا اليوم محترفين مطلوبين في سوق العمل.',
                'See how Sawt Incubator changed the lives of hundreds of students who started from zero and are now in demand.'
            ),
            // Button labels are fixed (not editable in admin)
            'view_all' => [
                'label' => [
                    'ar' => 'عرض الكل',
                    'en' => 'View all',
                ],
            ],
            'read_more' => [
                'label' => [
                    'ar' => 'اقرأ المزيد',
                    'en' => 'Read more',
                ],
            ],
            'items' => collect(array_values($items))
                ->filter(fn ($item) => is_array($item) && filled($item['name'] ?? null) && filled($item['quote_ar'] ?? null))
                ->values()
                ->map(function (array $item, int $index) {
                    $rating = (int) ($item['rating'] ?? 5);
                    $rating = max(1, min(5, $rating));

                    return [
                        'name' => (string) ($item['name'] ?? ''),
                        'role' => [
                            'ar' => (string) ($item['role_ar'] ?? ''),
                            'en' => (string) ($item['role_en'] ?? ''),
                        ],
                        'quote' => [
                            'ar' => (string) ($item['quote_ar'] ?? ''),
                            'en' => (string) ($item['quote_en'] ?? ''),
                        ],
                        'avatar_url' => MediaUrl::make($item['avatar'] ?? null),
                        'rating' => $rating,
                        'sort_order' => $index,
                    ];
                })
                ->all(),
        ];
    }
}

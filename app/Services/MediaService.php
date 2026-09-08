<?php

namespace App\Services;

use App\Models\MediaServiceItem;
use App\Models\MediaWork;
use App\Repositories\Contracts\SettingRepositoryInterface;
use App\Support\MediaUrl;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Builds public Sawt Media landing + contact + service/work detail APIs.
 * Chrome/copy from settings (group: media); services/works from DB tables.
 *
 * Chrome (navbar/footer): LayoutService → GET /api/v1/layout/media*
 */
class MediaService
{
    public function __construct(
        protected SettingRepositoryInterface $settings,
    ) {}

    /**
     * Read a Spatie JSON translation as {ar, en} for API payloads.
     *
     * @return array{ar: string, en: string}
     */
    protected function t(MediaServiceItem|MediaWork $model, string $field): array
    {
        return [
            'ar' => (string) $model->getTranslation($field, 'ar', false),
            'en' => (string) $model->getTranslation($field, 'en', false),
        ];
    }

    /**
     * Full media landing page (no chrome).
     *
     * @return array<string, mixed>
     */
    public function page(): array
    {
        return [
            'hero' => $this->hero(),
            'about' => $this->about(),
            'stats' => $this->stats(),
            'services' => $this->services(),
            'why' => $this->why(),
            'methodology' => $this->methodology(),
            'works' => $this->works(),
            'audiences' => $this->audiences(),
            'partners' => $this->partners(),
            'consultation' => $this->consultation(),
            'packages' => $this->packages(),
            'testimonials' => $this->testimonials(),
            'faq' => $this->faq(),
        ];
    }

    /**
     * Hero collage images (free list) + phrases ticker (شريط «عبارة» تحت الهيرو).
     *
     * @return array<string, mixed>
     */
    protected function hero(): array
    {
        // Phrases from إعدادات ميديا → عبارات (media_hero_rotating) — text strip only
        $phrases = $this->settings->get('media_hero_rotating', []);
        if (! is_array($phrases)) {
            $phrases = [];
        }

        // Free-form hero collage — admin adds any number of images
        $images = $this->settings->get('media_hero_images', []);
        if (! is_array($images)) {
            $images = [];
        }

        return [
            'eyebrow' => $this->settings->i18n('media_hero_eyebrow', 'صوت ميديا تقدم', 'Sawt Media presents'),
            'images' => collect(array_values($images))
                ->filter(fn ($item) => is_array($item) && filled($item['image'] ?? null))
                ->values()
                ->map(fn (array $item, int $i) => [
                    'url' => MediaUrl::make($item['image'] ?? null),
                    'sort_order' => $i,
                ])
                ->all(),
            'phrases' => collect(array_values($phrases))
                ->filter(fn ($item) => is_array($item) && filled($item['label_ar'] ?? null))
                ->values()
                ->map(fn (array $item, int $i) => [
                    'label' => [
                        'ar' => (string) ($item['label_ar'] ?? ''),
                        'en' => (string) ($item['label_en'] ?? ''),
                    ],
                    'sort_order' => $i,
                ])
                ->all(),
            'description' => $this->settings->i18n(
                'media_hero_desc',
                'نحوّل أفكارك إلى تجارب إعلامية مؤثرة. من الاستراتيجية إلى الإنتاج والنشر — كل شيء في مكان واحد.',
                'We turn your ideas into impactful media experiences — from strategy to production and publishing, all in one place.'
            ),
            'cta' => [
                'primary' => [
                    'key' => 'start_project',
                    'path' => '/media/contact',
                    'label' => $this->settings->i18n('media_hero_cta_primary', 'ابدأ مشروعك', 'Start your project'),
                ],
                'secondary' => [
                    'key' => 'services',
                    'path' => '/media#services',
                    'label' => $this->settings->i18n('media_hero_cta_secondary', 'تعرف على خدماتنا', 'Explore our services'),
                ],
            ],
            'badge' => [
                'value' => (string) ($this->settings->get('media_hero_badge_value', '98%') ?? '98%'),
                'label' => $this->settings->i18n('media_hero_badge_label', 'رضا العملاء', 'Client satisfaction'),
            ],
        ];
    }

    /**
     * About section with a fixed 2×2 image collage (four uploads).
     *
     * @return array<string, mixed>
     */
    protected function about(): array
    {
        // Collage slots: 1 top-start (يمين), 2 top-end, 3 bottom-start, 4 bottom-end
        $imageSlots = [
            ['key' => 'top_start', 'setting' => 'media_about_image_1'],
            ['key' => 'top_end', 'setting' => 'media_about_image_2'],
            ['key' => 'bottom_start', 'setting' => 'media_about_image_3'],
            ['key' => 'bottom_end', 'setting' => 'media_about_image_4'],
        ];

        return [
            'eyebrow' => $this->settings->i18n('media_about_eyebrow', 'من نحن', 'About us'),
            'title' => $this->settings->i18n('media_about_title', 'شريكك الإعلامي المتكامل', 'Your complete media partner'),
            'body' => $this->settings->i18n(
                'media_about_body',
                'صوت ميديا وكالة إعلامية إبداعية متكاملة، تقدم حلولاً إعلامية شاملة من الاستراتيجية إلى الإنتاج والنشر.',
                'Sawt Media is a full creative agency offering end-to-end media solutions.'
            ),
            'vision' => [
                'title' => $this->settings->i18n('media_about_vision_title', 'رؤيتنا', 'Our vision'),
                'text' => $this->settings->i18n('media_about_vision', 'أن تصبح منصة التقنية الأولى لإدارة معارض الكتب في العالم العربي.', 'To become the leading tech platform for managing book fairs in the Arab world.'),
            ],
            'mission' => [
                'title' => $this->settings->i18n('media_about_mission_title', 'رسالتنا', 'Our mission'),
                'text' => $this->settings->i18n('media_about_mission', 'تمكين منظمي معارض الكتب من إدارة فعالياتهم بكفاءة أعلى وتجربة أكثر.', 'Empower book-fair organizers to run events with higher efficiency.'),
            ],
            'images' => collect($imageSlots)
                ->map(fn (array $slot, int $i) => [
                    'key' => $slot['key'],
                    'url' => MediaUrl::make($this->settings->get($slot['setting'])),
                    'sort_order' => $i,
                ])
                ->all(),
            'badge' => [
                'value' => (string) ($this->settings->get('media_about_badge_value', '98%') ?? '98%'),
                'label' => $this->settings->i18n('media_about_badge_label', 'رضا العملاء', 'Client satisfaction'),
            ],
        ];
    }

    /** @return array<string, mixed> */
    protected function stats(): array
    {
        $items = $this->settings->get('media_stats', []);
        if (! is_array($items)) {
            $items = [];
        }

        return [
            'eyebrow' => $this->settings->i18n('media_stats_eyebrow', 'صوت ميديا في ارقام', 'Sawt Media in numbers'),
            'title' => $this->settings->i18n('media_stats_title', 'أرقام نفخر بها', 'Numbers we are proud of'),
            'subtitle' => $this->settings->i18n('media_stats_subtitle', 'أرقام تعكس ثقة عملائنا وجودة عملنا', 'Numbers that reflect our clients’ trust and quality'),
            'items' => collect(array_values($items))
                ->filter(fn ($item) => is_array($item) && filled($item['value'] ?? null))
                ->values()
                ->map(fn (array $item, int $i) => [
                    'value' => (string) ($item['value'] ?? ''),
                    'label' => [
                        'ar' => (string) ($item['label_ar'] ?? ''),
                        'en' => (string) ($item['label_en'] ?? ''),
                    ],
                    'sort_order' => $i,
                ])
                ->all(),
        ];
    }

    /** Landing services section — chrome from settings, cards from media_services table. */
    protected function services(): array
    {
        return $this->servicesIndex();
    }

    /**
     * Public services list — GET /api/v1/pages/media/services
     * (same payload as landing `services` section: chrome + active cards).
     *
     * @return array<string, mixed>
     */
    public function servicesIndex(): array
    {
        return [
            'eyebrow' => $this->settings->i18n('media_services_eyebrow', 'خدماتنا', 'Our services'),
            'title' => $this->settings->i18n('media_services_title', 'حلول إعلامية متكاملة', 'Complete media solutions'),
            'subtitle' => $this->settings->i18n('media_services_subtitle', 'اكتشف خدماتنا خطوة بخطوة — اسحب للأسفل', 'Explore our services step by step'),
            'cta' => [
                'label' => $this->settings->i18n('media_services_cta', 'استكشف المزيد', 'Explore more'),
            ],
            'items' => MediaServiceItem::query()
                ->active()
                ->get()
                ->values()
                ->map(fn (MediaServiceItem $item, int $i) => $this->mapServiceCard($item, $i))
                ->all(),
        ];
    }

    /**
     * Lean services dropdown — GET /api/v1/pages/media/services/options
     * Only id, uuid, slug, name (no chrome / images / descriptions).
     *
     * @return list<array{id: int, uuid: string, slug: string, name: array{ar: string, en: string}}>
     */
    public function servicesOptions(): array
    {
        return MediaServiceItem::query()
            ->active()
            ->get()
            ->values()
            ->map(fn (MediaServiceItem $item) => [
                'id' => $item->id,
                'uuid' => $item->uuid,
                'slug' => $item->slug,
                'name' => $this->t($item, 'title'),
            ])
            ->all();
    }

    /**
     * Single service detail — {slugOrUuid} accepts slug or uuid (active only).
     *
     * @return array<string, mixed>|null  null when not found / inactive
     */
    public function serviceBySlug(string $slugOrUuid): ?array
    {
        $key = trim(strtolower($slugOrUuid));
        if ($key === '') {
            return null;
        }

        // Same pattern as courses: /media/services/{slug|uuid}
        $found = MediaServiceItem::query()
            ->active()
            ->where(function ($query) use ($key) {
                $query->where('slug', $key)->orWhere('uuid', $key);
            })
            ->first();

        if (! $found) {
            return null;
        }

        // نماذج من أعمالنا — linked MediaWork rows (not settings JSON)
        $sampleWorks = MediaWork::query()
            ->active()
            ->where('media_service_id', $found->id)
            ->with('service')
            ->get();

        return [
            'uuid' => $found->uuid,
            'slug' => $found->slug,
            'path' => '/media/services/'.$found->slug,
            'hero' => [
                // Shared banner (image + title + breadcrumb) — edited in إعدادات ميديا → تفاصيل الخدمة
                'image_url' => MediaUrl::make($this->settings->get('media_service_detail_hero_image')),
                'title' => $this->settings->i18n(
                    'media_service_detail_hero',
                    'حلول رقمية تلتقي فيها الفكرة والتجربة والأثر.',
                    'Digital solutions where idea, experience, and impact meet.'
                ),
                'breadcrumb' => [
                    'home' => [
                        'key' => 'media',
                        'path' => '/media',
                        'label' => $this->settings->i18n(
                            'media_service_detail_breadcrumb_home',
                            'الرئيسية',
                            'Home'
                        ),
                    ],
                    'services' => [
                        'key' => 'services',
                        'path' => '/media#services',
                        'label' => $this->settings->i18n(
                            'media_service_detail_breadcrumb_services',
                            'خدماتنا',
                            'Our services'
                        ),
                    ],
                    // Last crumb = this service’s title (from media_services row)
                    'current' => $this->t($found, 'title'),
                ],
            ],
            'service' => [
                'number' => (string) ($found->number ?: sprintf('%02d', $found->sort_order ?: 1)),
                'title' => $this->t($found, 'title'),
                'tagline' => $this->t($found, 'tagline'),
                'image_url' => MediaUrl::make($found->image),
                // Detail-page carousel (horizontal gallery under the title)
                'gallery' => collect(is_array($found->gallery) ? array_values($found->gallery) : [])
                    ->map(fn ($path) => MediaUrl::make(is_string($path) ? $path : null))
                    ->filter()
                    ->values()
                    ->map(fn (string $url, int $i) => [
                        'url' => $url,
                        'sort_order' => $i,
                    ])
                    ->all(),
            ],
            'includes' => [
                'title' => $this->settings->i18n(
                    'media_service_detail_includes_title',
                    'ماذا تشمل الخدمة',
                    'What’s included'
                ),
                'body' => $this->t($found, 'description'),
                'items' => [
                    'ar' => $this->lines($this->t($found, 'includes')['ar']),
                    'en' => $this->lines($this->t($found, 'includes')['en']),
                ],
            ],
            'works' => [
                'title' => $this->settings->i18n(
                    'media_service_detail_works_title',
                    'نماذج من أعمالنا',
                    'Sample work'
                ),
                'more' => [
                    'key' => 'works',
                    'path' => '/media#works',
                    'label' => $this->settings->i18n(
                        'media_service_detail_works_more',
                        'عرض المزيد',
                        'View more'
                    ),
                ],
                'items' => $sampleWorks
                    ->values()
                    ->map(fn (MediaWork $work, int $i) => $this->mapWorkCard($work, $i))
                    ->all(),
            ],
            'cta' => $this->sharedServiceCta(),
        ];
    }

    /**
     * Map a DB service row to a landing card payload.
     *
     * @return array<string, mixed>
     */
    protected function mapServiceCard(MediaServiceItem $item, int $index): array
    {
        // tags is Spatie JSON string per locale; expose as string[] per locale
        $tagsPair = $this->t($item, 'tags');

        return [
            'uuid' => $item->uuid,
            'slug' => $item->slug,
            'path' => '/media/services/'.$item->slug,
            'number' => (string) ($item->number ?: sprintf('%02d', $index + 1)),
            'title' => $this->t($item, 'title'),
            'tagline' => $this->t($item, 'tagline'),
            'description' => $this->t($item, 'description'),
            'tags' => [
                'ar' => $this->tagList($tagsPair['ar']),
                'en' => $this->tagList($tagsPair['en']),
            ],
            'image_url' => MediaUrl::make($item->image),
            'sort_order' => $index,
        ];
    }

    /**
     * Split a comma-separated tags string into a clean list.
     *
     * @return list<string>
     */
    protected function tagList(string $csv): array
    {
        return collect(explode(',', $csv))
            ->map(fn ($t) => trim($t))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Lean work card (landing + archive + service «نماذج»).
     * Includes service + tag.value so /media/works can filter client-side.
     *
     * @return array<string, mixed>
     */
    protected function mapWorkCard(MediaWork $work, int $index): array
    {
        $tag = $this->t($work, 'tag');
        $service = $work->relationLoaded('service') ? $work->service : $work->service()->first();

        return [
            'uuid' => $work->uuid,
            'slug' => $work->slug,
            'path' => '/media/works/'.$work->slug,
            'category' => $this->t($work, 'category'),
            'date' => $this->workDateLabels($work),
            'title' => $this->t($work, 'title'),
            'description' => $this->t($work, 'summary'),
            'image_url' => MediaUrl::make($work->cover_image),
            // Filter keys for archive page (match filters.services / filters.tags)
            'service' => $service ? [
                'uuid' => $service->uuid,
                'slug' => $service->slug,
                'value' => $service->slug,
                'label' => $this->t($service, 'title'),
            ] : null,
            'tag' => [
                'value' => $this->tagFilterValue($tag['en'] ?: $tag['ar']),
                'label' => $tag,
            ],
            'sort_order' => $index,
        ];
    }

    /**
     * Shared bottom CTA used on service + work detail pages.
     *
     * @return array<string, mixed>
     */
    protected function sharedServiceCta(): array
    {
        return [
            'image_url' => MediaUrl::make($this->settings->get('media_service_detail_cta_image')),
            'title' => $this->settings->i18n(
                'media_service_detail_cta_title',
                'فريق صوت ميديا يدعم نموك',
                'Sawt Media’s team supports your growth'
            ),
            'body' => $this->settings->i18n(
                'media_service_detail_cta_body',
                'نساعد الشركات على تنفيذ مشاريعها بسرعة واحترافية من خلال فريق متخصص يعمل كامتداد لفريقك باستخدام أحدث أدوات التصميم وتقنيات الذكاء الاصطناعي.',
                'We help companies ship projects fast and professionally — a specialized team that extends yours with modern design tools and AI.'
            ),
            'button' => [
                'key' => 'start_project',
                'path' => '/media/contact',
                'label' => $this->settings->i18n(
                    'media_service_detail_cta_label',
                    'احجز استشارة',
                    'Book a consultation'
                ),
            ],
        ];
    }

    /**
     * @return list<array{value: string, label: array{ar: string, en: string}, sort_order: int}>
     */
    protected function mapStatItems(mixed $items): array
    {
        if (! is_array($items)) {
            return [];
        }

        return collect(array_values($items))
            ->filter(fn ($item) => is_array($item) && filled($item['value'] ?? null))
            ->values()
            ->map(fn (array $item, int $i) => [
                'value' => (string) ($item['value'] ?? ''),
                'label' => [
                    'ar' => (string) ($item['label_ar'] ?? ''),
                    'en' => (string) ($item['label_en'] ?? ''),
                ],
                'sort_order' => $i,
            ])
            ->all();
    }

    /**
     * Works archive page — GET /api/v1/pages/media/works
     * Matches /media/works: hero + filter options (service = القسم, tag = التخصص).
     * Frontend applies filtering client-side; we only expose the filter lists + items.
     *
     * @return array<string, mixed>
     */
    public function worksIndex(): array
    {
        $works = MediaWork::query()
            ->active()
            ->with('service')
            ->get();

        return [
            'path' => '/media/works',
            'hero' => [
                // Same headline as live /media/works and service/work detail heroes
                'title' => $this->settings->i18n(
                    'media_service_detail_hero',
                    'حلول رقمية تلتقي فيها الفكرة والتجربة والأثر.',
                    'Digital solutions where idea, experience, and impact meet.'
                ),
                'breadcrumb' => [
                    'home' => [
                        'key' => 'home',
                        'path' => '/media',
                        'label' => ['ar' => 'الرئيسية', 'en' => 'Home'],
                    ],
                    'current' => $this->settings->i18n('media_works_eyebrow', 'أعمالنا', 'Our work'),
                ],
            ],
            // Filter chrome only — FE filters `items` by service.slug / tag.value
            'filters' => [
                'services' => [
                    'key' => 'service',
                    'label' => ['ar' => 'القسم', 'en' => 'Section'],
                    'placeholder' => ['ar' => '—', 'en' => '—'],
                    'options' => $this->worksServiceFilterOptions(),
                ],
                'tags' => [
                    'key' => 'tag',
                    'label' => ['ar' => 'التخصص', 'en' => 'Specialty'],
                    'placeholder' => ['ar' => '—', 'en' => '—'],
                    'options' => $this->worksTagFilterOptions($works),
                ],
            ],
            'items' => $works
                ->values()
                ->map(fn (MediaWork $work, int $i) => $this->mapWorkCard($work, $i))
                ->all(),
        ];
    }

    /**
     * «القسم» options = active media services (slug is the filter value).
     *
     * @return list<array<string, mixed>>
     */
    protected function worksServiceFilterOptions(): array
    {
        return MediaServiceItem::query()
            ->active()
            ->get()
            ->values()
            ->map(fn (MediaServiceItem $service) => [
                'uuid' => $service->uuid,
                'slug' => $service->slug,
                'value' => $service->slug,
                'label' => $this->t($service, 'title'),
            ])
            ->all();
    }

    /**
     * «التخصص» options = unique tags from active works (stable slug value for FE).
     *
     * @param  Collection<int, MediaWork>  $works
     * @return list<array<string, mixed>>
     */
    protected function worksTagFilterOptions(Collection $works): array
    {
        return $works
            ->map(function (MediaWork $work) {
                $label = $this->t($work, 'tag');
                $value = $this->tagFilterValue($label['en'] ?: $label['ar']);
                if ($value === '') {
                    return null;
                }

                return [
                    'value' => $value,
                    'label' => $label,
                ];
            })
            ->filter()
            ->unique('value')
            ->values()
            ->all();
    }

    /**
     * Stable slug used as tag filter value (match items[].tag.value on the FE).
     */
    protected function tagFilterValue(string $text): string
    {
        $slug = Str::slug(trim($text));

        // Arabic-only labels may slug to empty — fall back to a hash-free compact key
        if ($slug === '' && $text !== '') {
            return 't-'.substr(md5($text), 0, 10);
        }

        return $slug;
    }

    /**
     * Display date as month/year labels for the front (datepicker stores work_date).
     * Falls back to legacy JSON `date` when work_date is empty.
     *
     * @return array{ar: string, en: string}
     */
    protected function workDateLabels(MediaWork $work): array
    {
        if ($work->work_date) {
            return [
                'ar' => $work->work_date->copy()->locale('ar')->translatedFormat('F Y'),
                'en' => $work->work_date->copy()->locale('en')->translatedFormat('F Y'),
            ];
        }

        return $this->t($work, 'date');
    }

    /**
     * Work detail — /media/works/{slug|uuid} (e.g. film).
     *
     * @return array<string, mixed>|null
     */
    public function workBySlug(string $slugOrUuid): ?array
    {
        $key = trim(strtolower($slugOrUuid));
        if ($key === '') {
            return null;
        }

        $work = MediaWork::query()
            ->active()
            ->with('service')
            ->where(function ($query) use ($key) {
                $query->where('slug', $key)->orWhere('uuid', $key);
            })
            ->first();

        if (! $work) {
            return null;
        }

        $service = $work->service;

        return [
            'uuid' => $work->uuid,
            'slug' => $work->slug,
            'path' => '/media/works/'.$work->slug,
            'hero' => [
                'image_url' => MediaUrl::make($this->settings->get('media_service_detail_hero_image')),
                'title' => $this->settings->i18n(
                    'media_service_detail_hero',
                    'حلول رقمية تلتقي فيها الفكرة والتجربة والأثر.',
                    'Digital solutions where idea, experience, and impact meet.'
                ),
                'breadcrumb' => [
                    'works' => [
                        'key' => 'works',
                        'path' => '/media#works',
                        'label' => $this->settings->i18n('media_works_eyebrow', 'أعمالنا', 'Our work'),
                    ],
                    'service' => $service ? [
                        'key' => 'service',
                        'uuid' => $service->uuid,
                        'slug' => $service->slug,
                        'path' => '/media/services/'.$service->slug,
                        'label' => $this->t($service, 'title'),
                    ] : null,
                    'current' => $this->t($work, 'title'),
                ],
            ],
            'work' => [
                'date' => $this->workDateLabels($work),
                'category' => $this->t($work, 'category'),
                'title' => $this->t($work, 'title'),
                'tag' => $this->t($work, 'tag'),
                'summary' => $this->t($work, 'summary'),
                'cover_url' => MediaUrl::make($work->cover_image),
                'highlights' => $this->mapStatItems($work->highlights),
            ],
            'tabs' => [
                'about' => [
                    'key' => 'about',
                    'label' => ['ar' => 'عن المشروع', 'en' => 'About the project'],
                ],
                'stages' => [
                    'key' => 'stages',
                    'label' => ['ar' => 'المراحل', 'en' => 'Stages'],
                ],
                'client' => [
                    'key' => 'client',
                    'label' => ['ar' => 'رأي العميل', 'en' => 'Client feedback'],
                ],
            ],
            'about' => [
                'body' => $this->t($work, 'about'),
                'challenges' => [
                    'title' => ['ar' => 'التحديات', 'en' => 'Challenges'],
                    'items' => [
                        'ar' => $this->lines($this->t($work, 'challenges')['ar']),
                        'en' => $this->lines($this->t($work, 'challenges')['en']),
                    ],
                ],
                'solutions' => [
                    'title' => ['ar' => 'الحلول', 'en' => 'Solutions'],
                    'items' => [
                        'ar' => $this->lines($this->t($work, 'solutions')['ar']),
                        'en' => $this->lines($this->t($work, 'solutions')['en']),
                    ],
                ],
            ],
            'stages' => [
                'items' => collect(is_array($work->stages) ? array_values($work->stages) : [])
                    ->filter(fn ($item) => is_array($item) && filled($item['title_ar'] ?? null))
                    ->values()
                    ->map(fn (array $item, int $i) => [
                        'title' => [
                            'ar' => (string) ($item['title_ar'] ?? ''),
                            'en' => (string) ($item['title_en'] ?? ''),
                        ],
                        'body' => [
                            'ar' => (string) ($item['body_ar'] ?? ''),
                            'en' => (string) ($item['body_en'] ?? ''),
                        ],
                        'sort_order' => $i,
                    ])
                    ->all(),
            ],
            'client' => [
                'name' => (string) ($work->client_name ?? ''),
                'role' => $this->t($work, 'client_role'),
                'quote' => $this->t($work, 'client_quote'),
                'avatar_url' => MediaUrl::make($work->client_avatar),
            ],
            'results' => [
                'title' => ['ar' => 'النتائج', 'en' => 'Results'],
                'items' => $this->mapStatItems($work->results),
            ],
            'gallery' => [
                'title' => ['ar' => 'صور من المشروع', 'en' => 'Project photos'],
                'items' => collect(is_array($work->gallery) ? array_values($work->gallery) : [])
                    ->map(fn ($path) => MediaUrl::make(is_string($path) ? $path : null))
                    ->filter()
                    ->values()
                    ->map(fn (string $url, int $i) => [
                        'url' => $url,
                        'sort_order' => $i,
                    ])
                    ->all(),
            ],
            'cta' => $this->sharedServiceCta(),
        ];
    }

    /** @return array<string, mixed> */
    protected function why(): array
    {
        $items = $this->settings->get('media_why_items', []);
        if (! is_array($items)) {
            $items = [];
        }

        return [
            'eyebrow' => $this->settings->i18n('media_why_eyebrow', 'مميزات صوت ميديا', 'Sawt Media advantages'),
            'title' => $this->settings->i18n('media_why_title', 'لماذا صوت ميديا', 'Why Sawt Media'),
            'subtitle' => $this->settings->i18n(
                'media_why_subtitle',
                'صوت ميديا فريق يبني خبرته من حكاية أصعب القصص بمصداقية، وتوصّلها لجمهور عالمي',
                'A team that tells hard stories with credibility and reaches a global audience'
            ),
            'items' => collect(array_values($items))
                ->filter(fn ($item) => is_array($item) && filled($item['title_ar'] ?? null))
                ->values()
                ->map(fn (array $item, int $i) => [
                    'icon_url' => MediaUrl::make($item['icon'] ?? null),
                    'title' => [
                        'ar' => (string) ($item['title_ar'] ?? ''),
                        'en' => (string) ($item['title_en'] ?? ''),
                    ],
                    'description' => [
                        'ar' => (string) ($item['desc_ar'] ?? ''),
                        'en' => (string) ($item['desc_en'] ?? ''),
                    ],
                    'sort_order' => $i,
                ])
                ->all(),
        ];
    }

    /** @return array<string, mixed> */
    protected function methodology(): array
    {
        $steps = $this->settings->get('media_method_steps', []);
        if (! is_array($steps)) {
            $steps = [];
        }

        return [
            'eyebrow' => $this->settings->i18n('media_method_eyebrow', 'منهجيتنا', 'Our methodology'),
            'title' => $this->settings->i18n('media_method_title', 'رحلتنا معك', 'Our journey with you'),
            'subtitle' => $this->settings->i18n(
                'media_method_subtitle',
                'ست خطوات واضحة تضمن لك نتيجة استثنائية في كل مرة',
                'Six clear steps for an exceptional result every time'
            ),
            'steps' => collect(array_values($steps))
                ->filter(fn ($item) => is_array($item) && filled($item['title_ar'] ?? null))
                ->values()
                ->map(fn (array $item, int $i) => [
                    'number' => (string) ($item['number'] ?? sprintf('%02d', $i + 1)),
                    'title' => [
                        'ar' => (string) ($item['title_ar'] ?? ''),
                        'en' => (string) ($item['title_en'] ?? ''),
                    ],
                    'description' => [
                        'ar' => (string) ($item['desc_ar'] ?? ''),
                        'en' => (string) ($item['desc_en'] ?? ''),
                    ],
                    'sort_order' => $i,
                ])
                ->all(),
        ];
    }

    /** Landing «أعمالنا» — chrome from settings, cards from media_works. */
    protected function works(): array
    {
        return [
            'eyebrow' => $this->settings->i18n('media_works_eyebrow', 'أعمالنا', 'Our work'),
            'title' => $this->settings->i18n('media_works_title', 'أبرز أعمالنا', 'Featured projects'),
            'subtitle' => $this->settings->i18n(
                'media_works_subtitle',
                'نستعرض أبرز مشاريعنا في الإنتاج والتصوير والتصميم والتسويق.',
                'Highlighted projects in production, photography, design, and marketing.'
            ),
            'more' => [
                'label' => $this->settings->i18n('media_works_more', 'شاهد المزيد من اعمالنا', 'See more of our work'),
            ],
            'items' => MediaWork::query()
                ->onLanding()
                ->with('service')
                ->get()
                ->values()
                ->map(fn (MediaWork $work, int $i) => $this->mapWorkCard($work, $i))
                ->all(),
        ];
    }

    /** @return array<string, mixed> */
    protected function audiences(): array
    {
        $items = $this->settings->get('media_audiences_items', []);
        if (! is_array($items)) {
            $items = [];
        }

        return [
            'eyebrow' => $this->settings->i18n('media_audiences_eyebrow', 'القطاعات', 'Sectors'),
            'title' => $this->settings->i18n('media_audiences_title', 'من نخدم ؟', 'Who we serve'),
            'subtitle' => $this->settings->i18n(
                'media_audiences_subtitle',
                'تخصص في ثلاثة قطاعات رئيسية نفهم احتياجاتها بعمق.',
                'Three core sectors we know deeply.'
            ),
            'items' => collect(array_values($items))
                ->filter(fn ($item) => is_array($item) && filled($item['title_ar'] ?? null))
                ->values()
                ->map(fn (array $item, int $i) => [
                    'title' => [
                        'ar' => (string) ($item['title_ar'] ?? ''),
                        'en' => (string) ($item['title_en'] ?? ''),
                    ],
                    'tagline' => [
                        'ar' => (string) ($item['tagline_ar'] ?? ''),
                        'en' => (string) ($item['tagline_en'] ?? ''),
                    ],
                    'description' => [
                        'ar' => (string) ($item['desc_ar'] ?? ''),
                        'en' => (string) ($item['desc_en'] ?? ''),
                    ],
                    'bullets' => [
                        'ar' => $this->lines((string) ($item['bullets_ar'] ?? '')),
                        'en' => $this->lines((string) ($item['bullets_en'] ?? '')),
                    ],
                    'sort_order' => $i,
                ])
                ->all(),
        ];
    }

    /** @return array<string, mixed> */
    protected function partners(): array
    {
        $logos = $this->settings->get('media_partners_logos', []);
        if (! is_array($logos)) {
            $logos = [];
        }

        return [
            'eyebrow' => $this->settings->i18n('media_partners_eyebrow', 'صوت ميديا في ارقام', 'Sawt Media in numbers'),
            'title' => $this->settings->i18n('media_partners_title', 'شركاء النجاح', 'Success partners'),
            'subtitle' => $this->settings->i18n('media_partners_subtitle', 'أرقام تعكس ثقة عملائنا وجودة عملنا', 'Numbers that reflect trust and quality'),
            'items' => collect(array_values($logos))
                ->filter(fn ($item) => is_array($item) && (filled($item['logo'] ?? null) || filled($item['name'] ?? null)))
                ->values()
                ->map(fn (array $item, int $i) => [
                    'name' => filled($item['name'] ?? null) ? (string) $item['name'] : null,
                    'logo_url' => MediaUrl::make($item['logo'] ?? null),
                    'url' => filled($item['url'] ?? null) ? (string) $item['url'] : null,
                    'sort_order' => $i,
                ])
                ->all(),
        ];
    }

    /** Landing consultation block + form config (services dropdown + submit path). */
    protected function consultation(): array
    {
        return [
            'eyebrow' => $this->settings->i18n('media_consult_eyebrow', 'الاستشارات', 'Consultations'),
            'title' => $this->settings->i18n(
                'media_consult_title',
                'احجز استشارتك مع خبراء صوت ميديا',
                'Book a consultation with Sawt Media experts'
            ),
            'body' => $this->settings->i18n(
                'media_consult_body',
                'صوت ميديا وكالة إعلامية إبداعية متكاملة.',
                'Sawt Media is a full creative agency.'
            ),
            'bullets' => [
                'ar' => $this->lines((string) ($this->settings->get('media_consult_bullets_ar') ?? '')),
                'en' => $this->lines((string) ($this->settings->get('media_consult_bullets_en') ?? '')),
            ],
            'form' => [
                'title' => $this->settings->i18n('media_consult_form_title', 'احجز الأن', 'Book now'),
                'submit_path' => '/api/v1/pages/media/consultation',
                'method' => 'POST',
                'fields' => [
                    'name' => [
                        'key' => 'name',
                        'label' => ['ar' => 'الاسم الكامل', 'en' => 'Full name'],
                        'required' => true,
                    ],
                    'phone' => [
                        'key' => 'phone',
                        'label' => ['ar' => 'رقم الهاتف', 'en' => 'Phone number'],
                        'required' => true,
                    ],
                    'country_code' => [
                        'key' => 'country_code',
                        'label' => ['ar' => 'رمز الدولة', 'en' => 'Country code'],
                        'required' => false,
                        'default' => '+970',
                    ],
                    'email' => [
                        'key' => 'email',
                        'label' => ['ar' => 'البريد الإلكتروني', 'en' => 'Email'],
                        'required' => true,
                    ],
                    'service' => [
                        'key' => 'service',
                        'label' => ['ar' => 'الخدمة المطلوبة', 'en' => 'Required service'],
                        'placeholder' => [
                            'ar' => 'اختر الخدمة المطلوبة',
                            'en' => 'Select the required service',
                        ],
                        'required' => true,
                    ],
                ],
                // Dropdown options — send option.value (slug) as `service`
                'services' => $this->worksServiceFilterOptions(),
                'submit' => [
                    'label' => $this->settings->i18n('media_consult_submit', 'احجز استشارتك', 'Book your consultation'),
                ],
            ],
        ];
    }

    /** @return array<string, mixed> */
    protected function packages(): array
    {
        $items = $this->settings->get('media_packages_items', []);
        if (! is_array($items)) {
            $items = [];
        }

        return [
            'eyebrow' => $this->settings->i18n('media_packages_eyebrow', 'الباقات', 'Packages'),
            'title' => $this->settings->i18n(
                'media_packages_title',
                'جمعنا لك الخدمات المناسبة في باقة واحدة , اختر باقتك',
                'Bundled services — choose your package'
            ),
            'subtitle' => $this->settings->i18n(
                'media_packages_subtitle',
                'باقات متخصصة حسب نوع الخدمة.',
                'Specialized packages by service type.'
            ),
            'cta' => [
                'key' => 'start_project',
                'path' => '/media/contact',
                'label' => $this->settings->i18n('media_packages_cta', 'ابدأ مشروعك', 'Start your project'),
            ],
            'items' => collect(array_values($items))
                ->filter(fn ($item) => is_array($item) && filled($item['title_ar'] ?? null))
                ->values()
                ->map(fn (array $item, int $i) => [
                    'title' => [
                        'ar' => (string) ($item['title_ar'] ?? ''),
                        'en' => (string) ($item['title_en'] ?? ''),
                    ],
                    'tagline' => [
                        'ar' => (string) ($item['tagline_ar'] ?? ''),
                        'en' => (string) ($item['tagline_en'] ?? ''),
                    ],
                    'description' => [
                        'ar' => (string) ($item['desc_ar'] ?? ''),
                        'en' => (string) ($item['desc_en'] ?? ''),
                    ],
                    'features' => [
                        'ar' => $this->pipeFeatures((string) ($item['features_ar'] ?? '')),
                        'en' => $this->pipeFeatures((string) ($item['features_en'] ?? '')),
                    ],
                    'sort_order' => $i,
                ])
                ->all(),
        ];
    }

    /** @return array<string, mixed> */
    protected function testimonials(): array
    {
        $items = $this->settings->get('media_testimonials_items', []);
        if (! is_array($items)) {
            $items = [];
        }

        return [
            'eyebrow' => $this->settings->i18n('media_testimonials_eyebrow', 'اراء العملاء', 'Client reviews'),
            'title' => $this->settings->i18n('media_testimonials_title', 'ماذا يقول عنّا عملاؤنا', 'What our clients say'),
            'subtitle' => $this->settings->i18n('media_testimonials_subtitle', '', ''),
            'items' => collect(array_values($items))
                ->filter(fn ($item) => is_array($item) && filled($item['name'] ?? null))
                ->values()
                ->map(fn (array $item, int $i) => [
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
                    'sort_order' => $i,
                ])
                ->all(),
        ];
    }

    /** @return array<string, mixed> */
    protected function faq(): array
    {
        $items = $this->settings->get('media_faq_items', []);
        if (! is_array($items)) {
            $items = [];
        }

        return [
            'eyebrow' => $this->settings->i18n('media_faq_eyebrow', 'الأسئلة الشائعة', 'FAQ'),
            'title' => $this->settings->i18n('media_faq_title', 'الأسئلة التي تدور ببالك؟', 'Questions on your mind?'),
            'subtitle' => $this->settings->i18n('media_faq_subtitle', 'أرقام حقيقية تعكس قوة مجتمعنا', 'Real numbers that reflect our community’s strength'),
            'items' => collect(array_values($items))
                ->filter(fn ($item) => is_array($item) && filled($item['question_ar'] ?? null))
                ->values()
                ->map(fn (array $item, int $i) => [
                    'question' => [
                        'ar' => (string) ($item['question_ar'] ?? ''),
                        'en' => (string) ($item['question_en'] ?? ''),
                    ],
                    'answer' => [
                        'ar' => (string) ($item['answer_ar'] ?? ''),
                        'en' => (string) ($item['answer_en'] ?? ''),
                    ],
                    'sort_order' => $i,
                ])
                ->all(),
        ];
    }

    /**
     * Media contact page — target of navbar/hero «ابدأ مشروعك» (`/media/contact`).
     * WhatsApp/email fall back to الإعدادات العامة when media overrides are empty.
     *
     * @return array<string, mixed>
     */
    public function contactPage(): array
    {
        $whatsapp = (string) (
            $this->settings->get('media_contact_wa_number')
            ?: $this->settings->get('support_whatsapp')
            ?: $this->settings->get('contact_phone')
            ?: ''
        );
        $email = (string) (
            $this->settings->get('media_contact_email')
            ?: $this->settings->get('contact_email')
            ?: ''
        );

        return [
            'hero' => [
                'title' => $this->settings->i18n('media_contact_title', 'تواصل معنا', 'Contact us'),
                'subtitle' => $this->settings->i18n(
                    'media_contact_subtitle',
                    'تعرّف على صنّاع المحتوى في صوت، حيث كل قصة إلها صوت، وكل مبدع إله حكاية.',
                    'Meet Sawt creators — every story has a voice, every creator has a tale.'
                ),
            ],
            'intro' => [
                'title' => $this->settings->i18n('media_contact_intro_title', 'لنبدأ العمل سويا', "Let's work together"),
                'body' => $this->settings->i18n(
                    'media_contact_intro_body',
                    'نحن متواجدون للاستماع والرد على جميع تساؤلاتكم لا تترددوا في التواصل معنا عبر الطرق المتاحة أدناه وسنكون سعداء بخدمتكم.',
                    'We are here to listen and answer your questions — reach out through the channels below.'
                ),
            ],
            'channels' => [
                'whatsapp' => [
                    'key' => 'whatsapp',
                    'label' => $this->settings->i18n('media_contact_wa_label', 'تواصل عبر واتساب', 'Contact via WhatsApp'),
                    'hint' => $this->settings->i18n('media_contact_wa_hint', 'رد فوري- متاح دائما', 'Instant reply — always available'),
                    'value' => $whatsapp,
                    'href' => $this->whatsappHref($whatsapp),
                ],
                'email' => [
                    'key' => 'email',
                    'label' => $this->settings->i18n('media_contact_email_label', 'راسلنا على البريد', 'Email us'),
                    'hint' => $email !== '' ? ['ar' => $email, 'en' => $email] : ['ar' => '', 'en' => ''],
                    'value' => $email,
                    'href' => $email !== '' ? 'mailto:'.$email : null,
                ],
            ],
            'trust' => [
                'value' => (string) ($this->settings->get('media_contact_trust_value', '+150') ?? '+150'),
                'label' => $this->settings->i18n('media_contact_trust_label', 'عميل يثقون بنا', 'clients trust us'),
            ],
            // Same CTA key the front already uses for «ابدأ مشروعك»
            'cta_key' => 'start_project',
        ];
    }

    /**
     * Build a wa.me link from a phone string (digits only; empty → null).
     */
    protected function whatsappHref(string $raw): ?string
    {
        $digits = preg_replace('/\D+/', '', $raw) ?: '';

        return $digits !== '' ? 'https://wa.me/'.$digits : null;
    }

    /**
     * Split multiline text into a clean list of lines.
     *
     * @return list<string>
     */
    protected function lines(string $text): array
    {
        return collect(preg_split("/\r\n|\n|\r/", $text) ?: [])
            ->map(fn ($line) => trim((string) $line))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Parse "title|description" lines into feature objects.
     *
     * @return list<array{title: string, description: string}>
     */
    protected function pipeFeatures(string $text): array
    {
        return collect($this->lines($text))
            ->map(function (string $line) {
                $parts = array_map('trim', explode('|', $line, 2));

                return [
                    'title' => $parts[0] ?? '',
                    'description' => $parts[1] ?? '',
                ];
            })
            ->filter(fn (array $f) => $f['title'] !== '')
            ->values()
            ->all();
    }
}

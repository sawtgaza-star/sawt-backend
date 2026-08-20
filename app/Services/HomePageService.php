<?php

namespace App\Services;

use App\Repositories\Contracts\CreatorPageRepositoryInterface;
use App\Repositories\Contracts\SettingRepositoryInterface;
use App\Repositories\Contracts\TeamRepositoryInterface;
use App\Support\MediaUrl;

class HomePageService
{
    public function __construct(
        protected SettingRepositoryInterface $settings,
        protected CreatorPageRepositoryInterface $creators,
        protected TeamRepositoryInterface $team,
        protected InstagramService $instagram,
    ) {}

    /**
     * Homepage payload for the Next.js front (no header/footer).
     *
     * @return array<string, mixed>
     */
    public function page(): array
    {
        return [
            'hero' => $this->hero(),
            'stats' => $this->stats(),
            'who_we_are' => $this->whoWeAre(),
            'news' => $this->news(),
            'creators' => $this->creatorsSection(),
            'platform_sections' => $this->platformSections(),
            'partners' => $this->partners(),
            'stories' => $this->stories(),
            'team' => $this->teamSection(),
            'join_cta' => $this->joinCta(),
            'reviews' => $this->reviews(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function hero(): array
    {
        $slides = $this->settings->get('home_hero_slides', []);
        if (! is_array($slides)) {
            $slides = [];
        }

        return [
            'trust' => $this->settings->i18n(
                'home_hero_trust',
                'ثقة آلاف المتابعين في منصة صوت غزة بصدق وتأثير',
                'Trusted by thousands of followers of Sawt Gaza'
            ),
            'buttons' => [
                'support' => [
                    'label' => $this->settings->i18n('home_hero_btn_support', 'ادعم صوت', 'Support Sawt'),
                ],
                'collaborate' => [
                    'label' => $this->settings->i18n('home_hero_btn_collab', 'تعاون معنا', 'Collaborate with us'),
                ],
            ],
            'slides' => collect(array_values($slides))->map(fn (array $slide, int $index) => [
                'image_url' => MediaUrl::make($slide['image'] ?? null),
                'title' => [
                    'ar' => (string) ($slide['title_ar'] ?? ''),
                    'en' => (string) ($slide['title_en'] ?? ''),
                ],
                'subtitle' => [
                    'ar' => (string) ($slide['subtitle_ar'] ?? ''),
                    'en' => (string) ($slide['subtitle_en'] ?? ''),
                ],
                'sort_order' => $index,
            ])->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function stats(): array
    {
        return [
            'items' => [
                [
                    'key' => 'team',
                    'value' => (string) $this->settings->get('home_stat_team', '20+'),
                    'label' => ['ar' => 'أعضاء الفريق', 'en' => 'Team members'],
                ],
                [
                    'key' => 'stories',
                    'value' => (string) $this->settings->get('home_stat_stories', '100+'),
                    'label' => ['ar' => 'قصة', 'en' => 'Stories'],
                ],
                [
                    'key' => 'views',
                    'value' => (string) $this->settings->get('home_stat_views', '+30'),
                    'label' => ['ar' => 'مشاهدة', 'en' => 'Views'],
                ],
                [
                    'key' => 'videos',
                    'value' => (string) $this->settings->get('home_stat_videos', '30+'),
                    'label' => ['ar' => 'فيديو', 'en' => 'Videos'],
                ],
                [
                    'key' => 'followers',
                    'value' => (string) $this->settings->get('home_stat_followers', '+10'),
                    'label' => ['ar' => 'متابع', 'en' => 'Followers'],
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function whoWeAre(): array
    {
        $features = $this->settings->get('home_who_features', []);
        if (! is_array($features)) {
            $features = [];
        }

        return [
            'section_title' => $this->settings->i18n('home_who_we_are', 'من نحن', 'Who We Are'),
            'section_subtitle' => $this->settings->i18n(
                'home_who_subtitle',
                'إعلام هادف، قصص حقيقية، وأثر مستدام',
                'Impactful media, real stories, and sustainable impact'
            ),
            'image_url' => MediaUrl::make($this->settings->get('home_hero_image')),
            'title' => $this->settings->i18n(
                'home_welcome_title',
                'نؤمن أن لكل إنسان قصة تستحق أن تروى',
                'We believe every person has a story worth telling'
            ),
            'lead' => $this->settings->i18n('home_welcome_lead'),
            'description' => $this->settings->i18n('home_welcome_desc'),
            'features' => collect(array_values($features))->map(fn (array $item, int $index) => [
                'icon_url' => MediaUrl::make($item['icon'] ?? null),
                'title' => [
                    'ar' => (string) ($item['title_ar'] ?? ''),
                    'en' => (string) ($item['title_en'] ?? ''),
                ],
                'sort_order' => $index,
            ])->all(),
            'cta' => [
                'label' => $this->settings->i18n('home_who_cta', 'اكتشف المزيد', 'Discover more'),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function news(): array
    {
        $items = $this->settings->get('home_news_items', []);
        if (! is_array($items)) {
            $items = [];
        }

        return [
            'title' => $this->settings->i18n('home_news_title', 'أخر أخبارنا', 'Our Latest News'),
            'subtitle' => $this->settings->i18n(
                'home_news_subtitle',
                'شاهد أحدث القصص والفيديوهات من منصة صوت',
                'Watch the latest stories and videos from Sawt'
            ),
            'view_all' => [
                'label' => $this->settings->i18n('home_news_view_all', 'عرض جميع الأخبار', 'View all news'),
            ],
            'items' => collect(array_values($items))->map(fn (array $item, int $index) => [
                'image_url' => MediaUrl::make($item['image'] ?? null),
                'title' => [
                    'ar' => (string) ($item['title_ar'] ?? ''),
                    'en' => (string) ($item['title_en'] ?? ''),
                ],
                'excerpt' => [
                    'ar' => (string) ($item['excerpt_ar'] ?? ''),
                    'en' => (string) ($item['excerpt_en'] ?? ''),
                ],
                'date' => $item['date'] ?? null,
                'sort_order' => $index,
            ])->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function creatorsSection(): array
    {
        $limit = (int) ($this->settings->get('home_creators_limit', 10) ?: 10);

        return [
            'title' => $this->settings->i18n('home_creators_title', 'صناع المحتوى في صوت', 'Content Creators in Sawt'),
            'description' => $this->settings->i18n(
                'home_creators_desc',
                'مجموعة من صناع المحتوى المبدعين الذين يوظفون مهاراتهم لإنتاج محتوى هادف ومؤثر.',
                'A group of creative content creators producing purposeful and influential content.'
            ),
            'view_all' => [
                'label' => $this->settings->i18n('home_creators_view_all', 'عرض الكل', 'View all'),
            ],
            'items' => $this->creators->activeCreators(max(1, min(30, $limit))),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function platformSections(): array
    {
        $items = $this->settings->get('home_platform_sections', []);
        if (! is_array($items)) {
            $items = [];
        }

        return [
            'title' => $this->settings->i18n('home_sections_title', 'أقسام المنصة', 'Platform Sections'),
            'subtitle' => $this->settings->i18n(
                'home_sections_subtitle',
                'تعرف على أذرع صوت وكيف نعمل معاً لصناعة الأثر',
                'Discover Sawt’s arms and how we work together for impact'
            ),
            'items' => collect(array_values($items))->map(fn (array $item, int $index) => [
                'image_url' => MediaUrl::make($item['image'] ?? null),
                'title' => [
                    'ar' => (string) ($item['title_ar'] ?? ''),
                    'en' => (string) ($item['title_en'] ?? ''),
                ],
                'description' => [
                    'ar' => (string) ($item['desc_ar'] ?? ''),
                    'en' => (string) ($item['desc_en'] ?? ''),
                ],
                'stats' => [
                    [
                        'ar' => (string) ($item['stat1_ar'] ?? ''),
                        'en' => (string) ($item['stat1_en'] ?? ''),
                    ],
                    [
                        'ar' => (string) ($item['stat2_ar'] ?? ''),
                        'en' => (string) ($item['stat2_en'] ?? ''),
                    ],
                ],
                'cta' => [
                    'label' => [
                        'ar' => (string) ($item['cta_ar'] ?? 'اقرأ المزيد'),
                        'en' => (string) ($item['cta_en'] ?? 'Read more'),
                    ],
                ],
                'sort_order' => $index,
            ])->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function partners(): array
    {
        $items = $this->settings->get('home_partners', []);
        if (! is_array($items)) {
            $items = [];
        }

        return [
            'title' => $this->settings->i18n('home_partners_title', 'شركاؤنا في صوت', 'Our Partners in Sawt'),
            'subtitle' => $this->settings->i18n(
                'home_partners_subtitle',
                'شركاء يشاركونا رحلة التأثير وصناعة التغيير',
                'Partners who share our journey of impact and change'
            ),
            'items' => collect(array_values($items))
                ->filter(fn (array $item) => filled($item['logo'] ?? null) || filled($item['name'] ?? null))
                ->values()
                ->map(fn (array $item, int $index) => [
                    'name' => (string) ($item['name'] ?? ''),
                    'logo_url' => MediaUrl::make($item['logo'] ?? null),
                    'sort_order' => $index,
                ])->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function stories(): array
    {
        $items = $this->settings->get('home_stories_items', []);
        if (! is_array($items)) {
            $items = [];
        }

        return [
            'title' => $this->settings->i18n(
                'home_stories_title',
                'هل لديك صوت يستحق أن يُسمع؟',
                'Do you have a voice that deserves to be heard?'
            ),
            'description' => $this->settings->i18n(
                'home_stories_desc',
                'شاركنا قصتك أو قضيتك، وقد تكون القصة القادمة التي نسلط الضوء عليها ليصل صوتها إلى العالم',
                'Share your story or cause — it may be the next one we highlight so its voice reaches the world'
            ),
            'badge' => $this->settings->i18n(
                'home_stories_badge',
                '+100 قصة واقعية نقلتها صوت إلى العالم',
                '+100 real stories Sawt has brought to the world'
            ),
            'items' => collect(array_values($items))->map(fn (array $item, int $index) => [
                'image_url' => MediaUrl::make($item['image'] ?? null),
                'badge' => [
                    'ar' => (string) ($item['badge_ar'] ?? ''),
                    'en' => (string) ($item['badge_en'] ?? ''),
                ],
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
     * @return array<string, mixed>
     */
    protected function teamSection(): array
    {
        $limit = (int) ($this->settings->get('home_team_limit', 8) ?: 8);

        return [
            'title' => $this->settings->i18n('home_team_title', 'أعضاء فريقنا', 'Our Team Members'),
            'subtitle' => $this->settings->i18n(
                'home_team_subtitle',
                'تعرف على فريق صوت، مبدعين يصنعون الفرق',
                'Get to know the Sawt team, creators who make a difference'
            ),
            'profile_cta' => $this->settings->i18n('home_team_cta', 'عرض الملف الشخصي', 'View profile'),
            'items' => $this->team->activeMembers()->take(max(1, min(30, $limit)))->values(),
        ];
    }

    /**
     * Join-as-creator CTA banner on the homepage.
     *
     * @return array<string, mixed>
     */
    protected function joinCta(): array
    {
        return [
            'image_url' => MediaUrl::make($this->settings->get('home_join_cta_bg')),
            'title' => $this->settings->i18n(
                'home_join_cta_title',
                'انضم إلينا كصانع محتوى',
                'Join us as a content creator'
            ),
            'description' => $this->settings->i18n(
                'home_join_cta_desc',
                'صوت تجمع صناع المحتوى . كن صوت من لا صوت له',
                'Sawt brings together content creators. Be the voice for the voiceless'
            ),
            'button' => [
                'label' => $this->settings->i18n(
                    'home_join_cta_button',
                    'طلب الانضمام',
                    'Request to join'
                ),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function reviews(): array
    {
        $useInstagram = (bool) $this->settings->get('home_reviews_use_instagram', true);

        $reels = [];
        $comments = [];
        $commentsCount = 0;
        $status = 'disabled';

        if ($useInstagram) {
            if (! $this->instagram->isConfigured()) {
                $status = 'missing_credentials';
            } else {
                $fetched = $this->instagram->reels(3, bypassCache: true);

                $reels = collect($fetched)
                    ->map(fn (array $reel, int $index) => [
                        'id' => $reel['id'] ?? null,
                        'caption' => (string) ($reel['caption'] ?? ''),
                        'thumbnail' => $reel['thumbnail'] ?? null,
                        'video_url' => (string) ($reel['video_url'] ?? ''),
                        'permalink' => (string) ($reel['permalink'] ?? ''),
                        'username' => $reel['username'] ?? null,
                        'likes' => $reel['likes'] ?? 0,
                        'comments_count' => $reel['comments'] ?? 0,
                        'views' => $reel['views'] ?? null,
                        'reach' => $reel['reach'] ?? null,
                        'collaborators' => $reel['collaborators'] ?? [],
                        'posted_at' => $reel['posted_at'] ?? null,
                        'sort_order' => $index,
                    ])
                    ->values()
                    ->all();

                $first = $fetched[0] ?? null;
                if ($first) {
                    $comments = $first['comment_items'] ?? [];
                    $commentsCount = (int) ($first['comments'] ?? count($comments));
                }

                $status = $reels === [] ? 'empty' : 'ok';
            }
        }

        return [
            'title' => $this->settings->i18n('home_reviews_title', 'آراؤكم في المحتوى', 'Your opinions on the content'),
            'description' => $this->settings->i18n(
                'home_reviews_desc',
                'نفخر بثقة جمهورنا، ونعتز بكل رأي يساهم في تطوير رسالتنا الإعلامية.',
                'We take pride in our audience’s trust and value every opinion that develops our media message.'
            ),
            'reels_enabled' => $useInstagram,
            'reels_status' => $status,
            'reels' => $reels,
            'comments' => [
                'count' => $commentsCount,
                'items' => $comments,
            ],
        ];
    }
}

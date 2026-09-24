<?php

namespace App\Services;

use App\Models\Creator;
use App\Models\CreatorJoinRequest;
use App\Repositories\Contracts\CreatorPageRepositoryInterface;
use App\Repositories\Contracts\SettingRepositoryInterface;
use App\Support\MediaUrl;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Creators listing + detail page payloads (Settings + Filament + Instagram collab reels).
 */
class CreatorsPageService
{
    public function __construct(
        protected CreatorPageRepositoryInterface $creators,
        protected SettingRepositoryInterface $settings,
        protected CreatorJoinRequestService $joinRequests,
        protected InstagramService $instagram,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function page(): array
    {
        $gridLimit = (int) ($this->settings->get('creators_grid_limit', 10) ?: 10);

        return [
            'hero' => $this->hero(),
            'grid' => [
                'title' => $this->settings->i18n('creators_grid_title', '+47 صانع محتوى ناجح في صوت', '+47 successful content creators in Sawt'),
                'subtitle' => $this->settings->i18n('creators_grid_subtitle'),
                'browse_label' => $this->settings->i18n('creators_card_browse_label', 'تصفح', 'Browse'),
                'experience_title' => $this->settings->i18n(
                    'home_creators_experience_title',
                    'تجربتي مع صوت',
                    'My experience with Sawt'
                ),
                'followers_suffix' => $this->settings->i18n('creators_all_followers_suffix', 'متابع', 'followers'),
                'creators' => $this->creators->activeCreators($gridLimit),
            ],
            'stats' => $this->stats(),
            'join' => $this->joinCta(),
            'partners' => [
                'title' => $this->settings->i18n('creators_partners_title', 'شركات إعلانية تعاونت مع صناع محتوى صوت', 'Advertising companies that collaborated with Sawt creators'),
                'description' => $this->settings->i18n('creators_partners_desc', 'شكراً للشركات التي حملت صوت أهل غزة إلى العالم', 'Thank you to the companies that carried the voice of Gaza to the world'),
                'companies' => $this->creators->activePartnerCompanies(),
            ],
            'collaboration' => $this->collaboration(),
            'faq' => [
                'title' => $this->settings->i18n('creators_faq_title', 'الأسئلة التي تدور ببالك؟ إليك ردودها', 'Questions on your mind? Here are the answers'),
                'subtitle' => $this->settings->i18n('creators_faq_subtitle', 'كل ما تحتاج معرفته قبل أن تبدأ رحلتك مع صوت', 'Everything you need to know before starting your journey with Sawt'),
                'image_url' => MediaUrl::make($this->settings->get('creators_faq_image')),
                'items' => $this->creators->activeFaqs(),
            ],
        ];
    }

    /**
     * Paginated "view all" listing (`/creators/all`).
     *
     * @return array<string, mixed>
     */
    public function all(?string $search = null, ?int $perPage = null): array
    {
        $defaultPerPage = (int) ($this->settings->get('creators_all_per_page', 10) ?: 10);
        $perPage = max(1, min(50, $perPage ?: $defaultPerPage));

        return [
            'hero' => $this->hero(),
            'labels' => [
                'experience_title' => $this->settings->i18n(
                    'home_creators_experience_title',
                    'تجربتي مع صوت',
                    'My experience with Sawt'
                ),
                'followers_suffix' => $this->settings->i18n('creators_all_followers_suffix', 'متابع', 'followers'),
            ],
            'creators' => $this->creators->paginateActiveCreators($perPage, $search),
        ];
    }

    /**
     * Single creator profile: hero, profile, Instagram collab reels, partners, how-it-works, join.
     *
     * @return array<string, mixed>
     *
     * @throws ModelNotFoundException
     */
    public function creator(string $uuid, ?int $reelsLimit = null): array
    {
        $creator = $this->creators->findCreatorByUuid($uuid);

        if (! $creator) {
            throw (new ModelNotFoundException)->setModel(Creator::class, [$uuid]);
        }

        $defaultLimit = (int) ($this->settings->get('creators_detail_reels_limit', 12) ?: 12);
        $reelsLimit = max(1, min(12, $reelsLimit ?: $defaultLimit));

        $content = $this->creatorCollabReels($creator, $reelsLimit);
        $reelItems = $content['items'];
        $reelViewsSum = collect($reelItems)->sum(fn (array $r) => (int) ($r['views'] ?? 0));
        $videosCount = count($reelItems);

        // Prefer Filament views_count; else sum Instagram insights from matched collab reels
        $views = (int) ($creator->views_count ?? 0);
        if ($views <= 0 && $reelViewsSum > 0) {
            $views = $reelViewsSum;
        }

        return [
            'hero' => $this->hero(),
            'creator' => $creator,
            'creator_extras' => [
                'stats' => [
                    'views' => $views,
                    'followers' => (int) ($creator->followers_count ?? 0),
                    'videos' => $videosCount,
                ],
            ],
            'labels' => [
                'follow' => $this->settings->i18n('creators_follow_label', 'متابعة', 'Follow'),
                'bio' => $this->settings->i18n('creators_bio_label', 'نبذة عنه', 'About'),
                'followers' => $this->settings->i18n('creators_followers_label', 'عدد المتابعين', 'Followers'),
                'socials' => $this->settings->i18n('creators_socials_label', 'تابعني على :', 'Follow me on:'),
                'views_suffix' => $this->settings->i18n('creators_views_suffix', 'مشاهدة', 'views'),
                'followers_suffix' => $this->settings->i18n('creators_followers_suffix', 'متابع', 'followers'),
                'videos_suffix' => $this->settings->i18n('creators_videos_suffix', 'فيديو', 'videos'),
                'content_title' => $this->settings->i18n('creators_content_title', 'المحتوى', 'Content'),
                'content_view_more' => $this->settings->i18n('creators_content_view_more', 'رؤية المزيد', 'See more'),
                'collaborations_title' => $this->settings->i18n('creators_detail_collabs_title', 'ابرز التعاونات', 'Top collaborations'),
                'collaborations_desc' => $this->settings->i18n(
                    'creators_detail_collabs_desc',
                    'صناع محتوى صوت جزء لهم بصمتهم مع الشركات المحلية والعالمية',
                    'Sawt creators leave their mark with local and global brands'
                ),
            ],
            'content' => $content,
            // One shared latest Instagram reel for the whole «أبرز التعاونات» player
            'collaborations' => $this->creatorCollaborations($creator),
            'collaboration' => $this->collaboration(),
            // Detail: banner CTA only — full join form stays on listing `GET /pages/creators`
            'join' => $this->joinCta(withForm: false),
        ];
    }

    /**
     * «أبرز التعاونات»: each company has its own caption; one latest platform reel for all tabs.
     *
     * @return array<string, mixed>
     */
    protected function creatorCollaborations(Creator $creator): array
    {
        return [
            'title' => $this->settings->i18n('creators_detail_collabs_title', 'ابرز التعاونات', 'Top collaborations'),
            'description' => $this->settings->i18n(
                'creators_detail_collabs_desc',
                'صناع محتوى صوت جزء لهم بصمتهم مع الشركات المحلية والعالمية',
                'Sawt creators leave their mark with local and global brands'
            ),
            'reel' => $this->latestPlatformReel(),
            'items' => $creator->partnerCompanies ?? collect(),
        ];
    }

    /**
     * Single newest reel from InstagramService (shared by every company tab in collaborations).
     *
     * @return array<string, mixed>|null
     */
    protected function latestPlatformReel(): ?array
    {
        // Master switch or missing credentials — no shared reel for company tabs
        if (! $this->instagram->isEnabled() || ! $this->instagram->isConfigured()) {
            return null;
        }

        // Lite fetch — no per-reel collaborators/insights (fast); newest first from service
        $fetched = $this->instagram->reels(1, bypassCache: false, withExtras: false);
        $reel = $fetched[0] ?? null;

        if (! is_array($reel)) {
            return null;
        }

        return [
            'id' => $reel['id'] ?? null,
            'caption' => (string) ($reel['caption'] ?? ''),
            'thumbnail' => $reel['thumbnail'] ?? null,
            'video_url' => (string) ($reel['video_url'] ?? ''),
            'permalink' => (string) ($reel['permalink'] ?? ''),
            'username' => $reel['username'] ?? null,
            'likes' => $reel['likes'] ?? 0,
            'comments_count' => $reel['comments'] ?? 0,
            'views' => $reel['views'] ?? null,
            'posted_at' => $reel['posted_at'] ?? null,
        ];
    }

    /**
     * Instagram reels where this creator is a collaborator on the platform account («المحتوى»).
     *
     * @return array<string, mixed>
     */
    protected function creatorCollabReels(Creator $creator, int $limit): array
    {
        $igUsername = $creator->instagramUsername();

        $base = [
            'title' => $this->settings->i18n('creators_content_title', 'المحتوى', 'Content'),
            'view_more' => $this->settings->i18n('creators_content_view_more', 'رؤية المزيد', 'See more'),
            'instagram_username' => $igUsername,
            'status' => InstagramService::STATUS_MISSING_CREDENTIALS,
            'message' => null,
            'items' => [],
        ];

        if (! filled($igUsername)) {
            $base['status'] = 'no_instagram';
            $base['message'] = 'Creator has no Instagram social link; cannot match reel collaborators.';

            return $base;
        }

        // Master switch off — skip Graph (same as home/content/reels API)
        if (! $this->instagram->isEnabled()) {
            $base['status'] = InstagramService::STATUS_DISABLED;
            $base['message'] = 'Instagram reels are disabled in Settings (reels_enabled).';

            return $base;
        }

        if (! $this->instagram->isConfigured()) {
            $base['message'] = 'Instagram user id or access token is missing.';

            return $base;
        }

        $fetched = $this->instagram->reelsForCollaborator($igUsername, $limit);

        $base['items'] = collect($fetched)
            ->map(fn (array $reel, int $index) => [
                'id' => $reel['id'] ?? null,
                'caption' => (string) ($reel['caption'] ?? ''),
                'thumbnail' => $reel['thumbnail'] ?? null,
                'video_url' => (string) ($reel['video_url'] ?? ''),
                'permalink' => (string) ($reel['permalink'] ?? ''),
                'username' => $reel['username'] ?? null,
                'likes' => $reel['likes'] ?? 0,
                'comments_count' => $reel['comments'] ?? 0,
                'comment_items' => $reel['comment_items'] ?? [],
                'views' => $reel['views'] ?? null,
                'reach' => $reel['reach'] ?? null,
                'collaborators' => $reel['collaborators'] ?? [],
                'posted_at' => $reel['posted_at'] ?? null,
                'sort_order' => $index,
            ])
            ->values()
            ->all();

        $base['status'] = $this->instagram->lastStatus();
        $base['message'] = $this->instagram->lastMessage();

        return $base;
    }

    /**
     * @return array<string, mixed>
     */
    protected function hero(): array
    {
        return [
            'image_url' => MediaUrl::make($this->settings->get('creators_header_bg')),
            'title' => $this->settings->i18n('creators_hero_title', 'صناع المحتوى في صوت', 'Content Creators in Sawt'),
            'description' => $this->settings->i18n('creators_hero_desc', 'تعرّف على صناع المحتوى في صوت، حيث كل فكرة لها صوت، وكل صانع محتوى له قصة.', 'Get to know the content creators in Sawt, where every idea has a voice, and every creator has a story.'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function stats(): array
    {
        return [
            'title' => $this->settings->i18n('creators_stats_title', 'إنجازات صناع محتوى صوت', 'Achievements of Sawt Content Creators'),
            'subtitle' => $this->settings->i18n('creators_stats_subtitle', 'أرقام حقيقية تعكس قوة مجتمعنا', 'Real numbers reflecting the strength of our community'),
            'items' => [
                [
                    'key' => 'active_creators',
                    'value' => (int) $this->settings->get('creators_stat_creators_value', 45),
                    'label' => $this->settings->i18n('creators_stat_creators_label', 'صانع محتوى نشط', 'Active content creator'),
                    'suffix' => '+',
                ],
                [
                    'key' => 'collaborations',
                    'value' => (int) $this->settings->get('creators_stat_collabs_value', 500),
                    'label' => $this->settings->i18n('creators_stat_collabs_label', 'إعلان تعاوني نُفّذ', 'Collaborative ads executed'),
                    'suffix' => '+',
                ],
                [
                    'key' => 'support',
                    'value' => (int) $this->settings->get('creators_stat_support_value', 250000),
                    'label' => $this->settings->i18n('creators_stat_support_label', 'دعم مالي وُزّع', 'Financial support distributed'),
                    'prefix' => '+$',
                ],
                [
                    'key' => 'reach',
                    'value' => (int) $this->settings->get('creators_stat_reach_value', 4000000),
                    'label' => $this->settings->i18n('creators_stat_reach_label', 'شخص وصلهم المحتوى', 'People reached by content'),
                    'suffix' => '+',
                ],
            ],
        ];
    }

    /**
     * Join CTA banner (+ optional 3-step form for the listing page only).
     *
     * @return array<string, mixed>
     */
    protected function joinCta(bool $withForm = true): array
    {
        $types = $this->settings->get('creators_join_content_types', []);
        if (! is_array($types)) {
            $types = [];
        }

        $payload = [
            'image_url' => MediaUrl::make($this->settings->get('creators_join_bg')),
            'title' => $this->settings->i18n('creators_join_title', 'انضم إلينا كصانع محتوى', 'Join us as a content creator'),
            'description' => $this->settings->i18n('creators_join_desc', 'صوت تجمع صناع المحتوى، كن صوت من لا صوت له', 'Sawt brings together content creators — be the voice for the voiceless'),
            'button' => [
                'label' => $this->settings->i18n('creators_join_button_text', 'طلب الانضمام', 'Request to join'),
            ],
        ];

        if (! $withForm) {
            return $payload;
        }

        $payload['form'] = [
            'title' => $this->settings->i18n('creators_join_form_title', 'انضم إلينا كصانع محتوى', 'Join us as a content creator'),
            'subtitle' => $this->settings->i18n('creators_join_form_subtitle', 'أخبرنا عن نفسك وسنتواصل معك قريباً', 'Tell us about yourself and we will contact you soon'),
            'steps' => [
                ['number' => 1, 'key' => 'personal', 'label' => $this->settings->i18n('creators_join_step_1', 'المعلومات الشخصية', 'Personal information')],
                ['number' => 2, 'key' => 'content', 'label' => $this->settings->i18n('creators_join_step_2', 'تفاصيل المحتوى', 'Content details')],
                ['number' => 3, 'key' => 'socials', 'label' => $this->settings->i18n('creators_join_step_3', 'مواقع التواصل', 'Social media')],
            ],
            'actions' => [
                'next' => $this->settings->i18n('creators_join_next', 'التالي', 'Next'),
                'previous' => $this->settings->i18n('creators_join_prev', 'السابق', 'Previous'),
                'cancel' => $this->settings->i18n('creators_join_cancel', 'إلغاء', 'Cancel'),
                'submit' => $this->settings->i18n('creators_join_submit', 'تسليم الطلب', 'Submit request'),
            ],
            'content_types' => collect($types)->values()->map(fn (array $type) => [
                'key' => (string) ($type['key'] ?? ''),
                'label' => [
                    'ar' => (string) ($type['label_ar'] ?? ''),
                    'en' => (string) ($type['label_en'] ?? ''),
                ],
            ])->filter(fn (array $type) => $type['key'] !== '')->values()->all(),
            'platforms' => collect(CreatorJoinRequest::PLATFORMS)->map(fn (string $platform) => [
                'key' => $platform,
            ])->all(),
            'submit_url' => '/api/v1/pages/creators/join',
        ];

        return $payload;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function submitJoin(array $data): CreatorJoinRequest
    {
        return $this->joinRequests->submit($data);
    }

    /**
     * «كيف يبدأ التعاون مع صناع محتوى صوت؟» — shared on listing + creator detail APIs.
     * Controlled in Settings → صناع المحتوى → خطوات التعاون.
     *
     * @return array<string, mixed>
     */
    protected function collaboration(): array
    {
        return [
            'title' => $this->settings->i18n('creators_collab_title', 'كيف يبدأ التعاون مع صناع محتوى صوت؟', 'How does collaboration with Sawt content creators begin?'),
            'description' => $this->settings->i18n(
                'creators_collab_desc',
                'وصلنا شركات من حول العالم بصنّاع المحتوى في غزة — صوت ميديا هي الجسر الذي يوصلك',
                'We connect companies worldwide with creators in Gaza — Sawt Media is the bridge that gets you there'
            ),
            'diagram' => [
                'creators' => [
                    'title' => $this->settings->i18n('creators_collab_creators_label', 'صناع المحتوى', 'Content Creators'),
                    'subtitle' => $this->settings->i18n('creators_collab_creators_subtitle', 'مبدعو غزة وفلسطين', 'Creators from Gaza and Palestine'),
                ],
                'media' => [
                    'image_url' => MediaUrl::make($this->settings->get('creators_collab_media_image')),
                    'title' => $this->settings->i18n('creators_collab_media_label', 'ميديا صوت', 'Sawt Media'),
                    'subtitle' => $this->settings->i18n('creators_collab_media_subtitle', 'الوسيط الرسمي الموثوق', 'The trusted official intermediary'),
                ],
                'brands' => [
                    'title' => $this->settings->i18n('creators_collab_brands_label', 'الشركات والعلامات', 'Companies and Brands'),
                    'subtitle' => $this->settings->i18n('creators_collab_brands_subtitle', 'التجارية حول العالم', 'Commercial brands worldwide'),
                ],
            ],
            'steps_title' => $this->settings->i18n('creators_collab_steps_title', 'خطوات التعاون', 'Collaboration steps'),
            'steps' => [
                [
                    'number' => 1,
                    'text' => $this->settings->i18n(
                        'creators_collab_step_1',
                        'استعرض ملفات صناعنا وفلتر حسب التخصص والميزانية والوصول الجماهيري',
                        'Browse our creators\' profiles and filter by specialty, budget, and audience reach'
                    ),
                ],
                [
                    'number' => 2,
                    'text' => $this->settings->i18n(
                        'creators_collab_step_2',
                        'فريق صوت ميديا يتولى التنسيق الكامل بينك وبين صانع المحتوى — من التفاصيل حتى العقد',
                        'The Sawt Media team handles full coordination between you and the creator — from details to contract'
                    ),
                ],
                [
                    'number' => 3,
                    'text' => $this->settings->i18n(
                        'creators_collab_step_3',
                        'المحتوى يُنتج ويُنشر، وتحصل على تقرير تفصيلي بالنتائج والوصول والتفاعل',
                        'Content is produced and published, and you get a detailed report on results, reach, and engagement'
                    ),
                ],
            ],
            'cta' => [
                'label' => $this->settings->i18n('creators_collab_cta_label', 'تواصل مع فريق صوت للانضمام', 'Contact the Sawt team to join'),
            ],
        ];
    }
}

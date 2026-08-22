<?php

namespace App\Services;

use App\Models\Creator;
use App\Models\CreatorJoinRequest;
use App\Repositories\Contracts\CreatorPageRepositoryInterface;
use App\Repositories\Contracts\SettingRepositoryInterface;
use App\Support\MediaUrl;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CreatorsPageService
{
    public function __construct(
        protected CreatorPageRepositoryInterface $creators,
        protected SettingRepositoryInterface $settings,
        protected CreatorJoinRequestService $joinRequests,
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
     * @return array<string, mixed>
     *
     * @throws ModelNotFoundException
     */
    public function creator(string $uuid): array
    {
        $creator = $this->creators->findCreatorByUuid($uuid);

        if (! $creator) {
            throw (new ModelNotFoundException)->setModel(Creator::class, [$uuid]);
        }

        return [
            'hero' => $this->hero(),
            'creator' => $creator,
            'labels' => [
                'bio' => $this->settings->i18n('creators_bio_label', 'نبذة عنه', 'About'),
                'followers' => $this->settings->i18n('creators_followers_label', 'عدد المتابعين', 'Followers'),
                'socials' => $this->settings->i18n('creators_socials_label', 'تابعنا على:', 'Follow us on:'),
            ],
        ];
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
     * @return array<string, mixed>
     */
    protected function joinCta(): array
    {
        $types = $this->settings->get('creators_join_content_types', []);
        if (! is_array($types)) {
            $types = [];
        }

        return [
            'image_url' => MediaUrl::make($this->settings->get('creators_join_bg')),
            'title' => $this->settings->i18n('creators_join_title', 'انضم إلينا كصانع محتوى', 'Join us as a content creator'),
            'description' => $this->settings->i18n('creators_join_desc', 'صوت تجمع صناع المحتوى، كن صوت من لا صوت له', 'Sawt brings together content creators — be the voice for the voiceless'),
            'button' => [
                'label' => $this->settings->i18n('creators_join_button_text', 'طلب الانضمام', 'Request to join'),
            ],
            'form' => [
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
                'platforms' => collect(\App\Models\CreatorJoinRequest::PLATFORMS)->map(fn (string $platform) => [
                    'key' => $platform,
                ])->all(),
                'submit_url' => '/api/v1/pages/creators/join',
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function submitJoin(array $data): CreatorJoinRequest
    {
        return $this->joinRequests->submit($data);
    }

    /**
     * @return array<string, mixed>
     */
    protected function collaboration(): array
    {
        return [
            'title' => $this->settings->i18n('creators_collab_title', 'كيف يبدأ التعاون مع صناع محتوى صوت؟', 'How does collaboration with Sawt content creators begin?'),
            'description' => $this->settings->i18n('creators_collab_desc', 'ميديا صوت هي الجسر الذي يربط الشركات بصناع المحتوى في غزة', 'Sawt Media is the bridge connecting companies with content creators in Gaza'),
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
                        'فريق صوت ميديا يتولى التنسيق الكامل بينك وبين صانع المحتوى - من التفاصيل حتى العقد',
                        'The Sawt Media team handles full coordination between you and the creator — from details to contract'
                    ),
                ],
                [
                    'number' => 3,
                    'text' => $this->settings->i18n(
                        'creators_collab_step_3',
                        'المحتوى يُنتج ويُنشر، وتحصل على تقرير تفصيلي بالنتائج والتفاعل',
                        'Content is produced and published, and you get a detailed report on results and engagement'
                    ),
                ],
            ],
            'cta' => [
                'label' => $this->settings->i18n('creators_collab_cta_label', 'تواصل مع فريق صوت للانضمام', 'Contact the Sawt team to join'),
            ],
        ];
    }
}

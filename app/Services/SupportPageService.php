<?php

namespace App\Services;

use App\Models\Campaign;
use App\Models\Story;
use App\Repositories\Contracts\SettingRepositoryInterface;
use App\Support\MediaUrl;

/**
 * Public Support landing page (GET /api/v1/pages/support).
 * Marketing sections from settings; donation plans/methods from SupportService;
 * sponsor packages reused from Incubator; stories from published Story models.
 */
class SupportPageService
{
    public function __construct(
        protected SettingRepositoryInterface $settings,
        protected SupportService $support,
        protected IncubatorService $incubator,
    ) {}

    /**
     * Full ادعم صوت landing payload (no layout chrome).
     *
     * @return array<string, mixed>
     */
    public function page(): array
    {
        return [
            'hero' => $this->hero(),
            'plans' => $this->support->plans(),
            'impact' => $this->impact(),
            'invite' => $this->invite(),
            'trust' => $this->trust(),
            'community_goal' => $this->communityGoal(),
            'fund_allocation' => $this->fundAllocation(),
            'partners' => $this->partners(),
            'sponsor' => $this->sponsor(),
            'stories' => $this->stories(),
            'faq' => $this->faq(),
            'contact_cta' => $this->contactCta(),
            'methods' => $this->methodsTeaser(),
        ];
    }

    /**
     * Top hero only — title/desc + collage background (heroSectionImg.jpeg on live site).
     * Stats/CTAs live under invite(), not here.
     *
     * @return array<string, mixed>
     */
    protected function hero(): array
    {
        return [
            'image_url' => MediaUrl::make($this->settings->get('support_header_bg')),
            'title' => $this->settings->i18n(
                'support_hero_title',
                'ادعم المنصة التي توصل أصواتهم',
                'Support the platform that carries their voices'
            ),
            'description' => $this->settings->i18n(
                'support_hero_desc',
                'كل تبرع يتحوّل إلى قصة تُروى، وصوت يصل إلى العالم من قلب غزة',
                'Every donation becomes a story told — a voice that reaches the world from the heart of Gaza'
            ),
        ];
    }

    /**
     * Mid-page invite card (tree.jpg) — badges, CTAs, shown after impact on live support page.
     *
     * @return array<string, mixed>
     */
    protected function invite(): array
    {
        return [
            'image_url' => MediaUrl::make($this->settings->get('support_invite_image')),
            'image_label' => $this->settings->i18n(
                'support_invite_label',
                'قصص إنسانية من غزة',
                'Human stories from Gaza'
            ),
            'title' => $this->settings->i18n(
                'support_invite_title',
                'ادعم المنصة التي توصل أصواتهم',
                'Support the platform that carries their voices'
            ),
            'badges' => [
                'donors' => [
                    'value' => (string) ($this->settings->get('support_hero_donors_value', '1,247') ?? '1,247'),
                    'label' => $this->settings->i18n(
                        'support_hero_donors_label',
                        'متبرع هذا الشهر',
                        'Donors this month'
                    ),
                ],
                'stories' => [
                    'value' => (string) ($this->settings->get('support_hero_stories_value', '340+') ?? '340+'),
                    'label' => $this->settings->i18n(
                        'support_hero_stories_label',
                        'قصة وثقت',
                        'Stories documented'
                    ),
                ],
            ],
            'cta' => [
                'primary' => $this->settings->i18n('support_hero_cta_primary', 'تبرع الآن', 'Donate now'),
                'secondary' => $this->settings->i18n(
                    'support_hero_cta_secondary',
                    'أين تذهب تبرعاتي؟',
                    'Where do my donations go?'
                ),
            ],
            'trust' => $this->trust(),
        ];
    }

    /**
     * «تبرعك يعني…» impact bullets + quote + side image.
     *
     * @return array<string, mixed>
     */
    protected function impact(): array
    {
        $items = $this->settings->get('support_impact_items', []);
        if (! is_array($items) || $items === []) {
            $items = [
                ['ar' => 'قصة إنسانية جديدة تُروى للعالم', 'en' => 'A new human story told to the world'],
                ['ar' => 'صحفي ميداني مدرَّب على الأرض', 'en' => 'A field journalist trained on the ground'],
                ['ar' => 'تقرير مفحوص يصل للمتابعين', 'en' => 'A verified report that reaches audiences'],
                ['ar' => 'أرشيف رقمي يحمي الذاكرة الجماعية', 'en' => 'A digital archive that protects collective memory'],
            ];
        }

        return [
            'title' => $this->settings->i18n('support_impact_title', 'تبرعك يعني...', 'Your donation means…'),
            'items' => collect(array_values($items))
                ->filter(fn ($row) => is_array($row) && (filled($row['ar'] ?? null) || filled($row['en'] ?? null)))
                ->values()
                ->map(fn (array $row, int $i) => [
                    'text' => [
                        'ar' => (string) ($row['ar'] ?? ''),
                        'en' => (string) ($row['en'] ?? ''),
                    ],
                    'sort_order' => $i,
                ])
                ->all(),
            'quote' => [
                'text' => $this->settings->i18n(
                    'support_impact_quote',
                    'كل تبرع يشجع فيه يعني قصة جديدة توصل للناس — قصة ما كانت تُسمع',
                    'Every donation you encourage means a new story that reaches people — a story that would not have been heard'
                ),
                'author' => $this->settings->i18n('support_impact_quote_author', 'فريق صوت', 'Sawt team'),
                'location' => $this->settings->i18n('support_impact_quote_location', 'غزة، فلسطين', 'Gaza, Palestine'),
            ],
        ];
    }

    /**
     * Trust strip under the donate CTA (ease / instant / secure).
     *
     * @return array<string, mixed>
     */
    protected function trust(): array
    {
        $items = $this->settings->get('support_trust_items', []);
        if (! is_array($items) || $items === []) {
            $items = [
                ['ar' => 'سهولة الدفع', 'en' => 'Easy payment'],
                ['ar' => 'وصول فوري للمستحقين', 'en' => 'Immediate reach to beneficiaries'],
                ['ar' => 'تبرع آمن ومشفر', 'en' => 'Secure encrypted donation'],
            ];
        }

        return [
            'items' => collect(array_values($items))
                ->filter(fn ($row) => is_array($row) && (filled($row['ar'] ?? null) || filled($row['en'] ?? null)))
                ->values()
                ->map(fn (array $row, int $i) => [
                    'label' => [
                        'ar' => (string) ($row['ar'] ?? ''),
                        'en' => (string) ($row['en'] ?? ''),
                    ],
                    'sort_order' => $i,
                ])
                ->all(),
        ];
    }

    /**
     * Monthly community goal — from Campaign or manual settings.
     *
     * @return array<string, mixed>
     */
    protected function communityGoal(): array
    {
        $mode = (string) ($this->settings->get('support_goal_mode', 'manual') ?: 'manual');
        $currency = (string) ($this->settings->get('support_default_currency', 'USD') ?: 'USD');

        $target = (float) ($this->settings->get('support_goal_target', 50000) ?: 50000);
        $raised = (float) ($this->settings->get('support_goal_raised', 32450) ?: 0);

        if ($mode === 'campaign') {
            $campaignId = (int) ($this->settings->get('support_goal_campaign_id') ?: 0);
            $campaign = $campaignId > 0
                ? Campaign::query()->find($campaignId)
                : Campaign::query()->where('status', 'active')->orderByDesc('id')->first();

            if ($campaign) {
                $target = (float) $campaign->target_amount;
                $raised = (float) $campaign->current_amount;
            }
        }

        $remaining = max(0, $target - $raised);
        $progress = $target > 0 ? round(($raised / $target) * 100, 1) : 0;

        return [
            'title' => $this->settings->i18n('support_goal_title', 'مجتمع الدعم الحي', 'Live support community'),
            'subtitle' => $this->settings->i18n(
                'support_goal_subtitle',
                'قيمنا هي الأساس الذي نبني عليه صوت، وهي ما يقود طريقة عملنا وتطويرنا المستمر',
                'Our values are the foundation of Sawt — they guide how we work and grow'
            ),
            'mode' => $mode,
            'currency' => $currency,
            'target' => $target,
            'raised' => $raised,
            'remaining' => $remaining,
            'progress_percent' => $progress,
            'labels' => [
                'target' => $this->settings->i18n('support_goal_label_target', 'هدف الشهر', 'Month goal'),
                'raised' => $this->settings->i18n('support_goal_label_raised', 'تم جمعه', 'Raised'),
                'remaining' => $this->settings->i18n('support_goal_label_remaining', 'متبقي', 'Remaining'),
                'progress' => $this->settings->i18n('support_goal_label_progress', 'الإنجاز', 'Progress'),
            ],
            'message' => $this->settings->i18n(
                'support_goal_message',
                'نحتاج :amount$ لإتمام هدف الشهر — ساهم الآن',
                'We need $:amount to finish this month’s goal — contribute now'
            ),
            'cta' => [
                'label' => $this->settings->i18n(
                    'support_goal_cta',
                    'أضف اسمك للقائمة — تبرع الآن',
                    'Add your name to the list — donate now'
                ),
            ],
        ];
    }

    /**
     * Where donations go — each card carries its own pct (no global fund_split_* fields).
     *
     * @return array<string, mixed>
     */
    protected function fundAllocation(): array
    {
        $cards = $this->settings->get('support_fund_cards', []);
        if (! is_array($cards) || $cards === []) {
            $cards = $this->defaultFundCards();
        }

        return [
            'title' => $this->settings->i18n('support_fund_title', 'أين تذهب تبرعاتكم؟', 'Where do your donations go?'),
            'subtitle' => $this->settings->i18n(
                'support_fund_subtitle',
                'كل دولار يُستثمر بمسؤولية — نُبلّغكم بكل تفصيلة لأن ثقتكم أمانة',
                'Every dollar is invested responsibly — we report every detail because your trust is a responsibility'
            ),
            'items' => collect(array_values($cards))
                ->filter(fn ($c) => is_array($c))
                ->values()
                ->map(fn (array $card, int $i) => [
                    'key' => (string) ($card['key'] ?? 'item_'.$i),
                    'pct' => (int) ($card['pct'] ?? 0),
                    'title' => [
                        'ar' => (string) ($card['title_ar'] ?? ''),
                        'en' => (string) ($card['title_en'] ?? ''),
                    ],
                    'description' => [
                        'ar' => (string) ($card['desc_ar'] ?? ''),
                        'en' => (string) ($card['desc_en'] ?? ''),
                    ],
                    'bullets' => collect($card['bullets'] ?? [])
                        ->map(function ($b) {
                            if (is_string($b)) {
                                return ['ar' => $b, 'en' => $b];
                            }
                            if (! is_array($b)) {
                                return null;
                            }

                            return [
                                'ar' => (string) ($b['ar'] ?? $b['label_ar'] ?? ''),
                                'en' => (string) ($b['en'] ?? $b['label_en'] ?? ''),
                            ];
                        })
                        ->filter()
                        ->values()
                        ->all(),
                    'sort_order' => $i,
                ])
                ->all(),
            'transparency' => [
                'badge' => $this->settings->i18n('support_fund_badge', '100% موزّع بشفافية', '100% allocated transparently'),
                'title' => $this->settings->i18n(
                    'support_fund_note_title',
                    'كل دولار له عنوان واضح',
                    'Every dollar has a clear destination'
                ),
                'body' => $this->settings->i18n(
                    'support_fund_note_body',
                    'نُصدر تقارير شهرية شاملة عن كيفية توزيع التبرعات — وبإمكانك طلب تقرير مفصّل في أي وقت.',
                    'We publish monthly reports on how donations are distributed — and you can request a detailed report anytime.'
                ),
            ],
        ];
    }

    /**
     * Default fund cards when support_fund_cards is empty (matches live sawtgaza.com/support).
     *
     * @return list<array<string, mixed>>
     */
    protected function defaultFundCards(): array
    {
        $bullets = [
            ['ar' => 'أدوات إنتاج احترافية', 'en' => 'Professional production tools'],
            ['ar' => 'منح للمواهب الصاعدة', 'en' => 'Grants for rising talent'],
            ['ar' => 'بيئة إبداعية آمنة ومحفّزة', 'en' => 'A safe, motivating creative environment'],
        ];

        return [
            [
                'key' => 'creators',
                'pct' => 40,
                'title_ar' => 'تمكين المبدعين',
                'title_en' => 'Empowering creators',
                'desc_ar' => 'دعم المبدعين الشباب في غزة بالأدوات والتدريب ليُنتجوا محتوى يُغيّر الرواية ويصنع أثراً حقيقياً.',
                'desc_en' => 'Supporting young creators in Gaza with tools and training to produce content that changes the narrative.',
                'bullets' => $bullets,
            ],
            [
                'key' => 'media',
                'pct' => 35,
                'title_ar' => 'التوثيق والإعلام',
                'title_en' => 'Documentation & media',
                'desc_ar' => 'تمويل التوثيق الميداني والإعلام المهني لإيصال الحقيقة من غزة إلى العالم.',
                'desc_en' => 'Funding field documentation and professional media so truth from Gaza reaches the world.',
                'bullets' => $bullets,
            ],
            [
                'key' => 'ops',
                'pct' => 25,
                'title_ar' => 'الدعم النفسي والتعليمي',
                'title_en' => 'Psychosocial & education support',
                'desc_ar' => 'برامج دعم نفسي وتعليمي تحمي الفريق والمجتمع وتبني قدرة طويلة الأمد.',
                'desc_en' => 'Psychosocial and educational programs that protect the team and community and build lasting capacity.',
                'bullets' => $bullets,
            ],
        ];
    }

    /**
     * Partners logos strip.
     *
     * @return array<string, mixed>
     */
    protected function partners(): array
    {
        $items = $this->settings->get('support_partners', []);
        if (! is_array($items)) {
            $items = [];
        }

        // Fallback to homepage partners if support list empty
        if ($items === []) {
            $home = $this->settings->get('home_partners', []);
            $items = is_array($home) ? $home : [];
        }

        return [
            'title' => $this->settings->i18n(
                'support_partners_title',
                'شركاؤنا في نشر الصوت',
                'Our partners in amplifying the voice'
            ),
            'subtitle' => $this->settings->i18n(
                'support_partners_subtitle',
                'شكراً للمؤسسات والشركات التي تؤمن بمهمتنا وتُوصل صوت أهل غزة للعالم',
                'Thanks to organizations that believe in our mission and carry Gaza’s voice to the world'
            ),
            'cta' => [
                'title' => $this->settings->i18n(
                    'support_partners_cta_title',
                    'الحقيقة تحتاج من يمولها',
                    'Truth needs those who fund it'
                ),
                'body' => $this->settings->i18n(
                    'support_partners_cta_body',
                    'شراكات مؤسسية مع صوت — للجهات التي تريد أن يكون دورها في إيصال الحقيقة للعالم. انضم وأبقِ صوت غزة حياً.',
                    'Institutional partnerships with Sawt — for organizations that want a role in delivering truth to the world. Join us and keep Gaza’s voice alive.'
                ),
                'label' => $this->settings->i18n('support_partners_cta_label', 'تواصل معنا', 'Contact us'),
            ],
            'items' => collect(array_values($items))
                ->filter(fn ($item) => is_array($item) && (filled($item['logo'] ?? null) || filled($item['name'] ?? null)))
                ->values()
                ->map(fn (array $item, int $i) => [
                    'name' => (string) ($item['name'] ?? ''),
                    'logo_url' => MediaUrl::make($item['logo'] ?? null),
                    'sort_order' => $i,
                ])
                ->all(),
        ];
    }

    /**
     * Reuse incubator sponsor packages section for «ساعد طلاب الحاضنة».
     *
     * @return array<string, mixed>
     */
    protected function sponsor(): array
    {
        if (! (bool) $this->settings->get('support_show_sponsor', true)) {
            return [
                'enabled' => false,
                'title' => null,
                'subtitle' => null,
                'packages' => [],
            ];
        }

        $sponsor = $this->incubator->sponsorSection();

        return [
            'enabled' => true,
            ...$sponsor,
        ];
    }

    /**
     * Untold / success stories strip from published stories.
     *
     * @return array<string, mixed>
     */
    protected function stories(): array
    {
        $limit = (int) ($this->settings->get('support_stories_limit', 4) ?: 4);
        $limit = max(1, min(12, $limit));

        $items = Story::query()
            ->published()
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();

        return [
            'title' => $this->settings->i18n(
                'support_stories_title',
                'أصوات لم نقدر على توصيلها',
                'Voices we could not deliver'
            ),
            'subtitle' => $this->settings->i18n(
                'support_stories_subtitle',
                'هذه قصص حقيقية من غزة لم تصل للعالم — لأن الموارد نفدت قبل أن نكمل روايتها',
                'Real stories from Gaza that never reached the world — resources ran out before we could finish telling them'
            ),
            // Per-story badge comes from StoryListingCardResource (Story::primaryBadge)
            'cta' => [
                'title' => $this->settings->i18n(
                    'support_stories_cta_title',
                    'دعمك يمنع القصة القادمة من الضياع',
                    'Your support keeps the next story from being lost'
                ),
                'body' => $this->settings->i18n(
                    'support_stories_cta_body',
                    'تبرعك اليوم يضمن أن الصوت القادم لن يضيع',
                    'Your donation today ensures the next voice will not be lost'
                ),
                'label' => $this->settings->i18n('support_stories_cta_label', 'ادعم المنصة الآن', 'Support the platform now'),
            ],
            'items' => $items->values()->all(),
        ];
    }

    /**
     * Support FAQ accordion.
     *
     * @return array<string, mixed>
     */
    protected function faq(): array
    {
        $items = $this->settings->get('support_faqs', []);
        if (! is_array($items) || $items === []) {
            $items = [
                [
                    'q_ar' => 'كيف يمكنني التبرع؟',
                    'q_en' => 'How can I donate?',
                    'a_ar' => 'اختر المبلغ وطريقة الدفع (بطاقة، PayPal، أو تحويل) واضغط «تبرع الآن». يصلك تأكيد على بريدك.',
                    'a_en' => 'Choose an amount and payment method (card, PayPal, or transfer) and tap Donate now. You will get email confirmation.',
                ],
                [
                    'q_ar' => 'هل التبرع آمن؟',
                    'q_en' => 'Is donating safe?',
                    'a_ar' => 'نعم — المدفوعات مشفّرة عبر مزوّدي دفع موثوقين، ولا نخزّن بيانات بطاقتك.',
                    'a_en' => 'Yes — payments are encrypted via trusted providers, and we never store your card details.',
                ],
                [
                    'q_ar' => 'هل يمكنني التبرع لمرة واحدة؟',
                    'q_en' => 'Can I donate once?',
                    'a_ar' => 'نعم، يمكنك التبرع لمرة واحدة أو اختيار دعم شهري/سنوي.',
                    'a_en' => 'Yes — you can give once or choose monthly/yearly support.',
                ],
                [
                    'q_ar' => 'كيف يتم استخدام التبرعات؟',
                    'q_en' => 'How are donations used?',
                    'a_ar' => 'تُوزَّع بنسب شفافة بين تمكين المبدعين والتوثيق والدعم — انظر قسم «أين تذهب تبرعاتكم؟».',
                    'a_en' => 'They are allocated transparently across creators, documentation, and support — see Where do your donations go?',
                ],
                [
                    'q_ar' => 'هل يمكنني إلغاء الاشتراك الشهري؟',
                    'q_en' => 'Can I cancel a monthly subscription?',
                    'a_ar' => 'نعم، يمكنك إلغاء الاشتراك في أي وقت من حسابك أو بالتواصل معنا.',
                    'a_en' => 'Yes — cancel anytime from your account or by contacting us.',
                ],
            ];
        }

        return [
            'title' => $this->settings->i18n('support_faq_title', 'الأسئلة المتكررة', 'Frequently asked questions'),
            'image_url' => MediaUrl::make($this->settings->get('support_faq_image')),
            'cta' => [
                'title' => $this->settings->i18n('support_faq_cta_title', 'لديك سؤال آخر؟', 'Have another question?'),
                'body' => $this->settings->i18n(
                    'support_faq_cta_body',
                    'فريقنا جاهز للإجابة — سنردّ عليك خلال ساعات',
                    'Our team is ready — we usually reply within hours'
                ),
                'label' => $this->settings->i18n('support_faq_cta_label', 'تواصل معنا', 'Contact us'),
                'image_url' => MediaUrl::make($this->settings->get('support_faq_cta_image')),
            ],
            'items' => collect(array_values($items))
                ->filter(fn ($row) => is_array($row) && filled($row['q_ar'] ?? null))
                ->values()
                ->map(fn (array $row, int $i) => [
                    'question' => [
                        'ar' => (string) ($row['q_ar'] ?? ''),
                        'en' => (string) ($row['q_en'] ?? ''),
                    ],
                    'answer' => [
                        'ar' => (string) ($row['a_ar'] ?? ''),
                        'en' => (string) ($row['a_en'] ?? ''),
                    ],
                    'sort_order' => $i,
                ])
                ->all(),
        ];
    }

    /**
     * Footer contact strip for the support page.
     *
     * @return array<string, mixed>
     */
    protected function contactCta(): array
    {
        return [
            'email' => (string) ($this->settings->get('contact_email') ?: ''),
            'phone' => (string) ($this->settings->get('contact_phone') ?: ''),
            'whatsapp' => (string) ($this->settings->get('support_whatsapp') ?: ''),
        ];
    }

    /**
     * Compact methods categories (without full method details) for landing teaser.
     *
     * @return array<string, mixed>
     */
    protected function methodsTeaser(): array
    {
        $page = $this->support->methodsPage();

        return [
            'title' => $page['section']['title'] ?? null,
            'description' => $page['section']['description'] ?? null,
            'categories' => collect($page['categories'] ?? [])
                ->map(fn (array $cat) => [
                    'key' => $cat['key'],
                    'title' => $cat['title'],
                    'description' => $cat['description'],
                    'icon' => $cat['icon'] ?? '',
                    'accent' => $cat['accent'] ?? '',
                    'is_enabled' => $cat['is_enabled'] ?? true,
                    'methods_count' => $cat['methods_count'] ?? 0,
                ])
                ->values()
                ->all(),
        ];
    }
}

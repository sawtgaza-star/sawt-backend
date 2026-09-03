<?php

namespace App\Services;

use App\Repositories\Contracts\SettingRepositoryInterface;
use App\Support\LayoutLinks;
use App\Support\MediaUrl;

/**
 * Builds public layout JSON for the main platform and the incubator site.
 * Link hrefs come from LayoutLinks (not editable URLs in admin).
 */
class LayoutService
{
    public function __construct(
        protected SettingRepositoryInterface $settings,
    ) {}

    /**
     * Shared navbar + footer for the public site.
     *
     * @return array{navbar: array<string, mixed>, footer: array<string, mixed>}
     */
    public function page(): array
    {
        return [
            'navbar' => $this->navbar(),
            'footer' => $this->footer(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function navbar(): array
    {
        $nav = $this->settings->get('header_nav_links', []);
        if (! is_array($nav)) {
            $nav = [];
        }

        $split = $this->splitNavLinks($nav);

        return [
            'site_name' => (string) $this->settings->get('site_name', 'Sawt'),
            'logo_url' => MediaUrl::make($this->settings->get('home_logo'), '/assets/images/صوت 1.png'),
            'topbar' => [
                'socials_label' => $this->settings->i18n(
                    'header_socials_label',
                    'وسائل التواصل الاجتماعي',
                    'Social Media'
                ),
                'socials' => $this->socials(),
                'support' => $split['support'],
                'auth' => [
                    'register' => [
                        'label' => $this->settings->i18n('header_auth_register_label', 'أنشئ حساب', 'Create account'),
                        'url' => '/register',
                    ],
                    'login' => [
                        'label' => $this->settings->i18n('header_auth_login_label', 'تسجيل الدخول', 'Sign in'),
                        'url' => '/login',
                    ],
                ],
                'search_placeholder' => [
                    'ar' => 'ابحث هنا...',
                    'en' => 'Search here...',
                ],
                'language' => [
                    'label' => ['ar' => 'En', 'en' => 'Ar'],
                ],
            ],
            'nav' => [
                'primary' => $split['primary'],
                'secondary' => $split['secondary'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function footer(): array
    {
        $main = $this->settings->get('footer_main_links', []);
        $quick = $this->settings->get('footer_quick_links', []);
        if (! is_array($main)) {
            $main = [];
        }
        if (! is_array($quick)) {
            $quick = [];
        }

        return [
            'logo_url' => MediaUrl::make($this->settings->get('footer_logo'), MediaUrl::make($this->settings->get('home_logo'), '/assets/images/صوت 1.png')),
            'about' => $this->settings->i18n(
                'footer_about',
                'منصة صوت، تأسست لتكون مساحة للمبدعين، تجمع الحاضنة، صوت ميديا، والصوت نفسه، لتقديم محتوى ملهم وتجارب فريدة لكل من يسعى لصوته أن يُسمع.',
                'Sawt platform was founded as a space for creators — bringing together the incubator, Sawt Media, and Sawt itself to deliver inspiring content and unique experiences.'
            ),
            'main' => [
                'title' => $this->settings->i18n('footer_main_title', 'الأقسام الرئيسية', 'Main Sections'),
                'links' => $this->mapLinks($main),
            ],
            'quick' => [
                'title' => $this->settings->i18n('footer_quick_title', 'روابط سريعة', 'Quick Links'),
                'links' => $this->mapLinks($quick, allowCustomUrl: true),
            ],
            'newsletter' => [
                'title' => $this->settings->i18n('footer_newsletter_title', 'ابقَ على اطلاع', 'Stay Updated'),
                'description' => $this->settings->i18n('footer_newsletter_desc', 'اشترك في نشرتنا الإخبارية ..', 'Subscribe to our newsletter..'),
                'email_placeholder' => [
                    'ar' => 'ادخل بريدك الالكتروني',
                    'en' => 'Enter your email',
                ],
            ],
            'contact' => [
                'phone' => (string) $this->settings->get('contact_phone', ''),
                'email' => (string) $this->settings->get('contact_email', ''),
            ],
            'socials' => $this->socials(),
            'copyright' => $this->settings->i18n('footer_copyright', '© جميع الحقوق محفوظة. 2026', '© All rights reserved. 2026'),
            'brand' => (string) $this->settings->get('footer_brand', 'SAWTGAZA'),
        ];
    }

    /**
     * Incubator site chrome (navbar + footer).
     *
     * @return array{navbar: array<string, mixed>, footer: array<string, mixed>}
     */
    public function incubatorPage(): array
    {
        return [
            'navbar' => $this->incubatorNavbar(),
            'footer' => $this->incubatorFooter(),
        ];
    }

    /**
     * Incubator navbar — labels from settings; paths from LayoutLinks::incubatorPathFor*.
     *
     * @return array<string, mixed>
     */
    public function incubatorNavbar(): array
    {
        $nav = $this->settings->get('incubator_nav_links', []);
        if (! is_array($nav)) {
            $nav = [];
        }

        return [
            'site_name' => (string) ($this->settings->get('incubator_site_name') ?: 'حاضنة صوت'),
            'logo_url' => MediaUrl::make(
                $this->settings->get('incubator_logo'),
                MediaUrl::make($this->settings->get('home_logo'), '/assets/images/صوت 1.png')
            ),
            'back_to_platform' => [
                'label' => $this->settings->i18n(
                    'incubator_back_label',
                    'العودة لمنصة صوت',
                    'Back to Sawt Platform'
                ),
                'key' => 'platform',
            ],
            'socials_label' => $this->settings->i18n(
                'incubator_socials_label',
                'وسائل التواصل الاجتماعي',
                'Social Media'
            ),
            'socials' => $this->socials(),
            'topbar' => [
                'socials_label' => $this->settings->i18n(
                    'incubator_socials_label',
                    'وسائل التواصل الاجتماعي',
                    'Social Media'
                ),
                'socials' => $this->socials(),
                'language' => [
                    'label' => ['ar' => 'En', 'en' => 'Ar'],
                ],
            ],
            'nav' => [
                'primary' => $this->mapIncubatorLinks($nav),
            ],
            'actions' => [
                'join' => [
                    'key' => 'join',
                    'label' => $this->settings->i18n(
                        'incubator_nav_join_label',
                        'انضم للحاضنة',
                        'Join the incubator'
                    ),
                ],
                'support' => [
                    'key' => 'support_students',
                    'label' => $this->settings->i18n(
                        'incubator_nav_support_label',
                        'ادعم طلاب الحاضنة',
                        'Support incubator students'
                    ),
                ],
            ],
        ];
    }

    /**
     * Incubator footer — about, main/Sawt link columns, newsletter, copyright.
     *
     * @return array<string, mixed>
     */
    public function incubatorFooter(): array
    {
        $main = $this->settings->get('incubator_footer_main_links', []);
        $sawt = $this->settings->get('incubator_footer_sawt_links', []);
        if (! is_array($main)) {
            $main = [];
        }
        if (! is_array($sawt)) {
            $sawt = [];
        }

        return [
            'logo_url' => MediaUrl::make(
                $this->settings->get('incubator_footer_logo'),
                MediaUrl::make(
                    $this->settings->get('incubator_logo'),
                    MediaUrl::make($this->settings->get('footer_logo'), MediaUrl::make($this->settings->get('home_logo'), '/assets/images/صوت 1.png'))
                )
            ),
            'about' => $this->settings->i18n(
                'incubator_footer_about',
                'منصة صوت، تأسست لتكون مساحة للمبدعين، تجمع الحاضنة، صوت ميديا، والصوت نفسه، لتقديم محتوى ملهم وتجارب فريدة لكل من يسعى لصوته أن يُسمع.',
                'Sawt platform was founded as a space for creators — bringing together the incubator, Sawt Media, and Sawt itself.'
            ),
            'main' => [
                'title' => $this->settings->i18n('incubator_footer_main_title', 'الأقسام الرئيسية', 'Main Sections'),
                'links' => $this->mapIncubatorLinks($main),
            ],
            'sawt' => [
                'title' => $this->settings->i18n('incubator_footer_sawt_title', 'اقسام صوت', 'Sawt Sections'),
                'links' => $this->mapIncubatorLinks($sawt),
            ],
            'newsletter' => [
                'title' => $this->settings->i18n('incubator_footer_newsletter_title', 'ابقَ على اطلاع', 'Stay Updated'),
                'description' => $this->settings->i18n(
                    'incubator_footer_newsletter_desc',
                    'اشترك في نشرتنا الإخبارية ..',
                    'Subscribe to our newsletter..'
                ),
                'email_placeholder' => [
                    'ar' => 'ادخل بريدك الالكتروني',
                    'en' => 'Enter your email',
                ],
            ],
            'contact' => [
                'phone' => (string) $this->settings->get('contact_phone', ''),
                'email' => (string) $this->settings->get('contact_email', ''),
            ],
            'socials_label' => $this->settings->i18n(
                'incubator_socials_label',
                'وسائل التواصل الاجتماعي',
                'Social Media'
            ),
            'socials' => $this->socials(),
            'copyright' => $this->settings->i18n(
                'incubator_footer_copyright',
                '© جميع الحقوق محفوظة. 2026',
                '© All rights reserved. 2026'
            ),
            'brand' => (string) ($this->settings->get('incubator_footer_brand') ?: $this->settings->get('footer_brand', 'SAWTGAZA')),
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $items
     * @return list<array{key: string, label: array{ar: string, en: string}}>
     */
    protected function mapIncubatorLinks(array $items, bool $allowCustomUrl = false): array
    {
        return collect(LayoutLinks::visible($items))
            ->map(fn (array $item) => [
                'key' => (string) ($item['key'] ?? ''),
                'label' => [
                    'ar' => (string) ($item['label_ar'] ?? ''),
                    'en' => (string) ($item['label_en'] ?? ''),
                ],
            ])
            ->values()
            ->all();
    }

    /**
     * Split header nav items into topbar support, primary row, and secondary row.
     *
     * @param  list<array<string, mixed>>  $items
     * @return array{primary: list<array<string, mixed>>, secondary: list<array<string, mixed>>, support: ?array<string, mixed>}
     */
    protected function splitNavLinks(array $items): array
    {
        $primary = [];
        $secondary = [];
        $support = null;

        foreach (LayoutLinks::visible($items) as $item) {
            $key = (string) ($item['key'] ?? '');

            if (in_array($key, LayoutLinks::NAV_EXCLUDED_KEYS, true)) {
                continue;
            }

            $link = [
                'key' => $key,
                'label' => [
                    'ar' => (string) ($item['label_ar'] ?? ''),
                    'en' => (string) ($item['label_en'] ?? ''),
                ],
                'url' => LayoutLinks::pathForKey($key),
            ];

            if (in_array($key, LayoutLinks::NAV_TOPBAR_KEYS, true)) {
                $support = $link;

                continue;
            }

            if (in_array($key, LayoutLinks::NAV_SECONDARY_KEYS, true)) {
                $secondary[] = array_merge($link, ['external' => true]);

                continue;
            }

            $primary[] = $link;
        }

        return [
            'primary' => $primary,
            'secondary' => $secondary,
            'support' => $support,
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $items
     * @return list<array{key: string, label: array{ar: string, en: string}, url: string}>
     */
    protected function mapLinks(array $items, bool $allowCustomUrl = false): array
    {
        return collect(LayoutLinks::visible($items))
            ->map(fn (array $item) => [
                'key' => (string) ($item['key'] ?? ''),
                'label' => [
                    'ar' => (string) ($item['label_ar'] ?? ''),
                    'en' => (string) ($item['label_en'] ?? ''),
                ],
                'url' => $allowCustomUrl
                    ? LayoutLinks::pathForItem($item)
                    : LayoutLinks::pathForKey($item['key'] ?? null),
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array{platform: string, url: string}>
     */
    protected function socials(): array
    {
        $platforms = ['instagram', 'twitter', 'telegram', 'facebook', 'linkedin', 'youtube'];

        return collect($platforms)
            ->map(fn (string $platform) => [
                'platform' => $platform,
                'url' => (string) ($this->settings->get($platform.'_url') ?: ''),
            ])
            ->filter(fn (array $item) => $item['url'] !== '' && $item['url'] !== '#')
            ->values()
            ->all();
    }
}

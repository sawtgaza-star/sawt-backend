<?php

namespace App\Services;

use App\Repositories\Contracts\SettingRepositoryInterface;
use App\Support\LayoutLinks;
use App\Support\MediaUrl;

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

        return [
            'site_name' => (string) $this->settings->get('site_name', 'Sawt'),
            'logo_url' => MediaUrl::make($this->settings->get('home_logo'), '/assets/images/صوت 1.png'),
            'search_placeholder' => [
                'ar' => 'ابحث هنا...',
                'en' => 'Search here...',
            ],
            'auth' => [
                'register' => [
                    'label' => ['ar' => 'أنشئ حساب', 'en' => 'Create account'],
                    'url' => '/register',
                ],
                'login' => [
                    'label' => ['ar' => 'تسجيل الدخول', 'en' => 'Sign in'],
                    'url' => '/login',
                ],
            ],
            'nav' => $this->mapLinks($nav),
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

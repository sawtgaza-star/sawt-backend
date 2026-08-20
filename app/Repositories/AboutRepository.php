<?php

namespace App\Repositories;

use App\Repositories\Contracts\AboutRepositoryInterface;
use App\Repositories\Contracts\SettingRepositoryInterface;
use App\Support\MediaUrl;

class AboutRepository implements AboutRepositoryInterface
{
    public function __construct(
        protected SettingRepositoryInterface $settings,
    ) {}

    public function page(): array
    {
        $valuesDefault = [
            ['icon' => null, 'title_ar' => 'المصداقية', 'title_en' => 'Credibility', 'desc_ar' => 'ننقل القصص والحقائق بدقة وموضوعية، ملتزمين بالتحقق من المعلومات واحترام ثقة جمهورنا.', 'desc_en' => 'We convey stories and facts accurately and objectively.'],
            ['icon' => null, 'title_ar' => 'الإنسانية', 'title_en' => 'Humanity', 'desc_ar' => 'نضع الإنسان في قلب كل قصة، ونؤمن بأن لكل فرد حقاً في أن يُسمع ويُمثل بكرامة واحترام.', 'desc_en' => 'We put the human at the heart of every story.'],
            ['icon' => null, 'title_ar' => 'التأثير', 'title_en' => 'Impact', 'desc_ar' => 'نسعى لصناعة محتوى يرفع الوعي، ويحدث أثراً إيجابياً في المجتمع، ويحفز التغيير نحو الأفضل.', 'desc_en' => 'We strive to create content that raises awareness.'],
            ['icon' => null, 'title_ar' => 'الاستقلالية', 'title_en' => 'Independence', 'desc_ar' => 'نلتزم بإعلام مستقل يعكس الواقع بصدق، بعيداً عن أي تحيزات أو أجندات تؤثر على رسالتنا.', 'desc_en' => 'We are committed to independent media.'],
        ];

        $storyDefault = [
            ['icon' => null, 'title_ar' => 'رحلتنا', 'title_en' => 'Our Journey', 'desc_ar' => 'بدأت رحلة «صوت» في ظل ظروف صعبة حيث كانت الكثير من القصص الحقيقية مخفية.', 'desc_en' => 'The journey of Sawt began under difficult circumstances.'],
            ['icon' => null, 'title_ar' => 'ما نقدم', 'title_en' => 'What We Offer', 'desc_ar' => 'نحن نقدم إعلاماً حقيقياً يعتمد على القصص الحقيقية والأصوات الصادقة.', 'desc_en' => 'We provide genuine media built on real stories.'],
            ['icon' => null, 'title_ar' => 'التأثير', 'title_en' => 'Impact', 'desc_ar' => 'منذ انطلاقنا، استطعنا إيصال أصوات آلاف من الأشخاص الذين كانوا صامتين.', 'desc_en' => 'Since launch, we have amplified thousands of silent voices.'],
        ];

        $values = $this->settings->get('about_core_values', $valuesDefault);
        if (! is_array($values) || empty($values)) {
            $values = $valuesDefault;
        }

        $storyCards = $this->settings->get('about_story_cards', $storyDefault);
        if (! is_array($storyCards) || empty($storyCards)) {
            $storyCards = $storyDefault;
        }

        return [
            'hero' => [
                'image_url' => MediaUrl::make($this->settings->get('about_header_bg')),
                'title' => $this->settings->i18n('about_hero_title', 'صناع الأثر.. الفريق خلف منصة صوت', 'Impact Makers.. The Team Behind Sawt'),
                'description' => $this->settings->i18n('about_hero_desc'),
            ],
            'intro' => [
                'image_url' => MediaUrl::make($this->settings->get('about_intro_image')),
                'title' => $this->settings->i18n('about_header', 'من نحن', 'About Us'),
                'body' => $this->settings->i18n('about_intro'),
            ],
            'values' => [
                'title' => $this->settings->i18n('about_core_values_title', 'أهم القيم التي نركز عليها', 'The values we focus on'),
                'subtitle' => $this->settings->i18n('about_core_values_subtitle'),
                'items' => collect(array_values($values))->map(fn (array $item, int $index) => [
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
            ],
            'platform' => [
                'image_url' => MediaUrl::make($this->settings->get('about_platform_image')),
                'title' => $this->settings->i18n('about_platform_question', 'ما الذي يدفعنا لنكون صوتك؟', 'What drives us to be your voice?'),
                'description' => $this->settings->i18n('about_platform_desc'),
            ],
            'story' => [
                'title' => $this->settings->i18n('about_story_title', 'قصة صوت', 'Sawt Story'),
                'subtitle' => $this->settings->i18n('about_story_subtitle'),
                'cards' => collect(array_values($storyCards))->map(fn (array $item, int $index) => [
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
            ],
            'join' => [
                'image_url' => MediaUrl::make($this->settings->get('about_join_bg')),
                'title' => $this->settings->i18n('about_join_title', 'لأن بعض الأصوات لا يجب أن تُنسى', 'Because some voices should not be forgotten'),
                'description' => $this->settings->i18n('about_join_desc'),
                'button_text' => $this->settings->i18n('about_join_button_text', 'مساهمة بإيصال صوت', 'Help amplify a voice'),
            ],
        ];
    }
}

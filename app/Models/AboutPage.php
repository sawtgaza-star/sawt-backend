<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use App\Support\MediaUrl;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class AboutPage extends Model
{
    use HasTranslations, HasUuid;

    public array $translatable = [
        'hero_title',
        'hero_description',
        'intro_title',
        'intro_body',
        'values_title',
        'values_subtitle',
        'platform_title',
        'platform_description',
        'story_title',
        'story_subtitle',
        'join_title',
        'join_description',
        'join_button_text',
    ];

    protected $fillable = [
        'hero_image',
        'hero_title',
        'hero_description',
        'intro_image',
        'intro_title',
        'intro_body',
        'values_title',
        'values_subtitle',
        'platform_image',
        'platform_title',
        'platform_description',
        'story_title',
        'story_subtitle',
        'join_image',
        'join_title',
        'join_description',
        'join_button_text',
        'join_button_url',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function values(): HasMany
    {
        return $this->hasMany(AboutValue::class)->orderBy('sort_order');
    }

    public function storyCards(): HasMany
    {
        return $this->hasMany(AboutStoryCard::class)->orderBy('sort_order');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public static function current(): self
    {
        $page = static::query()->active()->with([
            'values' => fn ($q) => $q->active(),
            'storyCards' => fn ($q) => $q->active(),
        ])->first();

        if ($page) {
            return $page;
        }

        $page = static::query()->create(static::defaultAttributes());
        $page->seedDefaultChildren();

        return $page->fresh([
            'values' => fn ($q) => $q->active(),
            'storyCards' => fn ($q) => $q->active(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public static function defaultAttributes(): array
    {
        $fromSettings = static::attributesFromSettings();

        if ($fromSettings !== null) {
            return $fromSettings;
        }

        return [
            'hero_title' => ['ar' => 'صناع الأثر.. الفريق خلف منصة صوت', 'en' => 'Impact Makers.. The Team Behind Sawt'],
            'hero_description' => [
                'ar' => 'صوت منصة إعلامية مستقلة توثق الواقع وتحكي قصص الناس، لتكون صوتا لمن لا صوت له.',
                'en' => 'Sawt is an independent media platform that documents reality and tells people\'s stories.',
            ],
            'intro_title' => ['ar' => 'من نحن', 'en' => 'About Us'],
            'intro_body' => ['ar' => '', 'en' => ''],
            'values_title' => ['ar' => 'أهم القيم التي نركز عليها', 'en' => 'The values we focus on'],
            'values_subtitle' => [
                'ar' => 'قيمنا هي الأساس الذي نبني عليه صوت، وهي ما يقود طريقة عملنا وتطويرنا المستمر',
                'en' => 'Our values are the foundation on which we build Sawt.',
            ],
            'platform_title' => ['ar' => 'ما الذي يدفعنا لنكون صوتك؟', 'en' => 'What drives us to be your voice?'],
            'platform_description' => ['ar' => '', 'en' => ''],
            'story_title' => ['ar' => 'قصة صوت', 'en' => 'Sawt Story'],
            'story_subtitle' => [
                'ar' => 'من فكرة بسيطة إلى منصة تحمل صوت الناس وتصل لقلوبهم.',
                'en' => 'From a simple idea to a platform that carries people\'s voices.',
            ],
            'join_title' => ['ar' => 'لأن بعض الأصوات لا يجب أن تُنسى', 'en' => 'Because some voices should not be forgotten'],
            'join_description' => [
                'ar' => 'مساهمتك ليست دعماً لمنصة إعلامية فحسب، بل دعماً لأصوات وقصص تنتظر من ينقلها',
                'en' => 'Your contribution supports voices and stories waiting to be told.',
            ],
            'join_button_text' => ['ar' => 'مساهمة بإيصال صوت', 'en' => 'Help amplify a voice'],
            'join_button_url' => '/donate',
            'is_active' => true,
        ];
    }

    /**
     * Prefer existing Settings values when creating the first AboutPage row.
     *
     * @return array<string, mixed>|null
     */
    protected static function attributesFromSettings(): ?array
    {
        if (! class_exists(Setting::class) || ! Setting::query()->where('group', 'about')->exists()) {
            return null;
        }

        $t = static fn (string $arKey, string $enKey, string $fallbackAr = '', string $fallbackEn = '') => [
            'ar' => (string) (Setting::get($arKey) ?: $fallbackAr),
            'en' => (string) (Setting::get($enKey) ?: $fallbackEn),
        ];

        return [
            'hero_image' => Setting::get('about_header_bg') ?: null,
            'hero_title' => $t('about_hero_title_ar', 'about_hero_title_en', 'صناع الأثر.. الفريق خلف منصة صوت', 'Impact Makers.. The Team Behind Sawt'),
            'hero_description' => $t('about_hero_desc_ar', 'about_hero_desc_en'),
            'intro_image' => Setting::get('about_intro_image') ?: null,
            'intro_title' => $t('about_header_ar', 'about_header_en', 'من نحن', 'About Us'),
            'intro_body' => $t('about_intro_ar', 'about_intro_en'),
            'values_title' => ['ar' => 'أهم القيم التي نركز عليها', 'en' => 'The values we focus on'],
            'values_subtitle' => $t('about_core_values_subtitle_ar', 'about_core_values_subtitle_en'),
            'platform_image' => Setting::get('about_platform_image') ?: null,
            'platform_title' => $t('about_platform_question_ar', 'about_platform_question_en', 'ما الذي يدفعنا لنكون صوتك؟', 'What drives us to be your voice?'),
            'platform_description' => $t('about_platform_desc_ar', 'about_platform_desc_en'),
            'story_title' => ['ar' => 'قصة صوت', 'en' => 'Sawt Story'],
            'story_subtitle' => $t('about_story_subtitle_ar', 'about_story_subtitle_en'),
            'join_image' => Setting::get('about_join_bg') ?: null,
            'join_title' => $t('about_join_title_ar', 'about_join_title_en'),
            'join_description' => $t('about_join_desc_ar', 'about_join_desc_en'),
            'join_button_text' => ['ar' => 'مساهمة بإيصال صوت', 'en' => 'Help amplify a voice'],
            'join_button_url' => '/donate',
            'is_active' => true,
        ];
    }

    public function seedDefaultChildren(): void
    {
        if ($this->values()->exists() || $this->storyCards()->exists()) {
            return;
        }

        $values = Setting::get('about_core_values');
        if (! is_array($values) || empty($values)) {
            $values = [
                [
                    'title_ar' => 'المصداقية',
                    'title_en' => 'Credibility',
                    'desc_ar' => 'ننقل القصص والحقائق بدقة وموضوعية، ملتزمين بالتحقق من المعلومات واحترام ثقة جمهورنا.',
                    'desc_en' => 'We convey stories and facts accurately and objectively, committed to verifying information and respecting our audience\'s trust.',
                ],
                [
                    'title_ar' => 'الإنسانية',
                    'title_en' => 'Humanity',
                    'desc_ar' => 'نضع الإنسان في قلب كل قصة، ونؤمن بأن لكل فرد حقاً في أن يُسمع ويُمثل بكرامة واحترام.',
                    'desc_en' => 'We put the human at the heart of every story, and believe every individual has a right to be heard with dignity and respect.',
                ],
                [
                    'title_ar' => 'التأثير',
                    'title_en' => 'Impact',
                    'desc_ar' => 'نسعى لصناعة محتوى يرفع الوعي، ويحدث أثراً إيجابياً في المجتمع، ويحفز التغيير نحو الأفضل.',
                    'desc_en' => 'We strive to create content that raises awareness, creates positive impact, and stimulates change for the better.',
                ],
                [
                    'title_ar' => 'الاستقلالية',
                    'title_en' => 'Independence',
                    'desc_ar' => 'نلتزم بإعلام مستقل يعكس الواقع بصدق، بعيداً عن أي تحيزات أو أجندات تؤثر على رسالتنا.',
                    'desc_en' => 'We are committed to independent media that reflects reality honestly, away from biases or agendas.',
                ],
            ];
        }

        foreach (array_values($values) as $i => $value) {
            $this->values()->create([
                'title' => ['ar' => $value['title_ar'] ?? '', 'en' => $value['title_en'] ?? ''],
                'description' => ['ar' => $value['desc_ar'] ?? '', 'en' => $value['desc_en'] ?? ''],
                'sort_order' => $i,
                'is_active' => true,
            ]);
        }

        $cards = Setting::get('about_story_cards');
        if (! is_array($cards) || empty($cards)) {
            $cards = [
                [
                    'title_ar' => 'رحلتنا',
                    'title_en' => 'Our Journey',
                    'desc_ar' => 'بدأت رحلة «صوت» في ظل ظروف صعبة حيث كانت الكثير من القصص الحقيقية مخفية والأصوات الصادقة مكبوتة.',
                    'desc_en' => 'The journey of Sawt began under difficult circumstances, when many real stories were hidden and honest voices muted.',
                ],
                [
                    'title_ar' => 'ما نقدم',
                    'title_en' => 'What We Offer',
                    'desc_ar' => 'نحن نقدم إعلاماً حقيقياً يعتمد على القصص الحقيقية والأصوات الصادقة بعيداً عن ضغوط الإعلام التقليدي.',
                    'desc_en' => 'We provide genuine media built on real stories and honest voices, away from traditional media pressures.',
                ],
                [
                    'title_ar' => 'التأثير',
                    'title_en' => 'Impact',
                    'desc_ar' => 'منذ انطلاقنا، استطعنا إيصال أصوات آلاف من الأشخاص الذين كانوا صامتين، وكشفنا حقائق عديدة لم يتناولها الرأي العام.',
                    'desc_en' => 'Since launch, we have amplified thousands of silent voices and uncovered facts missed by public opinion.',
                ],
            ];
        }

        foreach (array_values($cards) as $i => $card) {
            $this->storyCards()->create([
                'title' => ['ar' => $card['title_ar'] ?? '', 'en' => $card['title_en'] ?? ''],
                'description' => ['ar' => $card['desc_ar'] ?? '', 'en' => $card['desc_en'] ?? ''],
                'sort_order' => $i,
                'is_active' => true,
            ]);
        }
    }

    public function heroImageUrl(): ?string
    {
        return MediaUrl::make($this->hero_image);
    }

    public function introImageUrl(): ?string
    {
        return MediaUrl::make($this->intro_image);
    }

    public function platformImageUrl(): ?string
    {
        return MediaUrl::make($this->platform_image);
    }

    public function joinImageUrl(): ?string
    {
        return MediaUrl::make($this->join_image);
    }
}

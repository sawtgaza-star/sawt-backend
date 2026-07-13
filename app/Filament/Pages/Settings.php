<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

/**
 * صفحة إعدادات واحدة (بدل CRUD تقليدي) — كل إعدادات المنصة مجمّعة بتابات حسب
 * عمود "group" بجدول settings، ومفاتيحها مطابقة تماماً لما يزرعه SettingSeeder
 * عشان أول ما تشغّل db:seed القيم تظهر جاهزة بالفورم.
 *
 * القيم بتتخزن/تتقرأ عبر App\Models\Setting::get() / ::set() فيهم طبقة الكاش الجاهزة.
 */
class Settings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.settings';

    public ?array $data = [];

    public static function getNavigationGroup(): ?string
    {
        return __('Administration');
    }

    public static function getNavigationLabel(): string
    {
        return __('Settings');
    }

    public function getTitle(): string | \Illuminate\Contracts\Support\Htmlable
    {
        return __('Platform Settings');
    }

    /**
     * key => [group, type, default]
     */
    protected function fieldMeta(): array
    {
        return [
            // عام
            'site_name' => ['general', 'string', 'منصة صوت'],
            'site_description' => ['general', 'text', ''],
            'default_locale' => ['general', 'string', 'ar'],

            // payment
            'platform_fee_pct' => ['payment', 'number', 5],
            'min_donation_amount' => ['payment', 'number', 5],
            'bank_name' => ['payment', 'string', 'Bank of Palestine'],
            'bank_account_owner' => ['payment', 'string', 'مؤسسة صوت للإعلام'],
            'bank_account_number' => ['payment', 'string', ''],
            'bank_iban' => ['payment', 'string', ''],
            'paypal_mode' => ['payment', 'string', 'sandbox'],

            // finance (fund split)
            'fund_split_creators_pct' => ['finance', 'number', 40],
            'fund_split_media_pct' => ['finance', 'number', 35],
            'fund_split_support_pct' => ['finance', 'number', 25],

            // contact
            'contact_phone' => ['contact', 'string', ''],
            'contact_email' => ['contact', 'string', ''],
            'support_whatsapp' => ['contact', 'string', ''],

            // social
            'facebook_url' => ['social', 'string', ''],
            'instagram_url' => ['social', 'string', ''],
            'twitter_url' => ['social', 'string', ''],
            'linkedin_url' => ['social', 'string', ''],
            'telegram_url' => ['social', 'string', ''],
            'youtube_url' => ['social', 'string', ''],

            // الصفحة الرئيسية (محتوى قابل للتعديل — شعار، أرقام، نصوص ثنائية اللغة، صور)
            'home_logo' => ['home', 'string', ''],
            'home_hero_image' => ['home', 'string', ''],
            'home_hero_slides' => ['home', 'json', [
                ['image' => '', 'title_ar' => 'منصة صوت', 'title_en' => 'Sawt Platform', 'subtitle_ar' => 'نروي قصص غزة بكرامة... ونبني جيلاً جديداً من صناع المحتوى', 'subtitle_en' => "We tell Gaza's stories with dignity and build a new generation of creators"],
                ['image' => '', 'title_ar' => 'منصة صوت', 'title_en' => 'Sawt Platform', 'subtitle_ar' => 'نروي قصص غزة بكرامة... ونبني جيلاً جديداً من صناع المحتوى', 'subtitle_en' => "We tell Gaza's stories with dignity and build a new generation of creators"],
                ['image' => '', 'title_ar' => 'منصة صوت', 'title_en' => 'Sawt Platform', 'subtitle_ar' => 'نروي قصص غزة بكرامة... ونبني جيلاً جديداً من صناع المحتوى', 'subtitle_en' => "We tell Gaza's stories with dignity and build a new generation of creators"],
            ]],
            'home_stat_team' => ['home', 'string', '20+'],
            'home_stat_stories' => ['home', 'string', '100+'],
            'home_stat_views' => ['home', 'string', '+30'],
            'home_stat_videos' => ['home', 'string', '30+'],
            'home_stat_followers' => ['home', 'string', '+10'],
            'home_who_we_are_ar' => ['home', 'string', ''],
            'home_who_we_are_en' => ['home', 'string', ''],
            'home_welcome_lead_ar' => ['home', 'string', ''],
            'home_welcome_lead_en' => ['home', 'string', ''],
            'home_welcome_title_ar' => ['home', 'string', ''],
            'home_welcome_title_en' => ['home', 'string', ''],
            'home_welcome_desc_ar' => ['home', 'text', ''],
            'home_welcome_desc_en' => ['home', 'text', ''],

            // صفحة من نحن
            'about_header_bg' => ['about', 'string', ''],
            'about_intro_image' => ['about', 'string', ''],
            'about_platform_image' => ['about', 'string', ''],
            'about_join_bg' => ['about', 'string', ''],
            'about_hero_title_ar' => ['about', 'string', 'صناع الأثر.. الفريق خلف منصة صوت'],
            'about_hero_title_en' => ['about', 'string', 'Impact Makers.. The Team Behind Sawt Platform'],
            'about_hero_desc_ar' => ['about', 'text', 'في هذه الصفحة، نشارككم قصة فريق من الناس إلى الناس، رؤيتنا، رسالتنا، وكيف بدأنا لنكون صوتًا حيًّا ومعينًا لمن لا صوت لهم، وكيف منحنا الناس الأمل.'],
            'about_hero_desc_en' => ['about', 'text', 'On this page, we share with you the story of a team from the people to the people — our vision, our message, and how we began to be a living, supportive voice for the voiceless, and how we gave people hope.'],
            'about_header_ar' => ['about', 'string', 'من نحن'],
            'about_header_en' => ['about', 'string', 'About Sawt'],
            'about_intro_ar' => ['about', 'text', 'فريق منصة صوت حاضنة لأصوات غزة. لم نبدأ من فكرةٍ خارقة أو خطةٍ مُحكمة، بل من قرارٍ بسيط: أن نكون حاضرِين، نستمع، ونُعلِن صوت غزة للعالم. نحن من الناس ونعيش معاناتهم عن قرب، فرأينا أن الحاجة واضحة فقررنا ألا نصمت. نعمل بدون أن نتكلم نيابةً عن أحد، وبدون وعودٍ تفوق قدراتنا. وظيفتنا توصيل صوت أهل غزة وإيصاله إلى العالم، مع الحفاظ على كرامة الناس وصوتهم. هدفنا أن نكون جسرًا صادقًا بين من يقدّم الانتباه والدعم لصوت غزة ومن ينتظر من يسمعهم حقًا. اليوم نحن أكثر من فكرة آمن بها شخص واحد؛ نحن حاضنة لأصوات غزة، فريق عمل متكامل.'],
            'about_intro_en' => ['about', 'text', 'The Sawt platform team is an incubator for the voices of Gaza. We did not start from an extraordinary idea or a tight plan, but from a simple decision: to be present, to listen, and to announce the voice of Gaza to the world.'],
            'about_platform_question_ar' => ['about', 'string', 'ما الذي يدفعنا لنكون صوتك؟'],
            'about_platform_question_en' => ['about', 'string', 'What drives us to be your voice?'],
            'about_platform_desc_ar' => ['about', 'text', 'نؤمن أن لكل إنسان قصة تستحق أن تُروى، لذلك جاءت صوت لتكون مساحة حرة للتعبير، حيث يلتقي الأفراد لمشاركة تجاربهم وأفكارهم بصدق. نساعدك على إيصال صوتك إلى الآخرين، ونمنح المحتوى الإنساني مساحة حقيقية ليُرى، ويُسمع، ويترك أثرًا.'],
            'about_platform_desc_en' => ['about', 'text', 'We believe every person has a story worth telling. That\'s why Sawt was created as a free space for expression, where individuals come together to share their experiences and ideas with sincerity.'],
            'about_core_values_subtitle_ar' => ['about', 'text', 'قيمنا هي الأساس الذي نبني عليه صوت، وهي ما يقود طريقة عملنا وتطويرنا المستمر'],
            'about_core_values_subtitle_en' => ['about', 'text', 'Our values are the foundation on which we build Sawt, and they guide the way we work and continuously improve.'],
            'about_core_values' => ['about', 'json', [
                ['title_ar' => 'التمكين', 'title_en' => 'Empowerment', 'desc_ar' => 'نسعى لأن نكون منبرًا يُمكّن الإنسان، ويصنع تأثيرًا إيجابيًا حقيقيًا يساهم في إيصال صوتنا وصوت المجتمع إلى العالم.', 'desc_en' => 'We strive to be a platform that empowers people and creates a real positive impact, helping carry our voice and our community\'s voice to the world.'],
                ['title_ar' => 'الموثوقية', 'title_en' => 'Reliability', 'desc_ar' => 'نحرص على صون الأمانة الإعلامية وحماية الرواية وتوثيقها، معتمدين على معايير أخلاقية راسخة في كل ما ننشر.', 'desc_en' => 'We are committed to preserving media integrity, protecting and documenting the narrative, relying on firm ethical standards in everything we publish.'],
                ['title_ar' => 'الشراكة', 'title_en' => 'Partnership', 'desc_ar' => 'نؤمن أن قوة "صوت غزة" نبع من تلاحم المجتمع، ونعمل كجسر يصل بين الأصوات المختلفة لتعزيز الدعم المتبادل.', 'desc_en' => 'We believe the strength of "Sawt Gaza" springs from the cohesion of the community, and we act as a bridge connecting different voices to foster mutual support.'],
                ['title_ar' => 'الابتكار', 'title_en' => 'Innovation', 'desc_ar' => 'نلتزم بنقل الأخبار والقصص بمهنية عالية وأمانة صحفية، لنكون المصدر الموثوق الذي يعبّر عن الواقع بدقة.', 'desc_en' => 'We commit to delivering news and stories with high professionalism and journalistic integrity, to be the trusted source that reflects reality accurately.'],
            ]],
            'about_story_subtitle_ar' => ['about', 'text', 'قيمنا هي الأساس الذي نبني عليه صوت، وهي ما يقود طريقة عملنا وتطويرنا المستمر'],
            'about_story_subtitle_en' => ['about', 'text', 'Our values are the foundation on which we build Sawt, and they guide the way we work and continuously improve.'],
            'about_story_cards' => ['about', 'json', [
                ['title_ar' => 'التأثير', 'title_en' => 'Impact', 'desc_ar' => 'منذ انطلاقنا، استطعنا إيصال أصوات الآلاف من الأشخاص الذين كانوا صامتين، وكشفنا حقائق عديدة تم إخفاؤها عن الرأي العام. قصصنا وصلت لملايين المتابعين، وساهمت في لفت انتباه العالم إلى قضايا مهمشة.', 'desc_en' => 'Since we began, we have managed to carry the voices of thousands of people who were silenced, and uncovered many facts that were hidden from public opinion.'],
                ['title_ar' => 'ما نقدم', 'title_en' => 'What We Offer', 'desc_ar' => 'نحن نقدم إعلامًا حقيقيًا يعتمد على القصص الحقيقية والأصوات الصادقة، بعيدًا عن ضغوط الإعلام التقليدي والسرديات الرسمية. منصاتنا تتيح لأي إنسان أن يُسمع صوته ويُسرد قصته بحرية ومصداقية، حيث نوثّق القصص الحقيقية من المناطق المتضررة.', 'desc_en' => 'We provide genuine media built on real stories and honest voices, away from the pressures of traditional media and official narratives.'],
                ['title_ar' => 'رحلتنا', 'title_en' => 'Our Journey', 'desc_ar' => 'بدأت رحلة "صوت" في ظل ظروف صعبة، حيث كانت الكثير من القصص الحقيقية مخفية والأصوات الصادقة مكتومة تحت ضغوط الإعلام التقليدي والسرديات الرسمية. آمنّا بأن الحقيقة تستحق أن تُروى، وكل إنسان يستحق أن يُسمع صوته.', 'desc_en' => 'The journey of "Sawt" began under difficult circumstances, when many real stories were hidden and honest voices were muted under the pressures of traditional media and official narratives.'],
            ]],
            'about_join_title_ar' => ['about', 'string', 'قد تكون قصتك بداية التغيير'],
            'about_join_title_en' => ['about', 'string', 'Your story could be the start of change'],
            'about_join_desc_ar' => ['about', 'text', 'كل صوت مهم، إذا كانت لديك قصة تستحق أن تُسمع فإن صوت ستدعمك من أول محادثة إلى التأثير العام.'],
            'about_join_desc_en' => ['about', 'text', 'Every voice matters. If you have a story worth hearing, Sawt will support you from the first conversation to public impact.'],

            // stats (تظهر بصفحة صنّاع المحتوى بالموقع العام)
            'reach_count' => ['stats', 'number', 4000000],
            'supporters_count' => ['stats', 'number', 250000],
            'collaborations_count' => ['stats', 'number', 500],
            'active_creators_count' => ['stats', 'number', 45],

            // reels (إنستغرام)
            'reels_enabled' => ['reels', 'boolean', false],
            'instagram_user_id' => ['reels', 'string', ''],
            'instagram_access_token' => ['reels', 'string', ''],
            'instagram_cache_ttl' => ['reels', 'number', 300],

            // paypal
            'paypal_client_id' => ['paypal', 'string', ''],
            'paypal_secret' => ['paypal', 'string', ''],
            'paypal_webhook_id' => ['paypal', 'string', ''],
        ];
    }

    /**
     * يجيب الريلز الحية من إنستغرام للعرض داخل صفحة الإعدادات.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getReels(): array
    {
        if (! Setting::get('reels_enabled', false)) {
            return [];
        }

        return app(\App\Services\InstagramService::class)->reels(12);
    }

    /**
     * زر "تحديث الآن" — يتخطى الكاش ويجيب أحدث الريلز مباشرة.
     */
    public function refreshReels(): void
    {
        app(\App\Services\InstagramService::class)->reels(12, bypassCache: true);

        Notification::make()->title('تم تحديث الريلز')->success()->send();
    }

    public function mount(): void
    {
        $values = [];

        foreach ($this->fieldMeta() as $key => [$group, $type, $default]) {
            $values[$key] = Setting::get($key, $default);
        }

        $this->form->fill($values);
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Tabs::make('Settings')->columnSpanFull()->tabs([

                Forms\Components\Tabs\Tab::make('الصفحة الرئيسية')->icon('heroicon-o-home')->schema([
                    Forms\Components\Section::make('الشعار والصور')->schema([
                        Forms\Components\FileUpload::make('home_logo')
                            ->label('شعار المنصة (الهيدر)')
                            ->image()->disk('public')->directory('home')->imageEditor()
                            ->helperText('اتركه فارغاً لاستخدام الشعار الافتراضي'),
                        Forms\Components\FileUpload::make('home_hero_image')
                            ->label('صورة القسم الرئيسي (Hero)')
                            ->image()->disk('public')->directory('home')->imageEditor()
                            ->helperText('الصورة الكبيرة بجانب "من نحن"'),
                    ])->columns(2),

                    Forms\Components\Section::make('شرائح الكاروسيل الرئيسي (Hero) — أضف/احذف/رتّب')->schema([
                        Forms\Components\Repeater::make('home_hero_slides')
                            ->label('')
                            ->schema([
                                Forms\Components\FileUpload::make('image')
                                    ->label('صورة الخلفية')
                                    ->image()->disk('public')->directory('home/hero')->imageEditor()
                                    ->columnSpanFull(),
                                Forms\Components\TextInput::make('title_ar')->label('العنوان (عربي)'),
                                Forms\Components\TextInput::make('title_en')->label('Title (English)'),
                                Forms\Components\Textarea::make('subtitle_ar')->label('الوصف (عربي)')->rows(2),
                                Forms\Components\Textarea::make('subtitle_en')->label('Subtitle (English)')->rows(2),
                            ])
                            ->columns(2)
                            ->reorderable()
                            ->collapsible()
                            ->cloneable()
                            ->itemLabel(fn (array $state): ?string => $state['title_ar'] ?? 'شريحة')
                            ->addActionLabel('➕ إضافة شريحة جديدة'),
                    ]),

                    Forms\Components\Section::make('الأرقام (تظهر بنفس القيمة في اللغتين)')->schema([
                        Forms\Components\TextInput::make('home_stat_team')->label('أعضاء الفريق'),
                        Forms\Components\TextInput::make('home_stat_stories')->label('عدد القصص'),
                        Forms\Components\TextInput::make('home_stat_views')->label('المشاهدات'),
                        Forms\Components\TextInput::make('home_stat_videos')->label('عدد الفيديوهات'),
                        Forms\Components\TextInput::make('home_stat_followers')->label('المتابعون'),
                    ])->columns(3),

                    Forms\Components\Section::make('النصوص — عربي / إنجليزي')->schema([
                        Forms\Components\TextInput::make('home_who_we_are_ar')->label('عنوان "من نحن" (عربي)')->placeholder('من نحن'),
                        Forms\Components\TextInput::make('home_who_we_are_en')->label('"Who We Are" (English)')->placeholder('Who We Are'),
                        Forms\Components\TextInput::make('home_welcome_lead_ar')->label('السطر التمهيدي (عربي)')->placeholder('في صوت، كل فكرة بتلاقي مكانها!'),
                        Forms\Components\TextInput::make('home_welcome_lead_en')->label('Lead line (English)'),
                        Forms\Components\TextInput::make('home_welcome_title_ar')->label('العنوان الرئيسي (عربي)')->placeholder('كل فكرة إلها صوت...'),
                        Forms\Components\TextInput::make('home_welcome_title_en')->label('Main title (English)'),
                        Forms\Components\Textarea::make('home_welcome_desc_ar')->label('الوصف (عربي)')->rows(4)->columnSpanFull(),
                        Forms\Components\Textarea::make('home_welcome_desc_en')->label('Description (English)')->rows(4)->columnSpanFull(),
                    ])->columns(2),
                ]),

                Forms\Components\Tabs\Tab::make('من نحن')->icon('heroicon-o-user-group')->schema([
                    Forms\Components\Section::make('الصور')->schema([
                        Forms\Components\FileUpload::make('about_header_bg')
                            ->label('خلفية الهيرو')
                            ->image()->disk('public')->directory('about')->imageEditor()
                            ->helperText('اتركه فارغاً لاستخدام الصورة الافتراضية'),
                        Forms\Components\FileUpload::make('about_intro_image')
                            ->label('صورة قسم المقدمة')
                            ->image()->disk('public')->directory('about')->imageEditor(),
                        Forms\Components\FileUpload::make('about_platform_image')
                            ->label('صورة قسم المنصة')
                            ->image()->disk('public')->directory('about')->imageEditor(),
                        Forms\Components\FileUpload::make('about_join_bg')
                            ->label('خلفية بانر الانضمام')
                            ->image()->disk('public')->directory('about')->imageEditor(),
                    ])->columns(2),

                    Forms\Components\Section::make('الهيرو والمقدمة')->schema([
                        Forms\Components\TextInput::make('about_hero_title_ar')->label('عنوان الهيرو (عربي)'),
                        Forms\Components\TextInput::make('about_hero_title_en')->label('Hero title (English)'),
                        Forms\Components\Textarea::make('about_hero_desc_ar')->label('وصف الهيرو (عربي)')->rows(3),
                        Forms\Components\Textarea::make('about_hero_desc_en')->label('Hero description (English)')->rows(3),
                        Forms\Components\TextInput::make('about_header_ar')->label('عنوان القسم (عربي)'),
                        Forms\Components\TextInput::make('about_header_en')->label('Section title (English)'),
                        Forms\Components\Textarea::make('about_intro_ar')->label('نص المقدمة (عربي)')->rows(5)->columnSpanFull(),
                        Forms\Components\Textarea::make('about_intro_en')->label('Intro text (English)')->rows(5)->columnSpanFull(),
                    ])->columns(2),

                    Forms\Components\Section::make('نبذة عن المنصة')->schema([
                        Forms\Components\TextInput::make('about_platform_question_ar')->label('السؤال (عربي)'),
                        Forms\Components\TextInput::make('about_platform_question_en')->label('Question (English)'),
                        Forms\Components\Textarea::make('about_platform_desc_ar')->label('الوصف (عربي)')->rows(4),
                        Forms\Components\Textarea::make('about_platform_desc_en')->label('Description (English)')->rows(4),
                    ])->columns(2),

                    Forms\Components\Section::make('القيم الأساسية')->schema([
                        Forms\Components\Textarea::make('about_core_values_subtitle_ar')->label('العنوان الفرعي (عربي)')->rows(2),
                        Forms\Components\Textarea::make('about_core_values_subtitle_en')->label('Subtitle (English)')->rows(2),
                        Forms\Components\Repeater::make('about_core_values')
                            ->label('القيم')
                            ->schema([
                                Forms\Components\TextInput::make('title_ar')->label('العنوان (عربي)')->required(),
                                Forms\Components\TextInput::make('title_en')->label('Title (English)'),
                                Forms\Components\Textarea::make('desc_ar')->label('الوصف (عربي)')->rows(3),
                                Forms\Components\Textarea::make('desc_en')->label('Description (English)')->rows(3),
                            ])
                            ->columns(2)
                            ->reorderable()
                            ->collapsible()
                            ->cloneable()
                            ->itemLabel(fn (array $state): ?string => $state['title_ar'] ?? 'قيمة')
                            ->addActionLabel('➕ إضافة قيمة')
                            ->columnSpanFull(),
                    ])->columns(2),

                    Forms\Components\Section::make('قصة صوت')->schema([
                        Forms\Components\Textarea::make('about_story_subtitle_ar')->label('العنوان الفرعي (عربي)')->rows(2),
                        Forms\Components\Textarea::make('about_story_subtitle_en')->label('Subtitle (English)')->rows(2),
                        Forms\Components\Repeater::make('about_story_cards')
                            ->label('بطاقات القصة')
                            ->schema([
                                Forms\Components\TextInput::make('title_ar')->label('العنوان (عربي)')->required(),
                                Forms\Components\TextInput::make('title_en')->label('Title (English)'),
                                Forms\Components\Textarea::make('desc_ar')->label('الوصف (عربي)')->rows(3),
                                Forms\Components\Textarea::make('desc_en')->label('Description (English)')->rows(3),
                            ])
                            ->columns(2)
                            ->reorderable()
                            ->collapsible()
                            ->cloneable()
                            ->itemLabel(fn (array $state): ?string => $state['title_ar'] ?? 'بطاقة')
                            ->addActionLabel('➕ إضافة بطاقة')
                            ->columnSpanFull(),
                    ])->columns(2),

                    Forms\Components\Section::make('بانر الانضمام')->schema([
                        Forms\Components\TextInput::make('about_join_title_ar')->label('العنوان (عربي)'),
                        Forms\Components\TextInput::make('about_join_title_en')->label('Title (English)'),
                        Forms\Components\Textarea::make('about_join_desc_ar')->label('الوصف (عربي)')->rows(3),
                        Forms\Components\Textarea::make('about_join_desc_en')->label('Description (English)')->rows(3),
                    ])->columns(2),
                ]),

                Forms\Components\Tabs\Tab::make('عام')->icon('heroicon-o-globe-alt')->schema([
                    Forms\Components\TextInput::make('site_name')->label('اسم المنصة')->required(),
                    Forms\Components\Textarea::make('site_description')->label('وصف المنصة')->rows(3),
                    Forms\Components\Select::make('default_locale')
                        ->label('اللغة الافتراضية')
                        ->options(['ar' => 'العربية', 'en' => 'English'])
                        ->required(),
                ]),

                Forms\Components\Tabs\Tab::make('الدفع')->icon('heroicon-o-credit-card')->schema([
                    Forms\Components\TextInput::make('platform_fee_pct')
                        ->label('نسبة عمولة المنصة (%)')->numeric()->minValue(0)->maxValue(100)->suffix('%')->required(),
                    Forms\Components\TextInput::make('min_donation_amount')
                        ->label('الحد الأدنى للتبرع')->numeric()->prefix('$'),
                    Forms\Components\TextInput::make('bank_name')->label('اسم البنك'),
                    Forms\Components\TextInput::make('bank_account_owner')->label('اسم صاحب الحساب'),
                    Forms\Components\TextInput::make('bank_account_number')->label('رقم الحساب'),
                    Forms\Components\TextInput::make('bank_iban')->label('IBAN'),
                    Forms\Components\Select::make('paypal_mode')
                        ->label('وضع PayPal')
                        ->options(['sandbox' => 'تجريبي (Sandbox)', 'live' => 'مباشر (Live)']),
                ])->columns(2),

                Forms\Components\Tabs\Tab::make('PayPal')->icon('heroicon-o-banknotes')->schema([
                    Forms\Components\Placeholder::make('paypal_hint')
                        ->label('')
                        ->content('أدخل بيانات تطبيق PayPal (REST App). الوضع (تجريبي/مباشر) يُضبط من تبويب «الدفع». الـ Webhook ID اختياري لكنه يزيد الموثوقية.')
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('paypal_client_id')
                        ->label('Client ID')
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('paypal_secret')
                        ->label('Secret')
                        ->password()->revealable()
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('paypal_webhook_id')
                        ->label('Webhook ID (اختياري)')
                        ->helperText('من لوحة PayPal → Webhooks — للتحقق من صحة الإشعارات')
                        ->columnSpanFull(),
                ]),

                Forms\Components\Tabs\Tab::make('توزيع الأموال')->icon('heroicon-o-chart-pie')->schema([
                    Forms\Components\TextInput::make('fund_split_creators_pct')->label('نسبة صنّاع المحتوى (%)')->numeric()->suffix('%')->required(),
                    Forms\Components\TextInput::make('fund_split_media_pct')->label('نسبة الإنتاج الإعلامي (%)')->numeric()->suffix('%')->required(),
                    Forms\Components\TextInput::make('fund_split_support_pct')->label('نسبة الدعم التشغيلي (%)')->numeric()->suffix('%')->required(),
                ])->columns(3),

                Forms\Components\Tabs\Tab::make('التواصل')->icon('heroicon-o-envelope')->schema([
                    Forms\Components\TextInput::make('contact_email')->label('بريد التواصل')->email(),
                    Forms\Components\TextInput::make('contact_phone')->label('هاتف التواصل'),
                    Forms\Components\TextInput::make('support_whatsapp')->label('واتساب الدعم'),
                ])->columns(2),

                Forms\Components\Tabs\Tab::make('التواصل الاجتماعي')->icon('heroicon-o-share')->schema([
                    Forms\Components\TextInput::make('facebook_url')->label('Facebook')->url(),
                    Forms\Components\TextInput::make('instagram_url')->label('Instagram')->url(),
                    Forms\Components\TextInput::make('twitter_url')->label('X / Twitter')->url(),
                    Forms\Components\TextInput::make('linkedin_url')->label('LinkedIn')->url(),
                    Forms\Components\TextInput::make('telegram_url')->label('Telegram')->url(),
                    Forms\Components\TextInput::make('youtube_url')->label('YouTube')->url(),
                ])->columns(2),

                Forms\Components\Tabs\Tab::make('ريلز إنستغرام')->icon('heroicon-o-film')->schema([
                    Forms\Components\Toggle::make('reels_enabled')
                        ->label('تفعيل عرض الريلز')
                        ->helperText('لما يكون مفعّل، بيتم جلب الريلز من حساب إنستغرام وعرضها بالأسفل وعبر /api/reels')
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('instagram_user_id')
                        ->label('معرّف حساب إنستغرام (Business ID)')
                        ->helperText('رقم حساب Instagram Business — بتجيبه من Graph API'),
                    Forms\Components\TextInput::make('instagram_cache_ttl')
                        ->label('مدة الكاش (ثانية)')
                        ->numeric()->minValue(0)
                        ->helperText('0 = جلب مباشر كل مرة (احذر حد الطلبات). المقترح 300'),
                    Forms\Components\Textarea::make('instagram_access_token')
                        ->label('رمز الوصول (Access Token)')
                        ->rows(3)
                        ->helperText('Long-lived token من تطبيق Meta — يُخزَّن بأمان بجدول الإعدادات')
                        ->columnSpanFull(),
                ])->columns(2),

                Forms\Components\Tabs\Tab::make('إحصائيات الواجهة')->icon('heroicon-o-presentation-chart-line')->schema([
                    Forms\Components\TextInput::make('reach_count')->label('عدد الوصول')->numeric(),
                    Forms\Components\TextInput::make('supporters_count')->label('عدد الداعمين')->numeric(),
                    Forms\Components\TextInput::make('collaborations_count')->label('عدد التعاونات')->numeric(),
                    Forms\Components\TextInput::make('active_creators_count')->label('عدد صنّاع المحتوى النشطين')->numeric(),
                ])->columns(2),
            ]),
        ])->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();

        foreach ($this->fieldMeta() as $key => [$group, $type, $default]) {
            Setting::set($key, $state[$key] ?? $default, group: $group, type: $type);
        }

        Notification::make()
            ->title('تم حفظ الإعدادات بنجاح')
            ->success()
            ->send();
    }
}

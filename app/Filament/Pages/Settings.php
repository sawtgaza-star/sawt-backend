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

            // الهيدر (تعديل العناوين والترتيب والإظهار فقط — الروابط ثابتة حسب الصفحة)
            'header_nav_links' => ['header', 'json', [
                ['key' => 'home', 'label_ar' => 'الرئيسية', 'label_en' => 'Home', 'is_visible' => true],
                ['key' => 'about', 'label_ar' => 'من نحن', 'label_en' => 'About Us', 'is_visible' => true],
                ['key' => 'content', 'label_ar' => 'محتوانا', 'label_en' => 'Our Content', 'is_visible' => true],
                ['key' => 'courses', 'label_ar' => 'الكورسات', 'label_en' => 'Courses', 'is_visible' => true],
                ['key' => 'team', 'label_ar' => 'الفريق', 'label_en' => 'Team', 'is_visible' => true],
                ['key' => 'creators', 'label_ar' => 'صناع المحتوى', 'label_en' => 'Content Creators', 'is_visible' => true],
                ['key' => 'support', 'label_ar' => 'ادعم صوت', 'label_en' => 'Support Sawt', 'is_visible' => true],
                ['key' => 'incubator', 'label_ar' => 'حاضنة صوت', 'label_en' => 'Sawt Incubator', 'is_visible' => true],
                ['key' => 'media', 'label_ar' => 'صوت ميديا', 'label_en' => 'Sawt Media', 'is_visible' => true],
            ]],

            // الفوتر
            'footer_logo' => ['footer', 'string', ''],
            'footer_about_ar' => ['footer', 'text', 'منصة صوت، تأسست لتكون مساحة للمبدعين، تجمع الحاضنة، صوت ميديا، والصوت نفسه، لتقديم محتوى ملهم وتجارب فريدة لكل من يسعى لصوته أن يُسمع.'],
            'footer_about_en' => ['footer', 'text', 'Sawt platform was founded as a space for creators — bringing together the incubator, Sawt Media, and Sawt itself to deliver inspiring content and unique experiences.'],
            'footer_main_title_ar' => ['footer', 'string', 'الأقسام الرئيسية'],
            'footer_main_title_en' => ['footer', 'string', 'Main Sections'],
            'footer_main_links' => ['footer', 'json', [
                ['key' => 'home', 'label_ar' => 'الرئيسية', 'label_en' => 'Home', 'is_visible' => true],
                ['key' => 'about', 'label_ar' => 'من نحن', 'label_en' => 'About Us', 'is_visible' => true],
                ['key' => 'content', 'label_ar' => 'محتوانا', 'label_en' => 'Our Content', 'is_visible' => true],
                ['key' => 'team', 'label_ar' => 'الفريق', 'label_en' => 'Team', 'is_visible' => true],
                ['key' => 'creators', 'label_ar' => 'صناع المحتوى', 'label_en' => 'Content Creators', 'is_visible' => true],
                ['key' => 'incubator', 'label_ar' => 'حاضنة صوت', 'label_en' => 'Sawt Incubator', 'is_visible' => true],
                ['key' => 'media', 'label_ar' => 'صوت ميديا', 'label_en' => 'Sawt Media', 'is_visible' => true],
            ]],
            'footer_quick_title_ar' => ['footer', 'string', 'روابط سريعة'],
            'footer_quick_title_en' => ['footer', 'string', 'Quick Links'],
            'footer_quick_links' => ['footer', 'json', [
                ['key' => 'backstage', 'label_ar' => 'الكواليس', 'label_en' => 'Behind the Scenes', 'url' => '#', 'is_visible' => true],
                ['key' => 'media_kit', 'label_ar' => 'MEDIA KIT', 'label_en' => 'MEDIA KIT', 'url' => '#', 'is_visible' => true],
                ['key' => 'blog', 'label_ar' => 'المدونة', 'label_en' => 'Blog', 'url' => '#', 'is_visible' => true],
                ['key' => 'faq', 'label_ar' => 'الأسئلة الشائعة', 'label_en' => 'FAQs', 'url' => '#', 'is_visible' => true],
            ]],
            'footer_newsletter_title_ar' => ['footer', 'string', 'ابقَ على اطلاع'],
            'footer_newsletter_title_en' => ['footer', 'string', 'Stay Updated'],
            'footer_newsletter_desc_ar' => ['footer', 'string', 'اشترك في نشرتنا الإخبارية ..'],
            'footer_newsletter_desc_en' => ['footer', 'string', 'Subscribe to our newsletter..'],
            'footer_copyright_ar' => ['footer', 'string', '© جميع الحقوق محفوظة. 2026'],
            'footer_copyright_en' => ['footer', 'string', '© All rights reserved. 2026'],
            'footer_brand' => ['footer', 'string', 'SAWTGAZA'],

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
            'about_core_values_title_ar' => ['about', 'string', 'أهم القيم التي نركز عليها'],
            'about_core_values_title_en' => ['about', 'string', 'The values we focus on'],
            'about_core_values_subtitle_ar' => ['about', 'text', 'قيمنا هي الأساس الذي نبني عليه صوت، وهي ما يقود طريقة عملنا وتطويرنا المستمر'],
            'about_core_values_subtitle_en' => ['about', 'text', 'Our values are the foundation on which we build Sawt, and they guide the way we work and continuously improve.'],
            'about_core_values' => ['about', 'json', [
                ['icon' => null, 'title_ar' => 'المصداقية', 'title_en' => 'Credibility', 'desc_ar' => 'ننقل القصص والحقائق بدقة وموضوعية، ملتزمين بالتحقق من المعلومات واحترام ثقة جمهورنا.', 'desc_en' => 'We convey stories and facts accurately and objectively, committed to verifying information and respecting our audience\'s trust.'],
                ['icon' => null, 'title_ar' => 'الإنسانية', 'title_en' => 'Humanity', 'desc_ar' => 'نضع الإنسان في قلب كل قصة، ونؤمن بأن لكل فرد حقاً في أن يُسمع ويُمثل بكرامة واحترام.', 'desc_en' => 'We put the human at the heart of every story, and believe every individual has a right to be heard with dignity and respect.'],
                ['icon' => null, 'title_ar' => 'التأثير', 'title_en' => 'Impact', 'desc_ar' => 'نسعى لصناعة محتوى يرفع الوعي، ويحدث أثراً إيجابياً في المجتمع، ويحفز التغيير نحو الأفضل.', 'desc_en' => 'We strive to create content that raises awareness, creates positive impact, and stimulates change for the better.'],
                ['icon' => null, 'title_ar' => 'الاستقلالية', 'title_en' => 'Independence', 'desc_ar' => 'نلتزم بإعلام مستقل يعكس الواقع بصدق، بعيداً عن أي تحيزات أو أجندات تؤثر على رسالتنا.', 'desc_en' => 'We are committed to independent media that reflects reality honestly, away from biases or agendas.'],
            ]],
            'about_story_title_ar' => ['about', 'string', 'قصة صوت'],
            'about_story_title_en' => ['about', 'string', 'Sawt Story'],
            'about_story_subtitle_ar' => ['about', 'text', 'من فكرة بسيطة إلى منصة تحمل صوت الناس وتصل لقلوبهم.'],
            'about_story_subtitle_en' => ['about', 'text', 'From a simple idea to a platform that carries people\'s voices.'],
            'about_story_cards' => ['about', 'json', [
                ['icon' => null, 'title_ar' => 'رحلتنا', 'title_en' => 'Our Journey', 'desc_ar' => 'بدأت رحلة «صوت» في ظل ظروف صعبة حيث كانت الكثير من القصص الحقيقية مخفية والأصوات الصادقة مكبوتة تحت ضغوط الإعلام التقليدي.', 'desc_en' => 'The journey of Sawt began under difficult circumstances, when many real stories were hidden and honest voices muted.'],
                ['icon' => null, 'title_ar' => 'ما نقدم', 'title_en' => 'What We Offer', 'desc_ar' => 'نحن نقدم إعلاماً حقيقياً يعتمد على القصص الحقيقية والأصوات الصادقة بعيداً عن ضغوط الإعلام التقليدي والسرديات الرسمية.', 'desc_en' => 'We provide genuine media built on real stories and honest voices, away from traditional media pressures.'],
                ['icon' => null, 'title_ar' => 'التأثير', 'title_en' => 'Impact', 'desc_ar' => 'منذ انطلاقنا، استطعنا إيصال أصوات آلاف من الأشخاص الذين كانوا صامتين، وكشفنا حقائق عديدة لم يتناولها الرأي العام.', 'desc_en' => 'Since launch, we have amplified thousands of silent voices and uncovered facts missed by public opinion.'],
            ]],
            'about_join_title_ar' => ['about', 'string', 'لأن بعض الأصوات لا يجب أن تُنسى'],
            'about_join_title_en' => ['about', 'string', 'Because some voices should not be forgotten'],
            'about_join_desc_ar' => ['about', 'text', 'مساهمتك ليست دعماً لمنصة إعلامية فحسب، بل دعماً لأصوات وقصص تنتظر من ينقلها'],
            'about_join_desc_en' => ['about', 'text', 'Your contribution is not just support for a media platform, but support for voices and stories waiting to be told.'],
            'about_join_button_text_ar' => ['about', 'string', 'مساهمة بإيصال صوت'],
            'about_join_button_text_en' => ['about', 'string', 'Help amplify a voice'],
            'about_join_button_url' => ['about', 'string', '/donate'],

            // صفحة الفريق
            'team_header_bg' => ['team', 'string', ''],
            'team_hero_title_ar' => ['team', 'string', 'صناع الأثر.. الفريق خلف منصة صوت'],
            'team_hero_title_en' => ['team', 'string', 'Impact Makers.. The Team Behind Sawt'],
            'team_hero_desc_ar' => ['team', 'text', ''],
            'team_hero_desc_en' => ['team', 'text', ''],
            'team_all_label_ar' => ['team', 'string', 'الكل'],
            'team_all_label_en' => ['team', 'string', 'All'],
            'team_bio_label_ar' => ['team', 'string', 'نبذة عنه'],
            'team_bio_label_en' => ['team', 'string', 'About'],
            'team_experience_suffix_ar' => ['team', 'string', 'سنوات من الخبرة'],
            'team_experience_suffix_en' => ['team', 'string', 'years of experience'],
            'team_follow_label_ar' => ['team', 'string', 'تابعنا على :'],
            'team_follow_label_en' => ['team', 'string', 'Follow us on:'],
            'team_detail_intro_image' => ['team', 'string', ''],
            'team_detail_intro_ar' => ['team', 'text', 'في صوت، كل فرد في الفريق يحمل رؤية مشتركة: أن يكون الصوت الحر مساحة آمنة للتعبير، والإبداع، والتأثير.'],
            'team_detail_intro_en' => ['team', 'text', 'At Sawt, every team member shares one vision: a free, safe space for expression, creativity, and impact.'],
            'team_members_section_title_ar' => ['team', 'string', 'اعضاء الفريق'],
            'team_members_section_title_en' => ['team', 'string', 'Team Members'],
            'team_view_all_label_ar' => ['team', 'string', 'عرض الكل'],
            'team_view_all_label_en' => ['team', 'string', 'View all'],

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

                Forms\Components\Tabs\Tab::make('الهوية و SEO')->icon('heroicon-o-identification')->schema([
                    Forms\Components\Section::make('الهوية البصرية')->schema([
                        Forms\Components\FileUpload::make('home_logo')
                            ->label('شعار الهيدر')
                            ->image()->disk('public')->directory('branding')->imageEditor()
                            ->helperText('يظهر أعلى الموقع — اتركه فارغاً للشعار الافتراضي'),
                        Forms\Components\FileUpload::make('site_favicon')
                            ->label('أيقونة الموقع (Favicon)')
                            ->image()->disk('public')->directory('branding')
                            ->helperText('تظهر بتبويب المتصفح — يُفضّل صورة مربعة 512×512'),
                    ])->columns(2),

                    Forms\Components\Section::make('تحسين محركات البحث (SEO)')->schema([
                        Forms\Components\TextInput::make('meta_title')
                            ->label('عنوان الصفحة (Title)')
                            ->helperText('يظهر بتبويب المتصفح ونتائج جوجل — يُفضّل أقل من 60 حرفاً')
                            ->maxLength(70)->columnSpanFull(),
                        Forms\Components\Textarea::make('meta_description')
                            ->label('وصف الميتا (Description)')
                            ->helperText('يظهر تحت العنوان بنتائج البحث — 150–160 حرفاً مثالي')
                            ->rows(3)->maxLength(200)->columnSpanFull(),
                        Forms\Components\TextInput::make('meta_keywords')
                            ->label('الكلمات المفتاحية')
                            ->helperText('مفصولة بفاصلة')
                            ->columnSpanFull(),
                        Forms\Components\FileUpload::make('og_image')
                            ->label('صورة المشاركة (Open Graph)')
                            ->image()->disk('public')->directory('branding')->imageEditor()
                            ->helperText('تظهر عند مشاركة الرابط على فيسبوك/تويتر/واتساب — 1200×630')
                            ->columnSpanFull(),
                    ]),
                ]),

                Forms\Components\Tabs\Tab::make('الصفحة الرئيسية')->icon('heroicon-o-home')->schema([
                    Forms\Components\Section::make('صورة قسم "من نحن"')->schema([
                        Forms\Components\FileUpload::make('home_hero_image')
                            ->label('الصورة الكبيرة بجانب "من نحن"')
                            ->image()->disk('public')->directory('home')->imageEditor()
                            ->helperText('شعار الهيدر يُعدّل من تبويب «الهوية و SEO»'),
                    ]),

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
                    Forms\Components\Section::make('1) الهيرو')->schema([
                        Forms\Components\FileUpload::make('about_header_bg')
                            ->label('صورة خلفية الهيرو')
                            ->image()->disk('public')->directory('about')->imageEditor()
                            ->helperText('اتركه فارغاً لاستخدام الصورة الافتراضية')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('about_hero_title_ar')->label('العنوان (عربي)'),
                        Forms\Components\TextInput::make('about_hero_title_en')->label('Title (English)'),
                        Forms\Components\Textarea::make('about_hero_desc_ar')->label('الوصف (عربي)')->rows(3),
                        Forms\Components\Textarea::make('about_hero_desc_en')->label('Description (English)')->rows(3),
                    ])->columns(2),

                    Forms\Components\Section::make('2) من نحن (المقدمة)')->schema([
                        Forms\Components\FileUpload::make('about_intro_image')
                            ->label('صورة القسم')
                            ->image()->disk('public')->directory('about')->imageEditor()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('about_header_ar')->label('العنوان (عربي)')->placeholder('من نحن'),
                        Forms\Components\TextInput::make('about_header_en')->label('Title (English)'),
                        Forms\Components\Textarea::make('about_intro_ar')->label('النص (عربي)')->rows(5)->columnSpanFull(),
                        Forms\Components\Textarea::make('about_intro_en')->label('Body (English)')->rows(5)->columnSpanFull(),
                    ])->columns(2),

                    Forms\Components\Section::make('3) القيم')->schema([
                        Forms\Components\TextInput::make('about_core_values_title_ar')->label('عنوان القسم (عربي)'),
                        Forms\Components\TextInput::make('about_core_values_title_en')->label('Section title (English)'),
                        Forms\Components\Textarea::make('about_core_values_subtitle_ar')->label('الوصف الفرعي (عربي)')->rows(2),
                        Forms\Components\Textarea::make('about_core_values_subtitle_en')->label('Subtitle (English)')->rows(2),
                        Forms\Components\Repeater::make('about_core_values')
                            ->label('بطاقات القيم')
                            ->schema([
                                Forms\Components\FileUpload::make('icon')
                                    ->label('الأيقونة')
                                    ->image()->disk('public')->directory('about/values')
                                    ->columnSpanFull(),
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

                    Forms\Components\Section::make('4) ما الذي يدفعنا لنكون صوتك؟')->schema([
                        Forms\Components\FileUpload::make('about_platform_image')
                            ->label('صورة القسم')
                            ->image()->disk('public')->directory('about')->imageEditor()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('about_platform_question_ar')->label('العنوان (عربي)'),
                        Forms\Components\TextInput::make('about_platform_question_en')->label('Title (English)'),
                        Forms\Components\Textarea::make('about_platform_desc_ar')->label('الوصف (عربي)')->rows(4),
                        Forms\Components\Textarea::make('about_platform_desc_en')->label('Description (English)')->rows(4),
                    ])->columns(2),

                    Forms\Components\Section::make('5) قصة صوت')->schema([
                        Forms\Components\TextInput::make('about_story_title_ar')->label('عنوان القسم (عربي)'),
                        Forms\Components\TextInput::make('about_story_title_en')->label('Section title (English)'),
                        Forms\Components\Textarea::make('about_story_subtitle_ar')->label('الوصف الفرعي (عربي)')->rows(2),
                        Forms\Components\Textarea::make('about_story_subtitle_en')->label('Subtitle (English)')->rows(2),
                        Forms\Components\Repeater::make('about_story_cards')
                            ->label('بطاقات القصة')
                            ->schema([
                                Forms\Components\FileUpload::make('icon')
                                    ->label('الأيقونة')
                                    ->image()->disk('public')->directory('about/story')
                                    ->columnSpanFull(),
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

                    Forms\Components\Section::make('6) بانر المساهمة / الانضمام')->schema([
                        Forms\Components\FileUpload::make('about_join_bg')
                            ->label('صورة الخلفية')
                            ->image()->disk('public')->directory('about')->imageEditor()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('about_join_title_ar')->label('العنوان (عربي)'),
                        Forms\Components\TextInput::make('about_join_title_en')->label('Title (English)'),
                        Forms\Components\Textarea::make('about_join_desc_ar')->label('الوصف (عربي)')->rows(3),
                        Forms\Components\Textarea::make('about_join_desc_en')->label('Description (English)')->rows(3),
                        Forms\Components\TextInput::make('about_join_button_text_ar')->label('نص الزر (عربي)'),
                        Forms\Components\TextInput::make('about_join_button_text_en')->label('Button text (English)'),
                        Forms\Components\TextInput::make('about_join_button_url')->label('رابط الزر')->placeholder('/donate')->columnSpanFull(),
                    ])->columns(2),
                ]),

                Forms\Components\Tabs\Tab::make('الفريق')->icon('heroicon-o-users')->schema([
                    Forms\Components\Section::make('1) الهيرو')->schema([
                        Forms\Components\FileUpload::make('team_header_bg')
                            ->label('صورة خلفية الهيرو')
                            ->image()->disk('public')->directory('team')->imageEditor()
                            ->helperText('اتركه فارغاً لاستخدام الصورة الافتراضية')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('team_hero_title_ar')->label('العنوان (عربي)'),
                        Forms\Components\TextInput::make('team_hero_title_en')->label('Title (English)'),
                        Forms\Components\Textarea::make('team_hero_desc_ar')->label('الوصف (عربي)')->rows(3),
                        Forms\Components\Textarea::make('team_hero_desc_en')->label('Description (English)')->rows(3),
                    ])->columns(2),

                    Forms\Components\Section::make('2) فلتر الأقسام')->schema([
                        Forms\Components\TextInput::make('team_all_label_ar')->label('تسمية «الكل» (عربي)')->placeholder('الكل'),
                        Forms\Components\TextInput::make('team_all_label_en')->label('"All" label (English)')->placeholder('All'),
                        Forms\Components\Placeholder::make('team_majors_hint')
                            ->content('إدارة الأقسام (فريق التصميم، التسويق…) من قائمة **الفريق → الأقسام (Majors)** في الشريط الجانبي.')
                            ->columnSpanFull(),
                    ])->columns(2),

                    Forms\Components\Section::make('3) صفحة تفاصيل العضو')->schema([
                        Forms\Components\TextInput::make('team_bio_label_ar')->label('عنوان النبذة (عربي)')->placeholder('نبذة عنه'),
                        Forms\Components\TextInput::make('team_bio_label_en')->label('Bio heading (English)'),
                        Forms\Components\TextInput::make('team_experience_suffix_ar')->label('لاحقة الخبرة (عربي)')->placeholder('سنوات من الخبرة'),
                        Forms\Components\TextInput::make('team_experience_suffix_en')->label('Experience suffix (English)'),
                        Forms\Components\TextInput::make('team_follow_label_ar')->label('عنوان المتابعة (عربي)')->placeholder('تابعنا على :'),
                        Forms\Components\TextInput::make('team_follow_label_en')->label('Follow label (English)'),
                        Forms\Components\FileUpload::make('team_detail_intro_image')
                            ->label('صورة قسم المقدمة أسفل الصفحة')
                            ->image()->disk('public')->directory('team')->imageEditor()
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('team_detail_intro_ar')->label('نص المقدمة (عربي)')->rows(4),
                        Forms\Components\Textarea::make('team_detail_intro_en')->label('Intro text (English)')->rows(4),
                        Forms\Components\TextInput::make('team_members_section_title_ar')->label('عنوان شبكة الأعضاء (عربي)'),
                        Forms\Components\TextInput::make('team_members_section_title_en')->label('Members grid title (English)'),
                        Forms\Components\TextInput::make('team_view_all_label_ar')->label('نص «عرض الكل» (عربي)'),
                        Forms\Components\TextInput::make('team_view_all_label_en')->label('"View all" (English)'),
                        Forms\Components\Placeholder::make('team_members_hint')
                            ->content('روابط التواصل لكل عضو تُعدّل من **الفريق → أعضاء الفريق → تاب صفحة التفاصيل**.')
                            ->columnSpanFull(),
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

                Forms\Components\Tabs\Tab::make('الهيدر')->icon('heroicon-o-bars-3')->schema([
                    Forms\Components\Section::make('الشعار')->schema([
                        Forms\Components\FileUpload::make('home_logo')
                            ->label('شعار الهيدر')
                            ->image()->disk('public')->directory('home')->imageEditor()
                            ->helperText('يظهر في شريط التنقل')
                            ->columnSpanFull(),
                    ]),
                    Forms\Components\Repeater::make('header_nav_links')
                        ->label('عناصر القائمة — اسحب لإعادة الترتيب')
                        ->schema([
                            Forms\Components\Hidden::make('key'),
                            Forms\Components\TextInput::make('label_ar')->label('العنوان (عربي)')->required(),
                            Forms\Components\TextInput::make('label_en')->label('Label (English)'),
                            Forms\Components\Toggle::make('is_visible')->label('ظاهر')->default(true),
                        ])
                        ->columns(2)
                        ->reorderable()
                        ->collapsible()
                        ->deletable(false)
                        ->addable(false)
                        ->itemLabel(fn (array $state): ?string => $state['label_ar'] ?? 'عنصر')
                        ->columnSpanFull(),
                    Forms\Components\Placeholder::make('header_social_hint')
                        ->label('')
                        ->content('روابط السوشيال ميديا للهيدر/الفوتر تُعدّل من تبويب «التواصل الاجتماعي».')
                        ->columnSpanFull(),
                ]),

                Forms\Components\Tabs\Tab::make('الفوتر')->icon('heroicon-o-rectangle-group')->schema([
                    Forms\Components\Section::make('الشعار والنبذة')->schema([
                        Forms\Components\FileUpload::make('footer_logo')
                            ->label('شعار الفوتر')
                            ->image()->disk('public')->directory('footer')->imageEditor()
                            ->helperText('اتركه فارغاً لاستخدام الشعار الأبيض الافتراضي')
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('footer_about_ar')->label('نبذة (عربي)')->rows(3),
                        Forms\Components\Textarea::make('footer_about_en')->label('About (English)')->rows(3),
                    ])->columns(2),

                    Forms\Components\Section::make('الأقسام الرئيسية')->schema([
                        Forms\Components\TextInput::make('footer_main_title_ar')->label('عنوان العمود (عربي)'),
                        Forms\Components\TextInput::make('footer_main_title_en')->label('Column title (English)'),
                        Forms\Components\Repeater::make('footer_main_links')
                            ->label('العناصر — اسحب لإعادة الترتيب')
                            ->schema([
                                Forms\Components\Hidden::make('key'),
                                Forms\Components\TextInput::make('label_ar')->label('العنوان (عربي)')->required(),
                                Forms\Components\TextInput::make('label_en')->label('Label (English)'),
                                Forms\Components\Toggle::make('is_visible')->label('ظاهر')->default(true),
                            ])
                            ->columns(2)
                            ->reorderable()
                            ->collapsible()
                            ->deletable(false)
                            ->addable(false)
                            ->itemLabel(fn (array $state): ?string => $state['label_ar'] ?? 'عنصر')
                            ->columnSpanFull(),
                    ])->columns(2),

                    Forms\Components\Section::make('روابط سريعة')->schema([
                        Forms\Components\TextInput::make('footer_quick_title_ar')->label('عنوان العمود (عربي)'),
                        Forms\Components\TextInput::make('footer_quick_title_en')->label('Column title (English)'),
                        Forms\Components\Repeater::make('footer_quick_links')
                            ->label('العناصر — اسحب لإعادة الترتيب')
                            ->schema([
                                Forms\Components\Hidden::make('key'),
                                Forms\Components\TextInput::make('label_ar')->label('العنوان (عربي)')->required(),
                                Forms\Components\TextInput::make('label_en')->label('Label (English)'),
                                Forms\Components\TextInput::make('url')
                                    ->label('الرابط')
                                    ->placeholder('https://example.com أو /page')
                                    ->helperText('رابط خارجي أو مسار داخلي — اترك # إن لم يكن جاهزاً')
                                    ->columnSpanFull(),
                                Forms\Components\Toggle::make('is_visible')->label('ظاهر')->default(true),
                            ])
                            ->columns(2)
                            ->reorderable()
                            ->collapsible()
                            ->deletable(false)
                            ->addable(false)
                            ->itemLabel(fn (array $state): ?string => $state['label_ar'] ?? 'عنصر')
                            ->columnSpanFull(),
                    ])->columns(2),

                    Forms\Components\Section::make('حقوق النشر')->schema([
                        Forms\Components\TextInput::make('footer_copyright_ar')->label('حقوق النشر (عربي)'),
                        Forms\Components\TextInput::make('footer_copyright_en')->label('Copyright (English)'),
                        Forms\Components\TextInput::make('footer_brand')->label('العلامة (مثل SAWTGAZA)'),
                        Forms\Components\TextInput::make('footer_newsletter_title_ar')->label('عنوان النشرة (عربي)'),
                        Forms\Components\TextInput::make('footer_newsletter_title_en')->label('Newsletter title (English)'),
                        Forms\Components\TextInput::make('footer_newsletter_desc_ar')->label('وصف النشرة (عربي)'),
                        Forms\Components\TextInput::make('footer_newsletter_desc_en')->label('Newsletter description (English)'),
                    ])->columns(2),
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
                        ->helperText('لما يكون مفعّل، بيتم جلب الريلز من حساب إنستغرام وعرضها بالأسفل وعبر /api/v1/reels')
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
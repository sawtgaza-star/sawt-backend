<?php

namespace App\Filament\Pages;

use App\Support\LocaleText;

use App\Models\Setting;
use App\Support\StoredUploadCleanup;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

/**
 * Filament settings page for the Sawt Incubator site (header, footer, landing sections).
 *
 * All values are stored as Setting keys (group: incubator) — no dedicated DB tables.
 * Landing sections on tab «الصفحة الأولى»: hero … employers, join CTA, testimonials.
 *
 * Event times use date + 12h hour/minute + AM/PM in the form (Filament DateTimePicker
 * is 24h-only); we hydrate/compose a single `starts_at` for the API.
 */
class IncubatorSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-building-library';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.incubator-settings';

    public ?array $data = [];

    public static function getNavigationGroup(): ?string
    {
        return __('Settings');
    }

    public static function getNavigationLabel(): string
    {
        return __('Incubator Settings');
    }

    public function getTitle(): string | \Illuminate\Contracts\Support\Htmlable
    {
        return __('Incubator Settings');
    }

    /**
     * key => [group, type, default]
     *
     * Defaults seed the form (and Setting::get fallbacks) before the first save.
     *
     * @return array<string, array{0: string, 1: string, 2: mixed}>
     */
    protected function fieldMeta(): array
    {
        return [
            'incubator_site_name' => ['incubator', 'string', 'حاضنة صوت'],
            'incubator_logo' => ['incubator', 'string', ''],
            'incubator_back_label_ar' => ['incubator', 'string', 'العودة لمنصة صوت'],
            'incubator_back_label_en' => ['incubator', 'string', 'Back to Sawt Platform'],
            'incubator_socials_label_ar' => ['incubator', 'string', 'وسائل التواصل الاجتماعي'],
            'incubator_socials_label_en' => ['incubator', 'string', 'Social Media'],
            'incubator_nav_join_label_ar' => ['incubator', 'string', 'انضم للحاضنة'],
            'incubator_nav_join_label_en' => ['incubator', 'string', 'Join the incubator'],
            'incubator_nav_support_label_ar' => ['incubator', 'string', 'ادعم طلاب الحاضنة'],
            'incubator_nav_support_label_en' => ['incubator', 'string', 'Support incubator students'],
            'incubator_nav_links' => ['incubator', 'json', [
                ['key' => 'about', 'label_ar' => 'عن الحاضنة', 'label_en' => 'About the incubator', 'is_visible' => true],
                ['key' => 'courses', 'label_ar' => 'الدورات', 'label_en' => 'Courses', 'is_visible' => true],
                ['key' => 'workshops', 'label_ar' => 'الورشات', 'label_en' => 'Workshops', 'is_visible' => true],
            ]],
            'incubator_footer_logo' => ['incubator', 'string', ''],
            'incubator_footer_about_ar' => ['incubator', 'text', 'منصة صوت، تأسست لتكون مساحة للمبدعين، تجمع الحاضنة، صوت ميديا، والصوت نفسه، لتقديم محتوى ملهم وتجارب فريدة لكل من يسعى لصوته أن يُسمع.'],
            'incubator_footer_about_en' => ['incubator', 'text', 'Sawt platform was founded as a space for creators — bringing together the incubator, Sawt Media, and Sawt itself.'],
            'incubator_footer_main_title_ar' => ['incubator', 'string', 'الأقسام الرئيسية'],
            'incubator_footer_main_title_en' => ['incubator', 'string', 'Main Sections'],
            'incubator_footer_main_links' => ['incubator', 'json', [
                ['key' => 'home', 'label_ar' => 'الرئيسية', 'label_en' => 'Home', 'is_visible' => true],
                ['key' => 'about', 'label_ar' => 'من نحن', 'label_en' => 'About Us', 'url' => '/about', 'is_visible' => true],
                ['key' => 'team', 'label_ar' => 'الفريق', 'label_en' => 'Team', 'is_visible' => true],
                ['key' => 'creators', 'label_ar' => 'صناع المحتوى', 'label_en' => 'Content Creators', 'is_visible' => true],
                ['key' => 'content', 'label_ar' => 'محتوانا', 'label_en' => 'Our Content', 'is_visible' => true],
            ]],
            'incubator_footer_sawt_title_ar' => ['incubator', 'string', 'اقسام صوت'],
            'incubator_footer_sawt_title_en' => ['incubator', 'string', 'Sawt Sections'],
            'incubator_footer_sawt_links' => ['incubator', 'json', [
                ['key' => 'platform', 'label_ar' => 'منصة صوت', 'label_en' => 'Sawt Platform', 'url' => '/', 'is_visible' => true],
                ['key' => 'incubator', 'label_ar' => 'حاضنة صوت', 'label_en' => 'Sawt Incubator', 'is_visible' => true],
                ['key' => 'media', 'label_ar' => 'صوت ميديا', 'label_en' => 'Sawt Media', 'url' => '/media', 'is_visible' => true],
            ]],
            'incubator_footer_newsletter_title_ar' => ['incubator', 'string', 'ابقَ على اطلاع'],
            'incubator_footer_newsletter_title_en' => ['incubator', 'string', 'Stay Updated'],
            'incubator_footer_newsletter_desc_ar' => ['incubator', 'string', 'اشترك في نشرتنا الإخبارية ..'],
            'incubator_footer_newsletter_desc_en' => ['incubator', 'string', 'Subscribe to our newsletter..'],
            'incubator_footer_copyright_ar' => ['incubator', 'string', '© جميع الحقوق محفوظة. 2026'],
            'incubator_footer_copyright_en' => ['incubator', 'string', '© All rights reserved. 2026'],
            'incubator_footer_brand' => ['incubator', 'string', 'SAWTGAZA'],

            'incubator_hero_image' => ['incubator', 'string', ''],
            'incubator_hero_foreground' => ['incubator', 'string', ''],
            'incubator_hero_title_ar' => ['incubator', 'string', 'حوّل قصتك إلى محتوى يصنع أثرًا'],
            'incubator_hero_title_en' => ['incubator', 'string', 'Turn your story into content that creates impact'],
            'incubator_hero_desc_ar' => ['incubator', 'text', 'انضم إلى بيئة تدريبية تجمع بين التعلم العملي، والإرشاد، والمشاريع الواقعية لتساعدك على صناعة محتوى يترك أثرًا.'],
            'incubator_hero_desc_en' => ['incubator', 'text', 'Join a training environment that combines hands-on learning, mentorship, and real projects to help you create impactful content.'],
            'incubator_hero_cta_ar' => ['incubator', 'string', 'ابدأ رحلتك التعليمية'],
            'incubator_hero_cta_en' => ['incubator', 'string', 'Start your learning journey'],
            // Badges overlaid on hero image (not baked into the image file)
            'incubator_hero_badge_top_value' => ['incubator', 'string', '1,247'],
            'incubator_hero_badge_top_label_ar' => ['incubator', 'string', 'هذا الشهر'],
            'incubator_hero_badge_top_label_en' => ['incubator', 'string', 'This month'],
            'incubator_hero_badge_bottom_value' => ['incubator', 'string', '+340'],
            'incubator_hero_badge_bottom_label_ar' => ['incubator', 'string', 'قصة وثقت'],
            'incubator_hero_badge_bottom_label_en' => ['incubator', 'string', 'Stories documented'],

            'incubator_stats' => ['incubator', 'json', [
                ['key' => 'students', 'value' => '+200', 'label_ar' => 'طالب مسجّل', 'label_en' => 'Enrolled students'],
                ['key' => 'mentors', 'value' => '+100', 'label_ar' => 'مدرب وخبير', 'label_en' => 'Mentors & experts'],
                ['key' => 'satisfaction', 'value' => '100%', 'label_ar' => 'معدل رضاء الطلاب', 'label_en' => 'Student satisfaction'],
                ['key' => 'graduates', 'value' => '+30', 'label_ar' => 'طالب متخرج', 'label_en' => 'Graduates'],
            ]],

            'incubator_why_title_ar' => ['incubator', 'string', 'لماذا حاضنة صوت؟'],
            'incubator_why_title_en' => ['incubator', 'string', 'Why Sawt Incubator?'],
            'incubator_why_subtitle_ar' => ['incubator', 'text', 'حاضنة صوت ليست مجرد منصة تدريبية، بل رحلة متكاملة تساعدك على تحويل أفكارك وقصصك إلى محتوى مؤثر.'],
            'incubator_why_subtitle_en' => ['incubator', 'text', 'Sawt Incubator is not just a training platform — it is a complete journey to turn your ideas into impactful content.'],
            'incubator_why_image' => ['incubator', 'string', ''],
            'incubator_why_items' => ['incubator', 'json', [
                ['icon' => '', 'title_ar' => 'تدريب عملي', 'title_en' => 'Hands-on training', 'desc_ar' => 'تعلم من خلال التطبيق والممارسة', 'desc_en' => 'Learn through practice and application'],
                ['icon' => '', 'title_ar' => 'إرشاد متخصص', 'title_en' => 'Specialized mentorship', 'desc_ar' => 'أنجز مشاريع حقيقية تبني معرض أعمالك', 'desc_en' => 'Build a portfolio with real projects'],
                ['icon' => '', 'title_ar' => 'مشاريع واقعية', 'title_en' => 'Real projects', 'desc_ar' => 'توجيه مستمر من خبراء في المجال', 'desc_en' => 'Ongoing guidance from field experts'],
                ['icon' => '', 'title_ar' => 'إيصال صوتك', 'title_en' => 'Amplify your voice', 'desc_ar' => 'فرصة لنشر أعمالك والوصول إلى جمهور أوسع', 'desc_en' => 'Publish your work and reach a wider audience'],
            ]],

            'incubator_courses_title_ar' => ['incubator', 'string', 'دوراتنا الأكثر شهرة'],
            'incubator_courses_title_en' => ['incubator', 'string', 'Our most popular courses'],
            'incubator_courses_subtitle_ar' => ['incubator', 'text', 'دورات تدريبية شاملة، تعتمد على التطبيق والتنفيذ العملي، نبدأ معك من الصفر حتى تصل إلى الاحتراف.'],
            'incubator_courses_subtitle_en' => ['incubator', 'text', 'Comprehensive courses built on practice — from zero to professional readiness.'],
            'incubator_courses_limit' => ['incubator', 'number', 6],

            // Sponsor students section (ساعد طلاب في الانضمام للحاضنة)
            'incubator_sponsor_title_ar' => ['incubator', 'string', 'ساعد طلاب في الانضمام للحاضنة'],
            'incubator_sponsor_title_en' => ['incubator', 'string', 'Help students join the incubator'],
            'incubator_sponsor_subtitle_ar' => ['incubator', 'text', 'مبلغ بسيط يفتح باب المعرفة أمام شاب في غزة — تبرعك يصل مباشرة لتغطية تكاليف التدريب'],
            'incubator_sponsor_subtitle_en' => ['incubator', 'text', 'A small amount opens the door to knowledge for a young person in Gaza — your donation covers training costs directly.'],
            'incubator_sponsor_packages' => ['incubator', 'json', [
                [
                    'title_ar' => 'صحافة ميدانية',
                    'title_en' => 'Field journalism',
                    'desc_ar' => 'تدريب ميداني على التغطية الإخبارية في مناطق النزاع',
                    'desc_en' => 'Field training on news coverage in conflict zones',
                    'duration_ar' => '8 أسابيع',
                    'duration_en' => '8 weeks',
                    'seats_ar' => '6 مقاعد',
                    'seats_en' => '6 seats',
                    'price' => '120',
                    'currency' => '$',
                    'cta_ar' => 'تكفل دورة صحافة ميدانية لطالب واحد ب 120$',
                    'cta_en' => 'Sponsor one Field Journalism seat for $120',
                ],
                [
                    'title_ar' => 'بودكاست وصوت',
                    'title_en' => 'Podcast & audio',
                    'desc_ar' => 'إنتاج محتوى صوتي احترافي يصل لملايين المستمعين',
                    'desc_en' => 'Produce professional audio content for millions of listeners',
                    'duration_ar' => '8 أسابيع',
                    'duration_en' => '8 weeks',
                    'seats_ar' => '6 مقاعد',
                    'seats_en' => '6 seats',
                    'price' => '120',
                    'currency' => '$',
                    'cta_ar' => 'تكفل دورة بودكاست وصوت لطالب واحد ب 120$',
                    'cta_en' => 'Sponsor one Podcast & Audio seat for $120',
                ],
                [
                    'title_ar' => 'إنتاج مرئي',
                    'title_en' => 'Visual production',
                    'desc_ar' => 'إنتاج محتوى مرئي احترافي يصل لملايين المشاهدين',
                    'desc_en' => 'Produce professional visual content for millions of viewers',
                    'duration_ar' => '8 أسابيع',
                    'duration_en' => '8 weeks',
                    'seats_ar' => '6 مقاعد',
                    'seats_en' => '6 seats',
                    'price' => '120',
                    'currency' => '$',
                    'cta_ar' => 'تكفل دورة إنتاج مرئي لطالب واحد ب 120$',
                    'cta_en' => 'Sponsor one Visual Production seat for $120',
                ],
                [
                    'title_ar' => 'كتابة إبداعية',
                    'title_en' => 'Creative writing',
                    'desc_ar' => 'إنتاج محتوى نصي احترافي يصل لملايين القرّاء',
                    'desc_en' => 'Produce professional written content for millions of readers',
                    'duration_ar' => '8 أسابيع',
                    'duration_en' => '8 weeks',
                    'seats_ar' => '6 مقاعد',
                    'seats_en' => '6 seats',
                    'price' => '120',
                    'currency' => '$',
                    'cta_ar' => 'تكفل دورة كتابة إبداعية لطالب واحد ب 120$',
                    'cta_en' => 'Sponsor one Creative Writing seat for $120',
                ],
            ]],
            'incubator_sponsor_waiting_title_ar' => ['incubator', 'string', 'طلاب ينتظرون داعماً'],
            'incubator_sponsor_waiting_title_en' => ['incubator', 'string', 'Students waiting for a sponsor'],
            'incubator_sponsor_waiting_more_ar' => ['incubator', 'string', '+28 طالباً آخرين'],
            'incubator_sponsor_waiting_more_en' => ['incubator', 'string', '+28 more students'],
            'incubator_sponsor_waiting_students' => ['incubator', 'json', [
                ['name' => 'ريم س.', 'meta_ar' => 'إنتاج مرئي، خانيونس', 'meta_en' => 'Visual production, Khan Younis', 'avatar' => ''],
                ['name' => 'أحمد خ.', 'meta_ar' => 'بودكاست وصوت، غزة', 'meta_en' => 'Podcast & audio, Gaza', 'avatar' => ''],
                ['name' => 'يوسف م.', 'meta_ar' => 'كتابة إبداعية، رفح', 'meta_en' => 'Creative writing, Rafah', 'avatar' => ''],
            ]],
            'incubator_sponsor_impact_title_ar' => ['incubator', 'string', 'أثر البرنامج'],
            'incubator_sponsor_impact_title_en' => ['incubator', 'string', 'Program impact'],
            'incubator_sponsor_impact_stats' => ['incubator', 'json', [
                ['value' => '+340', 'label_ar' => 'شخص يستفيد', 'label_en' => 'people benefit'],
                ['value' => '12', 'label_ar' => 'صحفيون ميدانيون', 'label_en' => 'field journalists'],
                ['value' => '47', 'label_ar' => 'أتموا دوراتهم', 'label_en' => 'completed their courses'],
            ]],

            // Events / workshops section — «استكشف أحدث فعالياتنا» (filters + cards)
            'incubator_events_title_ar' => ['incubator', 'string', 'استكشف أحدث فعالياتنا'],
            'incubator_events_title_en' => ['incubator', 'string', 'Explore our latest events'],
            'incubator_events_subtitle_ar' => ['incubator', 'text', 'أرقام حقيقية تعكس قوة مجتمعنا'],
            'incubator_events_subtitle_en' => ['incubator', 'text', 'Real numbers that reflect the strength of our community'],
            'incubator_events_all_label_ar' => ['incubator', 'string', 'الكل'],
            'incubator_events_all_label_en' => ['incubator', 'string', 'All'],
            'incubator_events_categories' => ['incubator', 'json', [
                ['key' => 'economy', 'label_ar' => 'الاقتصاد', 'label_en' => 'Economy'],
                ['key' => 'war_stories', 'label_ar' => 'قصص الحرب', 'label_en' => 'War stories'],
                ['key' => 'finance', 'label_ar' => 'المال والأعمال', 'label_en' => 'Finance & business'],
                ['key' => 'news', 'label_ar' => 'الأخبار', 'label_en' => 'News'],
            ]],
            'incubator_events_items' => ['incubator', 'json', [
                [
                    'image' => '',
                    'category_key' => 'economy',
                    'title_ar' => 'ابتكار الحلول الإبداعية في تصميم واجهات المستخدم، الدليل النهائي للابتكار',
                    'title_en' => 'Creative solutions in UI design — the ultimate innovation guide',
                    'desc_ar' => 'اكتشف كيفية تحويل الأفكار إلى تصميمات فعالة. تعلم استراتيجيات جديدة لتعزيز الإبداع في عملك.',
                    'desc_en' => 'Turn ideas into effective designs and learn strategies that boost creativity at work.',
                    'starts_date' => '2026-05-27',
                    'time_hour' => 11,
                    'time_minute' => 0,
                    'time_period' => 'PM',
                    'starts_at' => '2026-05-27 23:00:00',
                    'delivery' => 'in_person',
                    'format' => 'seminar',
                ],
                [
                    'image' => '',
                    'category_key' => 'war_stories',
                    'title_ar' => 'كيفية استخدام أدوات التصميم الحديثة لتحقيق نتائج مذهلة',
                    'title_en' => 'Using modern design tools for outstanding results',
                    'desc_ar' => 'استفد من أحدث التطورات في أدوات التصميم لتحسين سرعة وكفاءة العمل.',
                    'desc_en' => 'Leverage the latest design tools to improve speed and efficiency.',
                    'starts_date' => '2026-05-26',
                    'time_hour' => 10,
                    'time_minute' => 0,
                    'time_period' => 'PM',
                    'starts_at' => '2026-05-26 22:00:00',
                    'delivery' => 'online',
                    'format' => 'seminar',
                ],
                [
                    'image' => '',
                    'category_key' => 'news',
                    'title_ar' => 'الاستراتيجيات الفعالة لتحسين تجربة المستخدم، الدليل الشامل لتحسين الأداء',
                    'title_en' => 'Effective strategies to improve UX — a complete performance guide',
                    'desc_ar' => 'تعلم كيفية استخدام البيانات لتحسين تصاميمك وجعلها أكثر جاذبية.',
                    'desc_en' => 'Learn how to use data to improve designs and make them more engaging.',
                    'starts_date' => '2026-05-25',
                    'time_hour' => 9,
                    'time_minute' => 30,
                    'time_period' => 'PM',
                    'starts_at' => '2026-05-25 21:30:00',
                    'delivery' => 'in_person',
                    'format' => 'workshop',
                ],
            ]],

            // Gallery / album («الحاضنة بيتك الثاني ، البوم الحاضنة»)
            'incubator_gallery_title_ar' => ['incubator', 'string', 'الحاضنة بيتك الثاني ، البوم الحاضنة'],
            'incubator_gallery_title_en' => ['incubator', 'string', 'The incubator is your second home — album'],
            'incubator_gallery_subtitle_ar' => ['incubator', 'text', 'مبلغ بسيط يفتح باب المعرفة أمام شاب في غزة — تبرعك يصل مباشرة لتغطية تكاليف التدريب'],
            'incubator_gallery_subtitle_en' => ['incubator', 'text', 'A small amount opens the door to knowledge for a young person in Gaza — your donation covers training costs directly.'],
            'incubator_gallery_items' => ['incubator', 'json', [
                [
                    'image' => '',
                    'video_url' => '',
                    'caption_ar' => 'يوم الإطلاق — الدفعة الثالثة',
                    'caption_en' => 'Launch day — cohort 3',
                    'subtitle_ar' => '',
                    'subtitle_en' => '',
                ],
                [
                    'image' => '',
                    'video_url' => '',
                    'caption_ar' => 'ورشة عمل — التسويق بالمحتوى',
                    'caption_en' => 'Workshop — content marketing',
                    'subtitle_ar' => 'كل جلسة عملية لا محاضرات نظرية',
                    'subtitle_en' => 'Every session is practical — no theory lectures',
                ],
                [
                    'image' => '',
                    'video_url' => '',
                    'caption_ar' => 'يوم الإطلاق — الدفعة الثالثة',
                    'caption_en' => 'Launch day — cohort 3',
                    'subtitle_ar' => 'الدفعة تسجّل مشاريعها النهائية',
                    'subtitle_en' => 'The cohort presents final projects',
                ],
                [
                    'image' => '',
                    'video_url' => '',
                    'caption_ar' => 'جلسة مرشد 1:1',
                    'caption_en' => '1:1 mentorship session',
                    'subtitle_ar' => '',
                    'subtitle_en' => '',
                ],
                [
                    'image' => '',
                    'video_url' => '',
                    'caption_ar' => 'مجتمع صانعي المحتوى',
                    'caption_en' => 'Content creators community',
                    'subtitle_ar' => '',
                    'subtitle_en' => '',
                ],
            ]],

            // Experts section («فريق خبراء متخصص») — cards from CourseTrainer (active)
            'incubator_experts_title_ar' => ['incubator', 'string', 'فريق خبراء متخصص'],
            'incubator_experts_title_en' => ['incubator', 'string', 'Specialized expert team'],
            'incubator_experts_subtitle_ar' => ['incubator', 'text', 'أرقام حقيقية تعكس قوة مجتمعنا'],
            'incubator_experts_subtitle_en' => ['incubator', 'text', 'Real numbers that reflect the strength of our community'],
            'incubator_experts_limit' => ['incubator', 'number', 8],

            // FAQ («الأسئلة التي تدور ببالك؟»)
            'incubator_faq_title_ar' => ['incubator', 'string', 'الأسئلة التي تدور ببالك؟'],
            'incubator_faq_title_en' => ['incubator', 'string', 'Questions on your mind?'],
            'incubator_faq_subtitle_ar' => ['incubator', 'text', 'أرقام حقيقية تعكس قوة مجتمعنا'],
            'incubator_faq_subtitle_en' => ['incubator', 'text', 'Real numbers that reflect the strength of our community'],
            'incubator_faq_image' => ['incubator', 'string', ''],
            'incubator_faq_items' => ['incubator', 'json', [
                [
                    'question_ar' => 'هل يمكنني نشر أعمالي بعد التدريب؟',
                    'question_en' => 'Can I publish my work after training?',
                    'answer_ar' => 'نعم، سننهي البرنامج بمشاريع حقيقية جاهزة للنشر، وسنساعدك على عرضها عبر منصات صوت لتصل إلى جمهور أوسع.',
                    'answer_en' => 'Yes — the program ends with real projects ready to publish, and we help you showcase them on Sawt platforms.',
                ],
                [
                    'question_ar' => 'هل أحتاج خبرة مسبقة للتقديم؟',
                    'question_en' => 'Do I need prior experience to apply?',
                    'answer_ar' => 'لا، نرحّب بالمبتدئين ونبدأ معك من الأساسيات حتى الاحتراف.',
                    'answer_en' => 'No — beginners are welcome; we start from the basics through to professional readiness.',
                ],
                [
                    'question_ar' => 'هل البرنامج نظري أم عملي؟',
                    'question_en' => 'Is the program theoretical or practical?',
                    'answer_ar' => 'البرنامج عملي بشكل كامل، حيث سنقوم بتطبيق كل ما نتعلمه عبر مشاريع حقيقية.',
                    'answer_en' => 'Fully practical — everything you learn is applied through real projects.',
                ],
                [
                    'question_ar' => 'هل أحصل على شهادة بعد الانتهاء؟',
                    'question_en' => 'Do I get a certificate after completion?',
                    'answer_ar' => 'نعم، تحصل على شهادة إتمام معتمدة من حاضنة صوت بعد إنهاء متطلبات البرنامج.',
                    'answer_en' => 'Yes — you receive a Sawt Incubator completion certificate after finishing the program requirements.',
                ],
            ]],
            'incubator_faq_more_title_ar' => ['incubator', 'string', 'لديك سؤال آخر؟'],
            'incubator_faq_more_title_en' => ['incubator', 'string', 'Have another question?'],
            'incubator_faq_more_desc_ar' => ['incubator', 'text', 'فريقنا جاهز للإجابة — سنردّ عليك خلال ساعات'],
            'incubator_faq_more_desc_en' => ['incubator', 'text', 'Our team is ready to help — we usually reply within hours'],

            // Employers / trusted orgs («يعمل خريجونا لدى جهات موثوقة»)
            'incubator_employers_title_ar' => ['incubator', 'string', 'يعمل خريجونا لدى جهات موثوقة'],
            'incubator_employers_title_en' => ['incubator', 'string', 'Our graduates work at trusted organizations'],
            'incubator_employers_subtitle_ar' => ['incubator', 'text', 'نفخر بتميز خريجينا وحصولهم على وظائف مرموقة في جهات عالمية'],
            'incubator_employers_subtitle_en' => ['incubator', 'text', 'We are proud of our graduates and the prestigious roles they hold worldwide'],
            'incubator_employers_logos' => ['incubator', 'json', [
                ['name' => 'Dubai', 'logo' => '', 'url' => ''],
                ['name' => 'Holidayme', 'logo' => '', 'url' => ''],
                ['name' => 'IHG', 'logo' => '', 'url' => ''],
                ['name' => 'Haramain', 'logo' => '', 'url' => ''],
                ['name' => 'Vodafone', 'logo' => '', 'url' => ''],
                ['name' => 'Talabat', 'logo' => '', 'url' => ''],
            ]],

            'incubator_join_cta_bg' => ['incubator', 'string', ''],
            'incubator_join_cta_title_ar' => ['incubator', 'string', 'ابدأ رحلتك مع حاضنة صوت'],
            'incubator_join_cta_title_en' => ['incubator', 'string', 'Start your journey with Sawt Incubator'],
            'incubator_join_cta_desc_ar' => ['incubator', 'text', 'حوّل فكرتك إلى محتوى مؤثر، وطوّر مهاراتك من خلال التدريب العملي والإرشاد المتخصص.'],
            'incubator_join_cta_desc_en' => ['incubator', 'text', 'Turn your idea into impactful content and grow through hands-on training and specialized mentorship.'],
            'incubator_join_cta_button_ar' => ['incubator', 'string', 'انضم إلى الحاضنة'],
            'incubator_join_cta_button_en' => ['incubator', 'string', 'Join the incubator'],

            // Testimonials («شهادات وتجارب خريجينا») — last landing section
            'incubator_testimonials_title_ar' => ['incubator', 'string', 'شهادات وتجارب خريجينا'],
            'incubator_testimonials_title_en' => ['incubator', 'string', 'Graduate testimonials'],
            'incubator_testimonials_subtitle_ar' => ['incubator', 'text', 'اكتشف كيف غيّرت حاضنة صوت حياة المئات من الطلاب الذين بدأوا رحلتهم من الصفر وأصبحوا اليوم محترفين مطلوبين في سوق العمل.'],
            'incubator_testimonials_subtitle_en' => ['incubator', 'text', 'See how Sawt Incubator changed the lives of hundreds of students who started from zero and are now in demand.'],
            'incubator_testimonials_items' => ['incubator', 'json', [
                [
                    'avatar' => '',
                    'name' => 'سارة القحطاني',
                    'role_ar' => 'مخترعة — تقنية',
                    'role_en' => 'Inventor — Tech',
                    'quote_ar' => 'التوجيه الذي تلقيته من المرشدين كان له تأثير كبير على مسيرتي. نصائحهم القيمة ساعدتني في اتخاذ قرارات مدروسة في مشاريعي.',
                    'quote_en' => 'Mentorship guidance had a huge impact on my path and helped me make better project decisions.',
                    'rating' => 5,
                ],
                [
                    'avatar' => '',
                    'name' => 'فهد النعيمي',
                    'role_ar' => 'محلل بيانات — تقنية',
                    'role_en' => 'Data analyst — Tech',
                    'quote_ar' => 'التحديات التي واجهتها أثناء العمل في الحاضنة كانت محفزة لتطوير مهاراتي. التفاعل مع فرق متعددة التخصصات أضاف بعدًا جديدًا لرؤيتي.',
                    'quote_en' => 'Challenges in the incubator pushed my skills forward, and cross-disciplinary teamwork widened my vision.',
                    'rating' => 5,
                ],
                [
                    'avatar' => '',
                    'name' => 'ريم العتيبي',
                    'role_ar' => 'مصممة جرافيك — إبداع',
                    'role_en' => 'Graphic designer — Creative',
                    'quote_ar' => 'بيئة الحاضنة الداعمة منحتني الثقة لعرض أعمالي أمام جمهور حقيقي. اليوم أدير مشروعي الخاص وأتعاون مع علامات تجارية أعتز بها.',
                    'quote_en' => 'The supportive environment gave me confidence to show my work. Today I run my own project with brands I value.',
                    'rating' => 5,
                ],
            ]],
        ];
    }

    public function mount(): void
    {
        $values = [];

        foreach ($this->fieldMeta() as $key => [$group, $type, $default]) {
            $values[$key] = Setting::get($key, $default);
        }

        // Split stored `starts_at` into date + 12h fields for the admin UI.
        if (is_array($values['incubator_events_items'] ?? null)) {
            $values['incubator_events_items'] = collect($values['incubator_events_items'])
                ->map(fn ($item) => is_array($item) ? $this->hydrateEventItemTime($item) : $item)
                ->all();
        }

        $this->form->fill($values);
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Tabs::make('Incubator')->columnSpanFull()->tabs([
                Forms\Components\Tabs\Tab::make(__('الهيدر'))->icon('heroicon-o-bars-3')->schema([
                    Forms\Components\Section::make(__('هيدر الحاضنة'))->schema([
                        Forms\Components\TextInput::make('incubator_site_name')
                            ->label(__('اسم الموقع'))
                            ->columnSpanFull(),
                        Forms\Components\FileUpload::make('incubator_logo')
                            ->label(__('شعار الحاضنة'))
                            ->image()->disk('public')->directory('incubator/branding')->imageEditor()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('incubator_back_label_ar')->label(__('العودة للمنصة (عربي)')),
                        Forms\Components\TextInput::make('incubator_back_label_en')->label('Back to platform (EN)'),
                        Forms\Components\TextInput::make('incubator_socials_label_ar')->label(__('عنوان السوشيال (عربي)')),
                        Forms\Components\TextInput::make('incubator_socials_label_en')->label('Socials label (EN)'),
                        Forms\Components\TextInput::make('incubator_nav_join_label_ar')->label(__('زر انضم (عربي)')),
                        Forms\Components\TextInput::make('incubator_nav_join_label_en')->label('Join CTA (EN)'),
                        Forms\Components\TextInput::make('incubator_nav_support_label_ar')->label(__('زر ادعم الطلاب (عربي)')),
                        Forms\Components\TextInput::make('incubator_nav_support_label_en')->label('Support students (EN)'),
                        Forms\Components\Repeater::make('incubator_nav_links')
                            ->label(__('قائمة الحاضنة'))
                            ->schema([
                                Forms\Components\Hidden::make('key'),
                                Forms\Components\TextInput::make('label_ar')->label(__('العنوان (عربي)'))->required(),
                                Forms\Components\TextInput::make('label_en')->label('Label (EN)'),
                                Forms\Components\Toggle::make('is_visible')
                                    ->label(__('ظاهر'))
                                    ->default(true)
                                    ->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->reorderable()
                            ->deletable(false)
                            ->addable(false)
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => LocaleText::pick($state, 'label', 'رابط') ?: null)
                            ->columnSpanFull(),
                    ])->columns(2),
                ]),

                Forms\Components\Tabs\Tab::make(__('الفوتر'))->icon('heroicon-o-rectangle-group')->schema([
                    Forms\Components\Section::make(__('فوتر الحاضنة'))->schema([
                        Forms\Components\FileUpload::make('incubator_footer_logo')
                            ->label(__('شعار الفوتر'))
                            ->image()->disk('public')->directory('incubator/branding')->imageEditor()
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('incubator_footer_about_ar')->label(__('نبذة (عربي)'))->rows(3),
                        Forms\Components\Textarea::make('incubator_footer_about_en')->label('About (EN)')->rows(3),
                        Forms\Components\TextInput::make('incubator_footer_main_title_ar')->label(__('عنوان الأقسام الرئيسية (عربي)')),
                        Forms\Components\TextInput::make('incubator_footer_main_title_en')->label('Main sections title (EN)'),
                        Forms\Components\Repeater::make('incubator_footer_main_links')
                            ->label(__('روابط الأقسام الرئيسية'))
                            ->schema([
                                Forms\Components\Hidden::make('key'),
                                Forms\Components\TextInput::make('label_ar')->label(__('العنوان (عربي)'))->required(),
                                Forms\Components\TextInput::make('label_en')->label('Label (EN)'),
                                Forms\Components\Toggle::make('is_visible')
                                    ->label(__('ظاهر'))
                                    ->default(true)
                                    ->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => LocaleText::pick($state, 'label', 'رابط') ?: null)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('incubator_footer_sawt_title_ar')->label(__('عنوان أقسام صوت (عربي)')),
                        Forms\Components\TextInput::make('incubator_footer_sawt_title_en')->label('Sawt sections title (EN)'),
                        Forms\Components\Repeater::make('incubator_footer_sawt_links')
                            ->label(__('روابط أقسام صوت'))
                            ->schema([
                                Forms\Components\Hidden::make('key'),
                                Forms\Components\TextInput::make('label_ar')->label(__('العنوان (عربي)'))->required(),
                                Forms\Components\TextInput::make('label_en')->label('Label (EN)'),
                                Forms\Components\Toggle::make('is_visible')
                                    ->label(__('ظاهر'))
                                    ->default(true)
                                    ->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => LocaleText::pick($state, 'label', 'رابط') ?: null)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('incubator_footer_newsletter_title_ar')->label(__('عنوان النشرة (عربي)')),
                        Forms\Components\TextInput::make('incubator_footer_newsletter_title_en')->label('Newsletter title (EN)'),
                        Forms\Components\TextInput::make('incubator_footer_newsletter_desc_ar')->label(__('وصف النشرة (عربي)')),
                        Forms\Components\TextInput::make('incubator_footer_newsletter_desc_en')->label('Newsletter desc (EN)'),
                        Forms\Components\TextInput::make('incubator_footer_copyright_ar')->label(__('حقوق النشر (عربي)')),
                        Forms\Components\TextInput::make('incubator_footer_copyright_en')->label('Copyright (EN)'),
                        Forms\Components\TextInput::make('incubator_footer_brand')
                            ->label(__('العلامة'))
                            ->columnSpanFull(),
                    ])->columns(2),
                ]),

                Forms\Components\Tabs\Tab::make(__('الصفحة الأولى'))->icon('heroicon-o-home')->schema([
                    // Landing sections mirror GET /api/v1/pages/incubator
                    Forms\Components\Section::make(__('1) الهيرو'))->schema([
                        Forms\Components\FileUpload::make('incubator_hero_image')
                            ->label(__('صورة الخلفية'))
                            ->helperText(__('الكولاج / الصور خلف الشجرة — بدون أرقام وبدون الشجرة'))
                            ->image()->disk('public')->directory('incubator/hero')->imageEditor()
                            ->columnSpanFull(),
                        Forms\Components\FileUpload::make('incubator_hero_foreground')
                            ->label(__('الصورة الأمامية (الشجرة)'))
                            ->helperText(__('صورة الزيتونة فوق الخلفية — منفصلة عن الخلفية والشارات'))
                            ->image()->disk('public')->directory('incubator/hero')->imageEditor()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('incubator_hero_title_ar')->label(__('العنوان (عربي)')),
                        Forms\Components\TextInput::make('incubator_hero_title_en')->label('Title (EN)'),
                        Forms\Components\Textarea::make('incubator_hero_desc_ar')->label(__('الوصف (عربي)'))->rows(3),
                        Forms\Components\Textarea::make('incubator_hero_desc_en')->label('Description (EN)')->rows(3),
                        Forms\Components\TextInput::make('incubator_hero_cta_ar')->label(__('نص الزر (عربي)')),
                        Forms\Components\TextInput::make('incubator_hero_cta_en')->label('CTA (EN)'),

                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('incubator_hero_badge_top_value')
                                    ->label(__('شارة أعلى — الرقم'))
                                    ->placeholder('1,247'),
                                Forms\Components\TextInput::make('incubator_hero_badge_top_label_ar')
                                    ->label(__('التسمية (عربي)'))
                                    ->placeholder(__('هذا الشهر')),
                                Forms\Components\TextInput::make('incubator_hero_badge_top_label_en')
                                    ->label('Label (EN)')
                                    ->placeholder('This month'),
                            ])
                            ->columnSpanFull(),

                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('incubator_hero_badge_bottom_value')
                                    ->label(__('شارة أسفل — الرقم'))
                                    ->placeholder('+340'),
                                Forms\Components\TextInput::make('incubator_hero_badge_bottom_label_ar')
                                    ->label(__('التسمية (عربي)'))
                                    ->placeholder(__('قصة وثقت')),
                                Forms\Components\TextInput::make('incubator_hero_badge_bottom_label_en')
                                    ->label('Label (EN)')
                                    ->placeholder('Stories documented'),
                            ])
                            ->columnSpanFull(),
                    ])->columns(2),

                    Forms\Components\Section::make(__('2) شريط الأرقام'))->schema([
                        Forms\Components\Repeater::make('incubator_stats')
                            ->label(__('الأرقام'))
                            ->schema([
                                Forms\Components\TextInput::make('key')->label(__('المفتاح'))->maxLength(40),
                                Forms\Components\TextInput::make('value')->label(__('القيمة'))->required(),
                                Forms\Components\TextInput::make('label_ar')->label(__('التسمية (عربي)')),
                                Forms\Components\TextInput::make('label_en')->label('Label (EN)'),
                            ])
                            ->columns(2)
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => trim(($state['value'] ?? '').' '.LocaleText::pick($state, 'label')))
                            ->addActionLabel(__('➕ إضافة رقم'))
                            ->columnSpanFull(),
                    ]),

                    Forms\Components\Section::make(__('3) لماذا حاضنة صوت؟'))->schema([
                        Forms\Components\TextInput::make('incubator_why_title_ar')->label(__('العنوان (عربي)')),
                        Forms\Components\TextInput::make('incubator_why_title_en')->label('Title (EN)'),
                        Forms\Components\Textarea::make('incubator_why_subtitle_ar')->label(__('الوصف (عربي)'))->rows(2),
                        Forms\Components\Textarea::make('incubator_why_subtitle_en')->label('Subtitle (EN)')->rows(2),
                        Forms\Components\FileUpload::make('incubator_why_image')
                            ->label(__('الصورة'))
                            ->image()->disk('public')->directory('incubator/why')->imageEditor()
                            ->columnSpanFull(),
                        Forms\Components\Repeater::make('incubator_why_items')
                            ->label(__('المميزات'))
                            ->schema([
                                Forms\Components\FileUpload::make('icon')
                                    ->label(__('أيقونة'))
                                    ->image()->disk('public')->directory('incubator/icons')->imageEditor()
                                    ->columnSpanFull(),
                                Forms\Components\TextInput::make('title_ar')->label(__('العنوان (عربي)')),
                                Forms\Components\TextInput::make('title_en')->label('Title (EN)'),
                                Forms\Components\Textarea::make('desc_ar')->label(__('الوصف (عربي)'))->rows(2),
                                Forms\Components\Textarea::make('desc_en')->label('Description (EN)')->rows(2),
                            ])
                            ->columns(2)
                            ->reorderable()
                            ->maxItems(4)
                            ->deletable(false)
                            ->addable(false)
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => LocaleText::pick($state, 'title', 'ميزة') ?: null)
                            ->columnSpanFull(),
                    ])->columns(2),

                    Forms\Components\Section::make(__('4) الدورات (قائمة فقط)'))
                        ->description(__('البطاقات من Courses المنشور — بدون صفحة تفاصيل هنا'))
                        ->schema([
                            Forms\Components\TextInput::make('incubator_courses_title_ar')->label(__('العنوان (عربي)')),
                            Forms\Components\TextInput::make('incubator_courses_title_en')->label('Title (EN)'),
                            Forms\Components\Textarea::make('incubator_courses_subtitle_ar')->label(__('الوصف (عربي)'))->rows(2),
                            Forms\Components\Textarea::make('incubator_courses_subtitle_en')->label('Subtitle (EN)')->rows(2),
                            Forms\Components\TextInput::make('incubator_courses_limit')
                                ->label(__('عدد الدورات المعروضة'))
                                ->numeric()->minValue(1)->maxValue(24)
                                ->columnSpanFull(),
                        ])->columns(2),

                    Forms\Components\Section::make(__('5) ساعد طلاب في الانضمام للحاضنة'))
                        ->description(__('قسم التكفّل / رعاية المقاعد + الطلاب المنتظرون + أثر البرنامج'))
                        ->schema([
                            Forms\Components\TextInput::make('incubator_sponsor_title_ar')->label(__('العنوان (عربي)')),
                            Forms\Components\TextInput::make('incubator_sponsor_title_en')->label('Title (EN)'),
                            Forms\Components\Textarea::make('incubator_sponsor_subtitle_ar')->label(__('الوصف (عربي)'))->rows(2),
                            Forms\Components\Textarea::make('incubator_sponsor_subtitle_en')->label('Subtitle (EN)')->rows(2),

                            Forms\Components\Repeater::make('incubator_sponsor_packages')
                                ->label(__('بطاقات التكفّل'))
                                ->schema([
                                    Forms\Components\TextInput::make('title_ar')->label(__('عنوان الدورة (عربي)'))->required(),
                                    Forms\Components\TextInput::make('title_en')->label('Course title (EN)'),
                                    Forms\Components\Textarea::make('desc_ar')->label(__('الوصف (عربي)'))->rows(2),
                                    Forms\Components\Textarea::make('desc_en')->label('Description (EN)')->rows(2),
                                    Forms\Components\TextInput::make('duration_ar')->label(__('المدة (عربي)'))->placeholder(__('8 أسابيع')),
                                    Forms\Components\TextInput::make('duration_en')->label('Duration (EN)')->placeholder('8 weeks'),
                                    Forms\Components\TextInput::make('seats_ar')->label(__('المقاعد (عربي)'))->placeholder(__('6 مقاعد')),
                                    Forms\Components\TextInput::make('seats_en')->label('Seats (EN)')->placeholder('6 seats'),
                                    Forms\Components\TextInput::make('price')->label(__('المبلغ'))->placeholder('120'),
                                    Forms\Components\TextInput::make('currency')->label(__('العملة'))->placeholder('$')->default('$'),
                                    Forms\Components\TextInput::make('cta_ar')->label(__('نص الزر (عربي)')),
                                    Forms\Components\TextInput::make('cta_en')->label('Button (EN)'),
                                ])
                                ->columns(2)
                                ->reorderable()
                                ->collapsible()
                                ->itemLabel(fn (array $state): ?string => LocaleText::pick($state, 'title', 'بطاقة') ?: null)
                                ->addActionLabel(__('➕ إضافة بطاقة تكفّل'))
                                ->columnSpanFull(),

                            Forms\Components\TextInput::make('incubator_sponsor_waiting_title_ar')->label(__('عنوان قائمة الانتظار (عربي)')),
                            Forms\Components\TextInput::make('incubator_sponsor_waiting_title_en')->label('Waiting list title (EN)'),
                            Forms\Components\TextInput::make('incubator_sponsor_waiting_more_ar')->label(__('نص «المزيد» (عربي)'))->placeholder(__('+28 طالباً آخرين')),
                            Forms\Components\TextInput::make('incubator_sponsor_waiting_more_en')->label('More label (EN)'),
                            Forms\Components\Repeater::make('incubator_sponsor_waiting_students')
                                ->label(__('الطلاب المنتظرون'))
                                ->schema([
                                    Forms\Components\FileUpload::make('avatar')
                                        ->label(__('الصورة'))
                                        ->image()->disk('public')->directory('incubator/sponsor/students')->imageEditor()
                                        ->columnSpanFull(),
                                    Forms\Components\TextInput::make('name')->label(__('الاسم'))->required()->columnSpanFull(),
                                    Forms\Components\TextInput::make('meta_ar')->label(__('التخصص/المكان (عربي)')),
                                    Forms\Components\TextInput::make('meta_en')->label('Specialty/location (EN)'),
                                ])
                                ->columns(2)
                                ->reorderable()
                                ->collapsible()
                                ->itemLabel(fn (array $state): ?string => $state['name'] ?? __('طالب'))
                                ->addActionLabel(__('➕ إضافة طالب'))
                                ->columnSpanFull(),

                            Forms\Components\TextInput::make('incubator_sponsor_impact_title_ar')->label(__('عنوان الأثر (عربي)')),
                            Forms\Components\TextInput::make('incubator_sponsor_impact_title_en')->label('Impact title (EN)'),
                            Forms\Components\Repeater::make('incubator_sponsor_impact_stats')
                                ->label(__('أرقام الأثر'))
                                ->schema([
                                    Forms\Components\TextInput::make('value')->label(__('القيمة'))->required(),
                                    Forms\Components\TextInput::make('label_ar')->label(__('التسمية (عربي)')),
                                    Forms\Components\TextInput::make('label_en')->label('Label (EN)'),
                                ])
                                ->columns(3)
                                ->reorderable()
                                ->itemLabel(fn (array $state): ?string => trim(($state['value'] ?? '').' '.LocaleText::pick($state, 'label')))
                                ->addActionLabel(__('➕ رقم'))
                                ->columnSpanFull(),
                        ])->columns(2),

                    Forms\Components\Section::make(__('6) استكشف أحدث فعالياتنا'))
                        ->description(__('الورشات / الندوات مع فلاتر التصنيف'))
                        ->schema([
                            Forms\Components\TextInput::make('incubator_events_title_ar')->label(__('العنوان (عربي)')),
                            Forms\Components\TextInput::make('incubator_events_title_en')->label('Title (EN)'),
                            Forms\Components\Textarea::make('incubator_events_subtitle_ar')->label(__('الوصف (عربي)'))->rows(2),
                            Forms\Components\Textarea::make('incubator_events_subtitle_en')->label('Subtitle (EN)')->rows(2),
                            Forms\Components\TextInput::make('incubator_events_all_label_ar')->label(__('تسمية «الكل» (عربي)')),
                            Forms\Components\TextInput::make('incubator_events_all_label_en')->label('“All” label (EN)'),

                            Forms\Components\Repeater::make('incubator_events_categories')
                                ->label(__('فلاتر التصنيف'))
                                ->schema([
                                    Forms\Components\TextInput::make('key')
                                        ->label(__('المفتاح'))
                                        ->required()
                                        ->helperText(__('مثل: economy')),
                                    Forms\Components\TextInput::make('label_ar')->label(__('الاسم (عربي)'))->required(),
                                    Forms\Components\TextInput::make('label_en')->label('Name (EN)'),
                                ])
                                ->columns(3)
                                ->reorderable()
                                ->collapsible()
                                ->itemLabel(fn (array $state): ?string => LocaleText::pick($state, 'label', 'تصنيف') ?: (string) ($state['key'] ?? '') ?: null)
                                ->addActionLabel(__('➕ تصنيف'))
                                ->columnSpanFull(),

                            Forms\Components\Repeater::make('incubator_events_items')
                                ->label(__('الفعاليات'))
                                ->schema([
                                    Forms\Components\FileUpload::make('image')
                                        ->label(__('الصورة'))
                                        ->image()->disk('public')->directory('incubator/events')->imageEditor()
                                        ->columnSpanFull(),
                                    Forms\Components\Select::make('category_key')
                                        ->label(__('التصنيف'))
                                        ->options(function (): array {
                                            $cats = $this->data['incubator_events_categories'] ?? [];
                                            if (! is_array($cats)) {
                                                return [];
                                            }

                                            $options = [];
                                            foreach ($cats as $cat) {
                                                if (! is_array($cat) || blank($cat['key'] ?? null)) {
                                                    continue;
                                                }
                                                $options[(string) $cat['key']] = (string) ($cat['label_ar'] ?: $cat['key']);
                                            }

                                            return $options;
                                        })
                                        ->searchable()
                                        ->columnSpanFull(),
                                    Forms\Components\TextInput::make('title_ar')->label(__('العنوان (عربي)'))->required(),
                                    Forms\Components\TextInput::make('title_en')->label('Title (EN)'),
                                    Forms\Components\Textarea::make('desc_ar')->label(__('الوصف (عربي)'))->rows(2),
                                    Forms\Components\Textarea::make('desc_en')->label('Description (EN)')->rows(2),
                                    // Filament DateTimePicker has no AM/PM UI — use 12h selects instead.
                                    Forms\Components\Grid::make(4)
                                        ->schema([
                                            Forms\Components\DatePicker::make('starts_date')
                                                ->label(__('التاريخ'))
                                                ->native(false)
                                                ->displayFormat('Y-m-d'),
                                            Forms\Components\Select::make('time_hour')
                                                ->label(__('الساعة'))
                                                ->options(collect(range(1, 12))->mapWithKeys(fn (int $h) => [(string) $h => sprintf('%02d', $h)])->all())
                                                ->native(false),
                                            Forms\Components\Select::make('time_minute')
                                                ->label(__('الدقيقة'))
                                                ->options(collect(range(0, 59))->mapWithKeys(fn (int $m) => [(string) $m => sprintf('%02d', $m)])->all())
                                                ->native(false),
                                            Forms\Components\Select::make('time_period')
                                                ->label('AM / PM')
                                                ->options([
                                                    'AM' => 'AM',
                                                    'PM' => 'PM',
                                                ])
                                                ->native(false),
                                        ])
                                        ->columnSpanFull(),
                                    Forms\Components\Select::make('delivery')
                                        ->label(__('الحضور'))
                                        ->options([
                                            'in_person' => 'وجاهي',
                                            'online' => 'أونلاين',
                                        ])
                                        ->required(),
                                    Forms\Components\Select::make('format')
                                        ->label(__('النوع'))
                                        ->options([
                                            'workshop' => 'ورشة عمل',
                                            'seminar' => 'ندوة',
                                        ])
                                        ->required(),
                                ])
                                ->columns(2)
                                ->reorderable()
                                ->collapsible()
                                ->itemLabel(fn (array $state): ?string => LocaleText::pick($state, 'title', 'فعالية') ?: null)
                                ->addActionLabel(__('➕ إضافة فعالية'))
                                ->columnSpanFull(),
                        ])->columns(2),

                    Forms\Components\Section::make(__('7) البوم الحاضنة'))
                        ->description(__('عنوان القسم + صور/فيديو المعرض (الترتيب = ترتيب العرض)'))
                        ->schema([
                            Forms\Components\TextInput::make('incubator_gallery_title_ar')->label(__('العنوان (عربي)')),
                            Forms\Components\TextInput::make('incubator_gallery_title_en')->label('Title (EN)'),
                            Forms\Components\Textarea::make('incubator_gallery_subtitle_ar')->label(__('الوصف (عربي)'))->rows(2),
                            Forms\Components\Textarea::make('incubator_gallery_subtitle_en')->label('Subtitle (EN)')->rows(2),

                            Forms\Components\Repeater::make('incubator_gallery_items')
                                ->label(__('عناصر المعرض'))
                                ->schema([
                                    Forms\Components\FileUpload::make('image')
                                        ->label(__('الصورة / بوستر الفيديو'))
                                        ->image()->disk('public')->directory('incubator/gallery')->imageEditor()
                                        ->columnSpanFull(),
                                    // Optional: if filled, API marks the item as type=video
                                    Forms\Components\TextInput::make('video_url')
                                        ->label(__('رابط الفيديو (اختياري)'))
                                        ->url()
                                        ->placeholder('https://…')
                                        ->columnSpanFull(),
                                    Forms\Components\TextInput::make('caption_ar')->label(__('التعليق (عربي)')),
                                    Forms\Components\TextInput::make('caption_en')->label('Caption (EN)'),
                                    Forms\Components\TextInput::make('subtitle_ar')->label(__('سطر إضافي (عربي)')),
                                    Forms\Components\TextInput::make('subtitle_en')->label('Extra line (EN)'),
                                ])
                                ->columns(2)
                                ->reorderable()
                                ->collapsible()
                                ->itemLabel(fn (array $state): ?string => LocaleText::pick($state, 'caption', 'عنصر') ?: null)
                                ->addActionLabel(__('➕ إضافة عنصر'))
                                ->columnSpanFull(),
                        ])->columns(2),

                    Forms\Components\Section::make(__('8) فريق خبراء متخصص'))
                        ->description(__('العنوان من هنا — البطاقات من «مدربو الدورات» (النشطون فقط)'))
                        ->schema([
                            Forms\Components\TextInput::make('incubator_experts_title_ar')->label(__('العنوان (عربي)')),
                            Forms\Components\TextInput::make('incubator_experts_title_en')->label('Title (EN)'),
                            Forms\Components\Textarea::make('incubator_experts_subtitle_ar')->label(__('الوصف (عربي)'))->rows(2),
                            Forms\Components\Textarea::make('incubator_experts_subtitle_en')->label('Subtitle (EN)')->rows(2),
                            Forms\Components\TextInput::make('incubator_experts_limit')
                                ->label(__('عدد المدربين المعروضين'))
                                ->numeric()->minValue(1)->maxValue(24)
                                ->helperText(__('أدِر الأسماء والصور والخبرة من: الدورات → مدربو الدورات'))
                                ->columnSpanFull(),
                        ])->columns(2),

                    Forms\Components\Section::make(__('9) الأسئلة الشائعة'))
                        ->description(__('عنوان القسم + الصورة الجانبية + أسئلة وأجوبة الأكورديون'))
                        ->schema([
                            Forms\Components\TextInput::make('incubator_faq_title_ar')->label(__('العنوان (عربي)')),
                            Forms\Components\TextInput::make('incubator_faq_title_en')->label('Title (EN)'),
                            Forms\Components\Textarea::make('incubator_faq_subtitle_ar')->label(__('الوصف (عربي)'))->rows(2),
                            Forms\Components\Textarea::make('incubator_faq_subtitle_en')->label('Subtitle (EN)')->rows(2),
                            Forms\Components\FileUpload::make('incubator_faq_image')
                                ->label(__('الصورة الجانبية'))
                                ->image()->disk('public')->directory('incubator/faq')->imageEditor()
                                ->columnSpanFull(),

                            Forms\Components\Repeater::make('incubator_faq_items')
                                ->label(__('الأسئلة والأجوبة'))
                                ->schema([
                                    Forms\Components\TextInput::make('question_ar')->label(__('السؤال (عربي)'))->required(),
                                    Forms\Components\TextInput::make('question_en')->label('Question (EN)'),
                                    Forms\Components\Textarea::make('answer_ar')->label(__('الجواب (عربي)'))->rows(3)->required(),
                                    Forms\Components\Textarea::make('answer_en')->label('Answer (EN)')->rows(3),
                                ])
                                ->columns(2)
                                ->reorderable()
                                ->collapsible()
                                ->itemLabel(fn (array $state): ?string => LocaleText::pick($state, 'question', 'سؤال') ?: null)
                                ->addActionLabel(__('➕ إضافة سؤال'))
                                ->columnSpanFull(),

                            Forms\Components\TextInput::make('incubator_faq_more_title_ar')->label(__('عنوان «سؤال آخر» (عربي)')),
                            Forms\Components\TextInput::make('incubator_faq_more_title_en')->label('More questions title (EN)'),
                            Forms\Components\Textarea::make('incubator_faq_more_desc_ar')->label(__('وصف «سؤال آخر» (عربي)'))->rows(2),
                            Forms\Components\Textarea::make('incubator_faq_more_desc_en')->label('More questions desc (EN)')->rows(2),
                        ])->columns(2),

                    Forms\Components\Section::make(__('10) يعمل خريجونا لدى جهات موثوقة'))
                        ->description(__('عنوان القسم + شعارات الجهات / الشركات'))
                        ->schema([
                            Forms\Components\TextInput::make('incubator_employers_title_ar')->label(__('العنوان (عربي)')),
                            Forms\Components\TextInput::make('incubator_employers_title_en')->label('Title (EN)'),
                            Forms\Components\Textarea::make('incubator_employers_subtitle_ar')->label(__('الوصف (عربي)'))->rows(2),
                            Forms\Components\Textarea::make('incubator_employers_subtitle_en')->label('Subtitle (EN)')->rows(2),
                            Forms\Components\Repeater::make('incubator_employers_logos')
                                ->label(__('الشعارات'))
                                ->schema([
                                    Forms\Components\FileUpload::make('logo')
                                        ->label(__('الشعار'))
                                        ->image()->disk('public')->directory('incubator/employers')->imageEditor()
                                        ->columnSpanFull(),
                                    Forms\Components\TextInput::make('name')->label(__('الاسم (اختياري)')),
                                    Forms\Components\TextInput::make('url')->label(__('الرابط (اختياري)'))->url(),
                                ])
                                ->columns(2)
                                ->reorderable()
                                ->collapsible()
                                ->itemLabel(fn (array $state): ?string => $state['name'] ?? __('شعار'))
                                ->addActionLabel(__('➕ إضافة شعار'))
                                ->columnSpanFull(),
                        ])->columns(2),

                    Forms\Components\Section::make(__('11) دعوة الانضمام'))->schema([
                        Forms\Components\FileUpload::make('incubator_join_cta_bg')
                            ->label(__('خلفية البنر'))
                            ->image()->disk('public')->directory('incubator/cta')->imageEditor()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('incubator_join_cta_title_ar')->label(__('العنوان (عربي)')),
                        Forms\Components\TextInput::make('incubator_join_cta_title_en')->label('Title (EN)'),
                        Forms\Components\Textarea::make('incubator_join_cta_desc_ar')->label(__('الوصف (عربي)'))->rows(2),
                        Forms\Components\Textarea::make('incubator_join_cta_desc_en')->label('Description (EN)')->rows(2),
                        Forms\Components\TextInput::make('incubator_join_cta_button_ar')->label(__('نص الزر (عربي)')),
                        Forms\Components\TextInput::make('incubator_join_cta_button_en')->label('Button (EN)'),
                    ])->columns(2),

                    Forms\Components\Section::make(__('12) شهادات وتجارب خريجينا'))
                        ->description(__('آخر قسم في الصفحة — شريط شهادات الخريجين'))
                        ->schema([
                            Forms\Components\TextInput::make('incubator_testimonials_title_ar')->label(__('العنوان (عربي)')),
                            Forms\Components\TextInput::make('incubator_testimonials_title_en')->label('Title (EN)'),
                            Forms\Components\Textarea::make('incubator_testimonials_subtitle_ar')->label(__('الوصف (عربي)'))->rows(3),
                            Forms\Components\Textarea::make('incubator_testimonials_subtitle_en')->label('Subtitle (EN)')->rows(3),

                            Forms\Components\Repeater::make('incubator_testimonials_items')
                                ->label(__('الشهادات'))
                                ->schema([
                                    Forms\Components\FileUpload::make('avatar')
                                        ->label(__('الصورة'))
                                        ->image()->disk('public')->directory('incubator/testimonials')->imageEditor()
                                        ->columnSpanFull(),
                                    Forms\Components\TextInput::make('name')->label(__('الاسم'))->required()->columnSpanFull(),
                                    Forms\Components\TextInput::make('role_ar')->label(__('المسمى / المجال (عربي)')),
                                    Forms\Components\TextInput::make('role_en')->label('Role / field (EN)'),
                                    Forms\Components\Textarea::make('quote_ar')->label(__('الشهادة (عربي)'))->rows(3)->required(),
                                    Forms\Components\Textarea::make('quote_en')->label('Quote (EN)')->rows(3),
                                    Forms\Components\TextInput::make('rating')
                                        ->label(__('التقييم (1–5)'))
                                        ->numeric()->minValue(1)->maxValue(5)->default(5)
                                        ->columnSpanFull(),
                                ])
                                ->columns(2)
                                ->reorderable()
                                ->collapsible()
                                ->itemLabel(fn (array $state): ?string => $state['name'] ?? __('شهادة'))
                                ->addActionLabel(__('➕ إضافة شهادة'))
                                ->columnSpanFull(),
                        ])->columns(2),
                ]),
            ]),
        ])->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();
        $meta = $this->fieldMeta();

        // Persist a single 24h `starts_at` for the API while keeping AM/PM fields for re-edit.
        if (is_array($state['incubator_events_items'] ?? null)) {
            $state['incubator_events_items'] = collect($state['incubator_events_items'])
                ->map(fn ($item) => is_array($item) ? $this->composeEventItemTime($item) : $item)
                ->all();
        }

        $oldValues = [];
        $newValues = [];

        foreach ($meta as $key => [$group, $type, $default]) {
            $oldValues[$key] = Setting::get($key, $default);
            $newValues[$key] = $state[$key] ?? $default;
        }

        StoredUploadCleanup::pruneReplaced($oldValues, $newValues);

        foreach ($meta as $key => [$group, $type, $default]) {
            Setting::set($key, $newValues[$key], group: $group, type: $type);
        }

        Notification::make()
            ->title(__('تم حفظ إعدادات الحاضنة بنجاح'))
            ->success()
            ->send();
    }

    /**
     * Expand `starts_at` (Y-m-d H:i:s) into starts_date + time_hour/minute/period for the form.
     * Skips when AM/PM fields are already present (e.g. after first save with new shape).
     *
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    protected function hydrateEventItemTime(array $item): array
    {
        if (filled($item['starts_date'] ?? null) && filled($item['time_period'] ?? null)) {
            $item['time_hour'] = (int) ($item['time_hour'] ?? 12);
            $item['time_minute'] = (int) ($item['time_minute'] ?? 0);

            return $item;
        }

        if (blank($item['starts_at'] ?? null)) {
            return $item;
        }

        try {
            $dt = \Illuminate\Support\Carbon::parse($item['starts_at']);
        } catch (\Throwable) {
            return $item;
        }

        $hour24 = (int) $dt->format('G');
        $period = $hour24 >= 12 ? 'PM' : 'AM';
        $hour12 = $hour24 % 12;
        if ($hour12 === 0) {
            $hour12 = 12; // midnight / noon
        }

        $item['starts_date'] = $dt->format('Y-m-d');
        $item['time_hour'] = $hour12;
        $item['time_minute'] = (int) $dt->format('i');
        $item['time_period'] = $period;

        return $item;
    }

    /**
     * Build `starts_at` from the 12-hour admin fields (hour 1–12 + AM/PM → 24h clock).
     *
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    protected function composeEventItemTime(array $item): array
    {
        $date = (string) ($item['starts_date'] ?? '');
        if ($date === '') {
            $item['starts_at'] = null;

            return $item;
        }

        $hour12 = max(1, min(12, (int) ($item['time_hour'] ?? 12)));
        $minute = max(0, min(59, (int) ($item['time_minute'] ?? 0)));
        $period = strtoupper((string) ($item['time_period'] ?? 'AM')) === 'PM' ? 'PM' : 'AM';

        // 12 AM → 00, 1–11 AM → 1–11, 12 PM → 12, 1–11 PM → 13–23
        $hour24 = $hour12 % 12;
        if ($period === 'PM') {
            $hour24 += 12;
        }

        $item['time_hour'] = $hour12;
        $item['time_minute'] = $minute;
        $item['time_period'] = $period;
        $item['starts_at'] = sprintf('%s %02d:%02d:00', $date, $hour24, $minute);

        return $item;
    }
}

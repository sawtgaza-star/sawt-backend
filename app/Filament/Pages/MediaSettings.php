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
 * Filament settings for the Sawt Media site (header, footer, landing chrome).
 *
 * Stored as Setting keys (group: media). Individual services are NOT here —
 * they live in `media_services` via MediaServiceResource (صوت ميديا → خدمات ميديا).
 *
 * Navbar/footer: GET /api/v1/layout/media*
 */
class MediaSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-film';

    /** Sort after إعدادات الحاضنة (sort 2). */
    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.pages.media-settings';

    public ?array $data = [];

    public static function getNavigationGroup(): ?string
    {
        return __('Settings');
    }

    public static function getNavigationLabel(): string
    {
        return __('Media Settings');
    }

    public function getTitle(): string | \Illuminate\Contracts\Support\Htmlable
    {
        return __('Media Settings');
    }

    /**
     * key => [group, type, default]
     *
     * @return array<string, array{0: string, 1: string, 2: mixed}>
     */
    protected function fieldMeta(): array
    {
        return [
            // —— Header / chrome ——
            'media_site_name' => ['media', 'string', 'صوت ميديا'],
            'media_logo' => ['media', 'string', ''],
            'media_back_label_ar' => ['media', 'string', 'العودة لمنصة صوت'],
            'media_back_label_en' => ['media', 'string', 'Back to Sawt Platform'],
            'media_nav_start_label_ar' => ['media', 'string', 'ابدأ مشروعك'],
            'media_nav_start_label_en' => ['media', 'string', 'Start your project'],
            'media_nav_links' => ['media', 'json', [
                ['key' => 'methodology', 'label_ar' => 'منهجيتنا', 'label_en' => 'Our methodology', 'is_visible' => true],
                ['key' => 'services', 'label_ar' => 'خدماتنا', 'label_en' => 'Our services', 'is_visible' => true],
                ['key' => 'works', 'label_ar' => 'أعمالنا', 'label_en' => 'Our work', 'is_visible' => true],
                ['key' => 'about', 'label_ar' => 'عن صوت ميديا', 'label_en' => 'About Sawt Media', 'is_visible' => true],
            ]],

            // —— Footer ——
            'media_footer_logo' => ['media', 'string', ''],
            'media_footer_about_ar' => ['media', 'text', 'منصة صوت، تأسست لتكون مساحة للمبدعين، تجمع الحاضنة، صوت ميديا، والصوت نفسه، لتقديم محتوى ملهم وتجارب فريدة لكل من يسعى لصوته أن يُسمع.'],
            'media_footer_about_en' => ['media', 'text', 'Sawt platform was founded as a space for creators — bringing together the incubator, Sawt Media, and Sawt itself.'],
            'media_footer_main_title_ar' => ['media', 'string', 'الأقسام الرئيسية'],
            'media_footer_main_title_en' => ['media', 'string', 'Main Sections'],
            'media_footer_main_links' => ['media', 'json', [
                ['key' => 'home', 'label_ar' => 'الرئيسية', 'label_en' => 'Home', 'is_visible' => true],
                ['key' => 'about', 'label_ar' => 'من نحن', 'label_en' => 'About Us', 'is_visible' => true],
                ['key' => 'team', 'label_ar' => 'الفريق', 'label_en' => 'Team', 'is_visible' => true],
                ['key' => 'creators', 'label_ar' => 'صناع المحتوى', 'label_en' => 'Content Creators', 'is_visible' => true],
                ['key' => 'content', 'label_ar' => 'محتوانا', 'label_en' => 'Our Content', 'is_visible' => true],
            ]],
            'media_footer_sawt_title_ar' => ['media', 'string', 'اقسام صوت'],
            'media_footer_sawt_title_en' => ['media', 'string', 'Sawt Sections'],
            'media_footer_sawt_links' => ['media', 'json', [
                ['key' => 'platform', 'label_ar' => 'منصة صوت', 'label_en' => 'Sawt Platform', 'is_visible' => true],
                ['key' => 'incubator', 'label_ar' => 'حاضنة صوت', 'label_en' => 'Sawt Incubator', 'is_visible' => true],
                ['key' => 'media', 'label_ar' => 'صوت ميديا', 'label_en' => 'Sawt Media', 'is_visible' => true],
            ]],
            'media_footer_newsletter_title_ar' => ['media', 'string', 'ابقَ على اطلاع'],
            'media_footer_newsletter_title_en' => ['media', 'string', 'Stay Updated'],
            'media_footer_newsletter_desc_ar' => ['media', 'string', 'اشترك في نشرتنا الإخبارية ..'],
            'media_footer_newsletter_desc_en' => ['media', 'string', 'Subscribe to our newsletter..'],
            // Footer-only chrome: socials label (+ URLs/contact come from الإعدادات العامة)
            'media_socials_label_ar' => ['media', 'string', 'وسائل التواصل الاجتماعي'],
            'media_socials_label_en' => ['media', 'string', 'Social Media'],
            'media_footer_copyright_ar' => ['media', 'string', '© جميع الحقوق محفوظة. 2026'],
            'media_footer_copyright_en' => ['media', 'string', '© All rights reserved. 2026'],
            'media_footer_brand' => ['media', 'string', 'SAWTGAZA'],

            // —— Landing: Hero ——
            'media_hero_eyebrow_ar' => ['media', 'string', 'صوت ميديا تقدم'],
            'media_hero_eyebrow_en' => ['media', 'string', 'Sawt Media presents'],
            'media_hero_rotating' => ['media', 'json', [
                ['label_ar' => 'إنتاج الفيديوهات', 'label_en' => 'Video production'],
                ['label_ar' => 'التصميم الجرافيكي', 'label_en' => 'Graphic design'],
                ['label_ar' => 'التغطية والاستشارات', 'label_en' => 'Coverage & consulting'],
                ['label_ar' => 'تصميم تجربة مستخدم', 'label_en' => 'UX design'],
                ['label_ar' => 'تصميم واجهه المستخدم', 'label_en' => 'UI design'],
                ['label_ar' => 'صناعة المحتوى', 'label_en' => 'Content creation'],
            ]],
            'media_hero_desc_ar' => ['media', 'text', 'نحوّل أفكارك إلى تجارب إعلامية مؤثرة. من الاستراتيجية إلى الإنتاج والنشر — كل شيء في مكان واحد.'],
            'media_hero_desc_en' => ['media', 'text', 'We turn your ideas into impactful media experiences — from strategy to production and publishing, all in one place.'],
            'media_hero_cta_primary_ar' => ['media', 'string', 'ابدأ مشروعك'],
            'media_hero_cta_primary_en' => ['media', 'string', 'Start your project'],
            'media_hero_cta_secondary_ar' => ['media', 'string', 'تعرف على خدماتنا'],
            'media_hero_cta_secondary_en' => ['media', 'string', 'Explore our services'],
            'media_hero_badge_value' => ['media', 'string', '98%'],
            'media_hero_badge_label_ar' => ['media', 'string', 'رضا العملاء'],
            'media_hero_badge_label_en' => ['media', 'string', 'Client satisfaction'],
            // Hero collage images — free-form list (admin adds as many as needed)
            'media_hero_images' => ['media', 'json', []],

            // —— About ——
            'media_about_eyebrow_ar' => ['media', 'string', 'من نحن'],
            'media_about_eyebrow_en' => ['media', 'string', 'About us'],
            'media_about_title_ar' => ['media', 'string', 'شريكك الإعلامي المتكامل'],
            'media_about_title_en' => ['media', 'string', 'Your complete media partner'],
            'media_about_body_ar' => ['media', 'text', 'صوت ميديا وكالة إعلامية إبداعية متكاملة، تقدم حلولاً إعلامية شاملة من الاستراتيجية إلى الإنتاج والنشر. لسنا مجرد مزود خدمات — نحن شريكك الإبداعي الذي يفهم أهدافك ويعمل على تحقيقها.'],
            'media_about_body_en' => ['media', 'text', 'Sawt Media is a full creative agency offering end-to-end media solutions from strategy to production and publishing.'],
            'media_about_vision_title_ar' => ['media', 'string', 'رؤيتنا'],
            'media_about_vision_title_en' => ['media', 'string', 'Our vision'],
            'media_about_vision_ar' => ['media', 'text', 'أن تصبح منصة التقنية الأولى لإدارة معارض الكتب في العالم العربي.'],
            'media_about_vision_en' => ['media', 'text', 'To become the leading tech platform for managing book fairs in the Arab world.'],
            'media_about_mission_title_ar' => ['media', 'string', 'رسالتنا'],
            'media_about_mission_title_en' => ['media', 'string', 'Our mission'],
            'media_about_mission_ar' => ['media', 'text', 'تمكين منظمي معارض الكتب من إدارة فعالياتهم بكفاءة أعلى وتجربة أكثر.'],
            'media_about_mission_en' => ['media', 'text', 'Empower book-fair organizers to run events with higher efficiency and a better experience.'],
            // About collage is a 2×2 grid (four separate uploads — not one composite image)
            'media_about_image_1' => ['media', 'string', ''],
            'media_about_image_2' => ['media', 'string', ''],
            'media_about_image_3' => ['media', 'string', ''],
            'media_about_image_4' => ['media', 'string', ''],
            'media_about_badge_value' => ['media', 'string', '98%'],
            'media_about_badge_label_ar' => ['media', 'string', 'رضا العملاء'],
            'media_about_badge_label_en' => ['media', 'string', 'Client satisfaction'],

            // —— Stats ——
            'media_stats_eyebrow_ar' => ['media', 'string', 'صوت ميديا في ارقام'],
            'media_stats_eyebrow_en' => ['media', 'string', 'Sawt Media in numbers'],
            'media_stats_title_ar' => ['media', 'string', 'أرقام نفخر بها'],
            'media_stats_title_en' => ['media', 'string', 'Numbers we are proud of'],
            'media_stats_subtitle_ar' => ['media', 'text', 'أرقام تعكس ثقة عملائنا وجودة عملنا'],
            'media_stats_subtitle_en' => ['media', 'text', 'Numbers that reflect our clients’ trust and the quality of our work'],
            'media_stats' => ['media', 'json', [
                ['value' => '120+', 'label_ar' => 'حملة إعلامية منفذة', 'label_en' => 'Media campaigns delivered'],
                ['value' => '5+', 'label_ar' => 'سنوات خبرة', 'label_en' => 'Years of experience'],
                ['value' => '98%', 'label_ar' => 'نسبة رضا العملاء', 'label_en' => 'Client satisfaction'],
                ['value' => '50+', 'label_ar' => 'عميل سعيد', 'label_en' => 'Happy clients'],
                ['value' => '200+', 'label_ar' => 'مشروع منجز', 'label_en' => 'Projects completed'],
            ]],

            // —— Services ——
            'media_services_eyebrow_ar' => ['media', 'string', 'خدماتنا'],
            'media_services_eyebrow_en' => ['media', 'string', 'Our services'],
            'media_services_title_ar' => ['media', 'string', 'حلول إعلامية متكاملة'],
            'media_services_title_en' => ['media', 'string', 'Complete media solutions'],
            'media_services_subtitle_ar' => ['media', 'text', 'اكتشف خدماتنا خطوة بخطوة — اسحب للأسفل'],
            'media_services_subtitle_en' => ['media', 'text', 'Explore our services step by step — scroll down'],
            'media_services_cta_ar' => ['media', 'string', 'استكشف المزيد'],
            'media_services_cta_en' => ['media', 'string', 'Explore more'],
            // Shared chrome for all /media/services/{slug} pages (hero banner + bottom CTA)
            'media_service_detail_hero_ar' => ['media', 'string', 'حلول رقمية تلتقي فيها الفكرة والتجربة والأثر.'],
            'media_service_detail_hero_en' => ['media', 'string', 'Digital solutions where idea, experience, and impact meet.'],
            'media_service_detail_hero_image' => ['media', 'string', ''],
            'media_service_detail_breadcrumb_home_ar' => ['media', 'string', 'الرئيسية'],
            'media_service_detail_breadcrumb_home_en' => ['media', 'string', 'Home'],
            'media_service_detail_breadcrumb_services_ar' => ['media', 'string', 'خدماتنا'],
            'media_service_detail_breadcrumb_services_en' => ['media', 'string', 'Our services'],
            'media_service_detail_includes_title_ar' => ['media', 'string', 'ماذا تشمل الخدمة'],
            'media_service_detail_includes_title_en' => ['media', 'string', 'What’s included'],
            'media_service_detail_works_title_ar' => ['media', 'string', 'نماذج من أعمالنا'],
            'media_service_detail_works_title_en' => ['media', 'string', 'Sample work'],
            'media_service_detail_works_more_ar' => ['media', 'string', 'عرض المزيد'],
            'media_service_detail_works_more_en' => ['media', 'string', 'View more'],
            'media_service_detail_cta_title_ar' => ['media', 'string', 'فريق صوت ميديا يدعم نموك'],
            'media_service_detail_cta_title_en' => ['media', 'string', 'Sawt Media’s team supports your growth'],
            'media_service_detail_cta_body_ar' => ['media', 'text', 'نساعد الشركات على تنفيذ مشاريعها بسرعة واحترافية من خلال فريق متخصص يعمل كامتداد لفريقك باستخدام أحدث أدوات التصميم وتقنيات الذكاء الاصطناعي.'],
            'media_service_detail_cta_body_en' => ['media', 'text', 'We help companies ship projects fast and professionally — a specialized team that extends yours with modern design tools and AI.'],
            'media_service_detail_cta_label_ar' => ['media', 'string', 'احجز استشارة'],
            'media_service_detail_cta_label_en' => ['media', 'string', 'Book a consultation'],
            'media_service_detail_cta_image' => ['media', 'string', ''],
            // Service rows live in media_services table (Filament group صوت ميديا)

            // —— Why ——
            'media_why_eyebrow_ar' => ['media', 'string', 'مميزات صوت ميديا'],
            'media_why_eyebrow_en' => ['media', 'string', 'Sawt Media advantages'],
            'media_why_title_ar' => ['media', 'string', 'لماذا صوت ميديا'],
            'media_why_title_en' => ['media', 'string', 'Why Sawt Media'],
            'media_why_subtitle_ar' => ['media', 'text', 'صوت ميديا فريق يبني خبرته من حكاية أصعب القصص بمصداقية، وتوصّلها لجمهور عالمي'],
            'media_why_subtitle_en' => ['media', 'text', 'A team that tells hard stories with credibility and reaches a global audience'],
            'media_why_items' => ['media', 'json', [
                ['icon' => '', 'title_ar' => 'الالتزام بالمواعيد', 'title_en' => 'On-time delivery', 'desc_ar' => 'نسلّم في الوقت المحدد دائمًا.', 'desc_en' => 'We always deliver on schedule.'],
                ['icon' => '', 'title_ar' => 'جودة عالمية', 'title_en' => 'World-class quality', 'desc_ar' => 'معايير إنتاج احترافية في كل مشروع.', 'desc_en' => 'Professional production standards on every project.'],
                ['icon' => '', 'title_ar' => 'فريق متخصص', 'title_en' => 'Specialized team', 'desc_ar' => 'خبراء في الإنتاج والإبداع والتسويق.', 'desc_en' => 'Experts in production, creativity, and marketing.'],
                ['icon' => '', 'title_ar' => 'حلول متكاملة', 'title_en' => 'End-to-end solutions', 'desc_ar' => 'من أول فكرة حتى آخر بيكسل.', 'desc_en' => 'From first idea to final pixel.'],
                ['icon' => '', 'title_ar' => 'نتائج قابلة للقياس', 'title_en' => 'Measurable results', 'desc_ar' => 'محتوى يُقاس بالأرقام ويحقق أهدافك.', 'desc_en' => 'Content measured by numbers that hit your goals.'],
            ]],

            // —— Methodology ——
            'media_method_eyebrow_ar' => ['media', 'string', 'منهجيتنا'],
            'media_method_eyebrow_en' => ['media', 'string', 'Our methodology'],
            'media_method_title_ar' => ['media', 'string', 'رحلتنا معك'],
            'media_method_title_en' => ['media', 'string', 'Our journey with you'],
            'media_method_subtitle_ar' => ['media', 'text', 'ست خطوات واضحة تضمن لك نتيجة استثنائية في كل مرة'],
            'media_method_subtitle_en' => ['media', 'text', 'Six clear steps for an exceptional result every time'],
            'media_method_steps' => ['media', 'json', [
                ['number' => '01', 'title_ar' => 'طلب الخدمة', 'title_en' => 'Request service', 'desc_ar' => 'تتواصل معنا وتخبرنا عن فكرتك. نرد في أقل من 24 ساعة.', 'desc_en' => 'Tell us your idea — we reply within 24 hours.'],
                ['number' => '02', 'title_ar' => 'دراسة الاحتياج', 'title_en' => 'Needs study', 'desc_ar' => 'نحلل متطلباتك ونفهم جمهورك وأهدافك بعمق.', 'desc_en' => 'We analyze requirements, audience, and goals.'],
                ['number' => '03', 'title_ar' => 'إعداد الخطة', 'title_en' => 'Plan', 'desc_ar' => 'نضع خطة عمل واضحة بجدول زمني وميزانية محددة.', 'desc_en' => 'A clear plan with timeline and budget.'],
                ['number' => '04', 'title_ar' => 'التنفيذ والإنتاج', 'title_en' => 'Production', 'desc_ar' => 'ينفّذ الفريق المشروع بمعايير احترافية ومتابعة مستمرة.', 'desc_en' => 'Professional execution with continuous follow-up.'],
                ['number' => '05', 'title_ar' => 'المراجعة والتسليم', 'title_en' => 'Review & delivery', 'desc_ar' => 'نراجع العمل معك ونعدّله حتى يصل إلى الصورة التي تريدها.', 'desc_en' => 'We review and refine until it matches your vision.'],
                ['number' => '06', 'title_ar' => 'المتابعة بعد التسليم', 'title_en' => 'Aftercare', 'desc_ar' => 'نبقى معك بعد التسليم لقياس الأثر ودعم ما يحتاج تطويرًا.', 'desc_en' => 'We stay after delivery to measure impact and support improvements.'],
            ]],

            // —— Works (portfolio chrome + items) ——
            'media_works_eyebrow_ar' => ['media', 'string', 'أعمالنا'],
            'media_works_eyebrow_en' => ['media', 'string', 'Our work'],
            'media_works_title_ar' => ['media', 'string', 'أبرز أعمالنا'],
            'media_works_title_en' => ['media', 'string', 'Featured projects'],
            'media_works_subtitle_ar' => ['media', 'text', 'نستعرض أبرز مشاريعنا في الإنتاج والتصوير والتصميم والتسويق.'],
            'media_works_subtitle_en' => ['media', 'text', 'Highlighted projects in production, photography, design, and marketing.'],
            'media_works_more_ar' => ['media', 'string', 'شاهد المزيد من اعمالنا'],
            'media_works_more_en' => ['media', 'string', 'See more of our work'],
            // Work rows live in media_works (Filament: صوت ميديا → أعمال ميديا)

            // —— Audiences (من نخدم؟) ——
            'media_audiences_eyebrow_ar' => ['media', 'string', 'القطاعات'],
            'media_audiences_eyebrow_en' => ['media', 'string', 'Sectors'],
            'media_audiences_title_ar' => ['media', 'string', 'من نخدم ؟'],
            'media_audiences_title_en' => ['media', 'string', 'Who we serve'],
            'media_audiences_subtitle_ar' => ['media', 'text', 'تخصص في ثلاثة قطاعات رئيسية نفهم احتياجاتها بعمق ونُقدم حلولاً إعلامية مُصمّمة لكل منها.'],
            'media_audiences_subtitle_en' => ['media', 'text', 'Three core sectors we know deeply — with tailored media solutions for each.'],
            'media_audiences_items' => ['media', 'json', [
                [
                    'title_ar' => 'المشاريع الناشئة',
                    'title_en' => 'Startups',
                    'tagline_ar' => 'نبني معك من الصفر',
                    'tagline_en' => 'We build with you from zero',
                    'desc_ar' => 'نفهم أن كل مشروع ناشئ يحتاج هوية قوية وحضورًا يُثبت وجوده من اليوم الأول.',
                    'desc_en' => 'Every startup needs a strong identity and presence from day one.',
                    'bullets_ar' => "هوية بصرية من الصفر\nمحتوى لبناء الجمهور\nفيديو تعريفي احترافي\nحضور رقمي متكامل",
                    'bullets_en' => "Visual identity from scratch\nAudience-building content\nProfessional intro video\nFull digital presence",
                ],
                [
                    'title_ar' => 'المؤسسات',
                    'title_en' => 'Institutions',
                    'tagline_ar' => 'حضور يليق بثقلكم',
                    'tagline_en' => 'Presence that matches your weight',
                    'desc_ar' => 'المؤسسات الحكومية والمدنية والأهلية تحتاج إعلامًا يعكس مصداقيتها وقيمها.',
                    'desc_en' => 'Public and civil institutions need media that reflects credibility and values.',
                    'bullets_ar' => "تغطية وتوثيق الفعاليات\nتقارير مرئية احترافية\nهوية بصرية مؤسسية\nإعلام داخلي وخارجي",
                    'bullets_en' => "Event coverage & documentation\nProfessional visual reports\nInstitutional identity\nInternal & external media",
                ],
                [
                    'title_ar' => 'الشركات',
                    'title_en' => 'Companies',
                    'tagline_ar' => 'محتوى يحقق نتائج',
                    'tagline_en' => 'Content that drives results',
                    'desc_ar' => 'نعمل مع الشركات لتحويل أهدافها التجارية إلى محتوى مؤثر قابل للقياس.',
                    'desc_en' => 'We turn business goals into measurable, impactful content.',
                    'bullets_ar' => "حملات تسويق رقمي\nإعلانات تجارية مؤثرة\nإدارة هوية العلامة\nتصوير منتجات احترافي",
                    'bullets_en' => "Digital marketing campaigns\nImpactful commercial ads\nBrand identity management\nProduct photography",
                ],
            ]],

            // —— Partners ——
            'media_partners_eyebrow_ar' => ['media', 'string', 'صوت ميديا في ارقام'],
            'media_partners_eyebrow_en' => ['media', 'string', 'Sawt Media in numbers'],
            'media_partners_title_ar' => ['media', 'string', 'شركاء النجاح'],
            'media_partners_title_en' => ['media', 'string', 'Success partners'],
            'media_partners_subtitle_ar' => ['media', 'text', 'أرقام تعكس ثقة عملائنا وجودة عملنا'],
            'media_partners_subtitle_en' => ['media', 'text', 'Numbers that reflect our clients’ trust and quality'],
            'media_partners_logos' => ['media', 'json', [
                ['name' => 'Haramain', 'logo' => '', 'url' => ''],
                ['name' => 'IHG', 'logo' => '', 'url' => ''],
                ['name' => 'holidayme', 'logo' => '', 'url' => ''],
                ['name' => 'talabat', 'logo' => '', 'url' => ''],
                ['name' => 'Vodafone', 'logo' => '', 'url' => ''],
            ]],

            // —— Consultation CTA chrome (form submit is front + future endpoint) ——
            'media_consult_eyebrow_ar' => ['media', 'string', 'الاستشارات'],
            'media_consult_eyebrow_en' => ['media', 'string', 'Consultations'],
            'media_consult_title_ar' => ['media', 'string', 'احجز استشارتك مع خبراء صوت ميديا'],
            'media_consult_title_en' => ['media', 'string', 'Book a consultation with Sawt Media experts'],
            'media_consult_body_ar' => ['media', 'text', 'صوت ميديا وكالة إعلامية إبداعية متكاملة، تقدم حلولاً إعلامية شاملة من الاستراتيجية إلى الإنتاج والنشر.'],
            'media_consult_body_en' => ['media', 'text', 'Sawt Media is a full creative agency offering end-to-end media solutions.'],
            'media_consult_bullets_ar' => ['media', 'text', "فريق متخصص ومحترف\nحلول إعلامية متكاملة\nسرية تامة\nصناعة أثر حقيقي ومستدام"],
            'media_consult_bullets_en' => ['media', 'text', "Specialized professional team\nComplete media solutions\nFull confidentiality\nReal lasting impact"],
            'media_consult_form_title_ar' => ['media', 'string', 'احجز الأن'],
            'media_consult_form_title_en' => ['media', 'string', 'Book now'],
            'media_consult_submit_ar' => ['media', 'string', 'احجز استشارتك'],
            'media_consult_submit_en' => ['media', 'string', 'Book your consultation'],

            // —— Packages ——
            'media_packages_eyebrow_ar' => ['media', 'string', 'الباقات'],
            'media_packages_eyebrow_en' => ['media', 'string', 'Packages'],
            'media_packages_title_ar' => ['media', 'string', 'جمعنا لك الخدمات المناسبة في باقة واحدة , اختر باقتك'],
            'media_packages_title_en' => ['media', 'string', 'Bundled services — choose your package'],
            'media_packages_subtitle_ar' => ['media', 'text', 'باقات متخصصة حسب نوع الخدمة — كل باقة مصممة لتلبية احتياجات محددة بدقة.'],
            'media_packages_subtitle_en' => ['media', 'text', 'Specialized packages by service type — each built for a precise need.'],
            'media_packages_cta_ar' => ['media', 'string', 'ابدأ مشروعك'],
            'media_packages_cta_en' => ['media', 'string', 'Start your project'],
            'media_packages_items' => ['media', 'json', [
                [
                    'title_ar' => 'باقة السوشيل ميديا',
                    'title_en' => 'Social media package',
                    'tagline_ar' => 'حضور رقمي احترافي ومتكامل',
                    'tagline_en' => 'Complete professional digital presence',
                    'desc_ar' => 'كل ما تحتاجه لبناء حضور قوي على منصات التواصل.',
                    'desc_en' => 'Everything you need for a strong social presence.',
                    'features_ar' => "تصاميم سوشيل ميديا|تصاميم يومية لجميع المنصات\nإدارة الحسابات|نشر ومتابعة وتفاعل يومي\nخطة تسويقية شهرية|استراتيجية مدروسة للنمو",
                    'features_en' => "Social designs|Daily designs for all platforms\nAccount management|Daily posting & engagement\nMonthly marketing plan|Growth strategy",
                ],
                [
                    'title_ar' => 'باقة المواقع الإلكترونية',
                    'title_en' => 'Websites package',
                    'tagline_ar' => 'حضور رقمي احترافي ومتكامل',
                    'tagline_en' => 'Professional web presence',
                    'desc_ar' => 'تصميم وبرمجة مواقع إلكترونية احترافية من الصفر حتى الإطلاق.',
                    'desc_en' => 'Design and build websites from zero to launch.',
                    'features_ar' => "تصميم UI/UX|تجربة مستخدم احترافية\nبرمجة الواجهة|واجهة حديثة\nدعم ما بعد الإطلاق|صيانة ودعم فني",
                    'features_en' => "UI/UX design|Professional UX\nFront-end build|Modern interface\nPost-launch support|Maintenance",
                ],
            ]],

            // —— Testimonials ——
            'media_testimonials_eyebrow_ar' => ['media', 'string', 'اراء العملاء'],
            'media_testimonials_eyebrow_en' => ['media', 'string', 'Client reviews'],
            'media_testimonials_title_ar' => ['media', 'string', 'ماذا يقول عنّا عملاؤنا'],
            'media_testimonials_title_en' => ['media', 'string', 'What our clients say'],
            'media_testimonials_subtitle_ar' => ['media', 'text', 'باقات متخصصة حسب نوع الخدمة — كل باقة مصممة لتلبية احتياجات محددة بدقة.'],
            'media_testimonials_subtitle_en' => ['media', 'text', 'Specialized packages tailored to precise needs.'],
            'media_testimonials_items' => ['media', 'json', [
                ['name' => 'سارة القحطاني', 'role_ar' => 'مخرجة — تقنية', 'role_en' => 'Director — Tech', 'quote_ar' => 'التوجيه الذي تلقيته من المرشدين كان له تأثير كبير على مسيرتي.', 'quote_en' => 'Mentorship guidance had a huge impact on my path.', 'avatar' => ''],
                ['name' => 'خالد الحسيني', 'role_ar' => 'مدير تسويق — تجارة', 'role_en' => 'Marketing manager — Commerce', 'quote_ar' => 'التوجيه الذي تلقيته من المرشدين كان له تأثير كبير على مسيرتي.', 'quote_en' => 'Mentorship guidance had a huge impact on my path.', 'avatar' => ''],
            ]],

            // —— FAQ ——
            'media_faq_eyebrow_ar' => ['media', 'string', 'الأسئلة الشائعة'],
            'media_faq_eyebrow_en' => ['media', 'string', 'FAQ'],
            'media_faq_title_ar' => ['media', 'string', 'الأسئلة التي تدور ببالك؟'],
            'media_faq_title_en' => ['media', 'string', 'Questions on your mind?'],
            'media_faq_subtitle_ar' => ['media', 'text', 'أرقام حقيقية تعكس قوة مجتمعنا'],
            'media_faq_subtitle_en' => ['media', 'text', 'Real numbers that reflect our community’s strength'],
            'media_faq_items' => ['media', 'json', [
                ['question_ar' => 'هل يمكنني نشر أعمالي بعد التدريب؟', 'question_en' => 'Can I publish my work after training?', 'answer_ar' => 'نعم، سننهي البرنامج بمشاريع حقيقية جاهزة للنشر.', 'answer_en' => 'Yes — projects are ready to publish.'],
                ['question_ar' => 'هل البرنامج نظري أم عملي؟', 'question_en' => 'Theoretical or practical?', 'answer_ar' => 'البرنامج عملي بشكل كامل.', 'answer_en' => 'Fully practical.'],
            ]],

            // —— Contact page (/media/contact — target of ابدأ مشروعك) ——
            'media_contact_title_ar' => ['media', 'string', 'تواصل معنا'],
            'media_contact_title_en' => ['media', 'string', 'Contact us'],
            'media_contact_subtitle_ar' => ['media', 'text', 'تعرّف على صنّاع المحتوى في صوت، حيث كل قصة إلها صوت، وكل مبدع إله حكاية.'],
            'media_contact_subtitle_en' => ['media', 'text', 'Meet Sawt creators — every story has a voice, every creator has a tale.'],
            'media_contact_intro_title_ar' => ['media', 'string', 'لنبدأ العمل سويا'],
            'media_contact_intro_title_en' => ['media', 'string', "Let's work together"],
            'media_contact_intro_body_ar' => ['media', 'text', 'نحن متواجدون للاستماع والرد على جميع تساؤلاتكم لا تترددوا في التواصل معنا عبر الطرق المتاحة أدناه وسنكون سعداء بخدمتكم.'],
            'media_contact_intro_body_en' => ['media', 'text', 'We are here to listen and answer your questions — reach out through the channels below.'],
            'media_contact_wa_label_ar' => ['media', 'string', 'تواصل عبر واتساب'],
            'media_contact_wa_label_en' => ['media', 'string', 'Contact via WhatsApp'],
            'media_contact_wa_hint_ar' => ['media', 'string', 'رد فوري- متاح دائما'],
            'media_contact_wa_hint_en' => ['media', 'string', 'Instant reply — always available'],
            // Empty = fall back to support_whatsapp / contact_phone from الإعدادات العامة
            'media_contact_wa_number' => ['media', 'string', ''],
            'media_contact_email_label_ar' => ['media', 'string', 'راسلنا على البريد'],
            'media_contact_email_label_en' => ['media', 'string', 'Email us'],
            // Empty = fall back to contact_email
            'media_contact_email' => ['media', 'string', ''],
            'media_contact_trust_value' => ['media', 'string', '+150'],
            'media_contact_trust_label_ar' => ['media', 'string', 'عميل يثقون بنا'],
            'media_contact_trust_label_en' => ['media', 'string', 'clients trust us'],
        ];
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
            Forms\Components\Tabs::make('Media')->columnSpanFull()->tabs([
                Forms\Components\Tabs\Tab::make(__('الهيدر'))->icon('heroicon-o-bars-3')->schema([
                    Forms\Components\Section::make(__('هيدر صوت ميديا'))->schema([
                        Forms\Components\TextInput::make('media_site_name')->label(__('اسم الموقع'))->columnSpanFull(),
                        Forms\Components\FileUpload::make('media_logo')
                            ->label(__('شعار ميديا'))
                            ->image()->disk('public')->directory('media/branding')->imageEditor()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('media_back_label_ar')->label(__('العودة للمنصة (عربي)')),
                        Forms\Components\TextInput::make('media_back_label_en')->label('Back to platform (EN)'),
                        Forms\Components\TextInput::make('media_nav_start_label_ar')->label(__('زر ابدأ مشروعك (عربي)')),
                        Forms\Components\TextInput::make('media_nav_start_label_en')->label('Start project (EN)'),
                        Forms\Components\Repeater::make('media_nav_links')
                            ->label(__('قائمة ميديا'))
                            ->schema([
                                Forms\Components\Hidden::make('key'),
                                Forms\Components\TextInput::make('label_ar')->label(__('العنوان (عربي)'))->required(),
                                Forms\Components\TextInput::make('label_en')->label('Label (EN)'),
                                Forms\Components\Toggle::make('is_visible')->label(__('ظاهر'))->default(true)->columnSpanFull(),
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
                    Forms\Components\Section::make(__('فوتر ميديا'))->schema([
                        Forms\Components\FileUpload::make('media_footer_logo')
                            ->label(__('شعار الفوتر'))
                            ->image()->disk('public')->directory('media/branding')->imageEditor()
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('media_footer_about_ar')->label(__('نبذة (عربي)'))->rows(3),
                        Forms\Components\Textarea::make('media_footer_about_en')->label('About (EN)')->rows(3),
                        Forms\Components\TextInput::make('media_footer_main_title_ar')->label(__('عنوان الأقسام الرئيسية (عربي)')),
                        Forms\Components\TextInput::make('media_footer_main_title_en')->label('Main sections title (EN)'),
                        Forms\Components\Repeater::make('media_footer_main_links')
                            ->label(__('روابط الأقسام الرئيسية'))
                            ->schema([
                                Forms\Components\Hidden::make('key'),
                                Forms\Components\TextInput::make('label_ar')->label(__('العنوان (عربي)'))->required(),
                                Forms\Components\TextInput::make('label_en')->label('Label (EN)'),
                                Forms\Components\Toggle::make('is_visible')->label(__('ظاهر'))->default(true)->columnSpanFull(),
                            ])
                            ->columns(2)->reorderable()->collapsible()
                            ->itemLabel(fn (array $state): ?string => LocaleText::pick($state, 'label', 'رابط') ?: null)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('media_footer_sawt_title_ar')->label(__('عنوان أقسام صوت (عربي)')),
                        Forms\Components\TextInput::make('media_footer_sawt_title_en')->label('Sawt sections title (EN)'),
                        Forms\Components\Repeater::make('media_footer_sawt_links')
                            ->label(__('روابط أقسام صوت'))
                            ->schema([
                                Forms\Components\Hidden::make('key'),
                                Forms\Components\TextInput::make('label_ar')->label(__('العنوان (عربي)'))->required(),
                                Forms\Components\TextInput::make('label_en')->label('Label (EN)'),
                                Forms\Components\Toggle::make('is_visible')->label(__('ظاهر'))->default(true)->columnSpanFull(),
                            ])
                            ->columns(2)->reorderable()->collapsible()
                            ->itemLabel(fn (array $state): ?string => LocaleText::pick($state, 'label', 'رابط') ?: null)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('media_footer_newsletter_title_ar')->label(__('عنوان النشرة (عربي)')),
                        Forms\Components\TextInput::make('media_footer_newsletter_title_en')->label('Newsletter title (EN)'),
                        Forms\Components\TextInput::make('media_footer_newsletter_desc_ar')->label(__('وصف النشرة (عربي)')),
                        Forms\Components\TextInput::make('media_footer_newsletter_desc_en')->label('Newsletter desc (EN)'),
                        // Label only — URLs + phone/email come from الإعدادات العامة and appear in layout/media/footer
                        Forms\Components\TextInput::make('media_socials_label_ar')->label(__('عنوان السوشيال (عربي)')),
                        Forms\Components\TextInput::make('media_socials_label_en')->label('Socials label (EN)'),
                        Forms\Components\Placeholder::make('media_footer_socials_contact_hint')
                            ->label(__('التواصل والسوشيال'))
                            ->content(__('روابط السوشيال (Facebook, Instagram, …) ورقم الهاتف والبريد تُعدَّل من «الإعدادات العامة»، وتُرجع في API الفوتر فقط ضمن contact و socials.'))
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('media_footer_copyright_ar')->label(__('حقوق النشر (عربي)')),
                        Forms\Components\TextInput::make('media_footer_copyright_en')->label('Copyright (EN)'),
                        Forms\Components\TextInput::make('media_footer_brand')->label(__('العلامة'))->columnSpanFull(),
                    ])->columns(2),
                ]),

                Forms\Components\Tabs\Tab::make(__('الصفحة الأولى'))->icon('heroicon-o-home')->schema([
                    Forms\Components\Section::make(__('1) الهيرو'))->schema([
                        Forms\Components\TextInput::make('media_hero_eyebrow_ar')->label(__('فوق العنوان (عربي)')),
                        Forms\Components\TextInput::make('media_hero_eyebrow_en')->label('Eyebrow (EN)'),
                        // Free-form collage images (not tied to left/center/right)
                        Forms\Components\Repeater::make('media_hero_images')
                            ->label(__('صور الهيرو'))
                            ->helperText(__('أضف صوراً لكولاج الهيرو بالعدد والترتيب الذي تريده — ليست شريط العبارات.'))
                            ->schema([
                                Forms\Components\FileUpload::make('image')
                                    ->label(__('الصورة'))
                                    ->image()->disk('public')->directory('media/hero')->imageEditor()
                                    ->required()
                                    ->columnSpanFull(),
                            ])
                            ->reorderable()
                            ->addActionLabel(__('➕ صورة'))
                            ->columnSpanFull(),
                        Forms\Components\Repeater::make('media_hero_rotating')
                            ->label(__('عبارات (شريط الخدمات تحت الهيرو)'))
                            ->helperText(__('نصوص الشريط الأفقي فقط (بدون صور). صور الكولاج أعلى في «صور الهيرو».'))
                            ->schema([
                                Forms\Components\TextInput::make('label_ar')->label(__('عبارة (عربي)'))->required(),
                                Forms\Components\TextInput::make('label_en')->label('Phrase (EN)'),
                            ])
                            ->columns(2)->reorderable()
                            ->itemLabel(fn (array $state): ?string => LocaleText::pick($state, 'label', 'عبارة') ?: null)
                            ->addActionLabel(__('➕ عبارة'))
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('media_hero_desc_ar')->label(__('الوصف (عربي)'))->rows(2),
                        Forms\Components\Textarea::make('media_hero_desc_en')->label('Description (EN)')->rows(2),
                        Forms\Components\TextInput::make('media_hero_cta_primary_ar')->label(__('زر أساسي (عربي)')),
                        Forms\Components\TextInput::make('media_hero_cta_primary_en')->label('Primary CTA (EN)'),
                        Forms\Components\TextInput::make('media_hero_cta_secondary_ar')->label(__('زر ثانوي (عربي)')),
                        Forms\Components\TextInput::make('media_hero_cta_secondary_en')->label('Secondary CTA (EN)'),
                        Forms\Components\TextInput::make('media_hero_badge_value')->label(__('قيمة الشارة')),
                        Forms\Components\TextInput::make('media_hero_badge_label_ar')->label(__('تسمية الشارة (عربي)')),
                        Forms\Components\TextInput::make('media_hero_badge_label_en')->label('Badge label (EN)')->columnSpanFull(),
                    ])->columns(2),

                    Forms\Components\Section::make(__('2) من نحن'))->schema([
                        Forms\Components\TextInput::make('media_about_eyebrow_ar')->label(__('فوق العنوان (عربي)')),
                        Forms\Components\TextInput::make('media_about_eyebrow_en')->label('Eyebrow (EN)'),
                        Forms\Components\TextInput::make('media_about_title_ar')->label(__('العنوان (عربي)')),
                        Forms\Components\TextInput::make('media_about_title_en')->label('Title (EN)'),
                        Forms\Components\Textarea::make('media_about_body_ar')->label(__('النص (عربي)'))->rows(3),
                        Forms\Components\Textarea::make('media_about_body_en')->label('Body (EN)')->rows(3),
                        Forms\Components\TextInput::make('media_about_vision_title_ar')->label(__('عنوان الرؤية (عربي)')),
                        Forms\Components\TextInput::make('media_about_vision_title_en')->label('Vision title (EN)'),
                        Forms\Components\Textarea::make('media_about_vision_ar')->label(__('الرؤية (عربي)'))->rows(2),
                        Forms\Components\Textarea::make('media_about_vision_en')->label('Vision (EN)')->rows(2),
                        Forms\Components\TextInput::make('media_about_mission_title_ar')->label(__('عنوان الرسالة (عربي)')),
                        Forms\Components\TextInput::make('media_about_mission_title_en')->label('Mission title (EN)'),
                        Forms\Components\Textarea::make('media_about_mission_ar')->label(__('الرسالة (عربي)'))->rows(2),
                        Forms\Components\Textarea::make('media_about_mission_en')->label('Mission (EN)')->rows(2),
                        // Four collage slots matching the 2×2 about grid in the design
                        Forms\Components\FileUpload::make('media_about_image_1')
                            ->label(__('صورة 1 — أعلى يمين'))
                            ->image()->disk('public')->directory('media/about')->imageEditor(),
                        Forms\Components\FileUpload::make('media_about_image_2')
                            ->label(__('صورة 2 — أعلى يسار'))
                            ->image()->disk('public')->directory('media/about')->imageEditor(),
                        Forms\Components\FileUpload::make('media_about_image_3')
                            ->label(__('صورة 3 — أسفل يمين'))
                            ->image()->disk('public')->directory('media/about')->imageEditor(),
                        Forms\Components\FileUpload::make('media_about_image_4')
                            ->label(__('صورة 4 — أسفل يسار'))
                            ->image()->disk('public')->directory('media/about')->imageEditor(),
                        Forms\Components\TextInput::make('media_about_badge_value')->label(__('قيمة الشارة')),
                        Forms\Components\TextInput::make('media_about_badge_label_ar')->label(__('تسمية الشارة (عربي)')),
                        Forms\Components\TextInput::make('media_about_badge_label_en')->label('Badge label (EN)')->columnSpanFull(),
                    ])->columns(2),

                    Forms\Components\Section::make(__('3) الأرقام'))->schema([
                        Forms\Components\TextInput::make('media_stats_eyebrow_ar')->label(__('فوق العنوان (عربي)')),
                        Forms\Components\TextInput::make('media_stats_eyebrow_en')->label('Eyebrow (EN)'),
                        Forms\Components\TextInput::make('media_stats_title_ar')->label(__('العنوان (عربي)')),
                        Forms\Components\TextInput::make('media_stats_title_en')->label('Title (EN)'),
                        Forms\Components\Textarea::make('media_stats_subtitle_ar')->label(__('الوصف (عربي)'))->rows(2),
                        Forms\Components\Textarea::make('media_stats_subtitle_en')->label('Subtitle (EN)')->rows(2),
                        Forms\Components\Repeater::make('media_stats')
                            ->label(__('الأرقام'))
                            ->schema([
                                Forms\Components\TextInput::make('value')->label(__('القيمة'))->required(),
                                Forms\Components\TextInput::make('label_ar')->label(__('التسمية (عربي)')),
                                Forms\Components\TextInput::make('label_en')->label('Label (EN)'),
                            ])
                            ->columns(3)->reorderable()
                            ->itemLabel(fn (array $state): ?string => trim(($state['value'] ?? '').' '.LocaleText::pick($state, 'label')))
                            ->addActionLabel(__('➕ رقم'))
                            ->columnSpanFull(),
                    ])->columns(2),

                    Forms\Components\Section::make(__('4) الخدمات'))->schema([
                        Forms\Components\TextInput::make('media_services_eyebrow_ar')->label(__('فوق العنوان (عربي)')),
                        Forms\Components\TextInput::make('media_services_eyebrow_en')->label('Eyebrow (EN)'),
                        Forms\Components\TextInput::make('media_services_title_ar')->label(__('العنوان (عربي)')),
                        Forms\Components\TextInput::make('media_services_title_en')->label('Title (EN)'),
                        Forms\Components\Textarea::make('media_services_subtitle_ar')->label(__('الوصف (عربي)'))->rows(2),
                        Forms\Components\Textarea::make('media_services_subtitle_en')->label('Subtitle (EN)')->rows(2),
                        Forms\Components\TextInput::make('media_services_cta_ar')->label(__('نص «استكشف» (عربي)')),
                        Forms\Components\TextInput::make('media_services_cta_en')->label('Explore CTA (EN)'),
                        Forms\Components\Placeholder::make('media_services_crud_hint')
                            ->label(__('قائمة الخدمات + بانر التفاصيل'))
                            ->content(__('بطاقات الخدمات: صوت ميديا → خدمات ميديا. بانر الهيرو وبانر «احجز استشارة» أسفل صفحة الخدمة: تبويب «تفاصيل الخدمة» في هذه الصفحة.'))
                            ->columnSpanFull(),
                    ])->columns(2),

                    Forms\Components\Section::make(__('5) لماذا صوت ميديا'))->schema([
                        Forms\Components\TextInput::make('media_why_eyebrow_ar')->label(__('فوق العنوان (عربي)')),
                        Forms\Components\TextInput::make('media_why_eyebrow_en')->label('Eyebrow (EN)'),
                        Forms\Components\TextInput::make('media_why_title_ar')->label(__('العنوان (عربي)')),
                        Forms\Components\TextInput::make('media_why_title_en')->label('Title (EN)'),
                        Forms\Components\Textarea::make('media_why_subtitle_ar')->label(__('الوصف (عربي)'))->rows(2),
                        Forms\Components\Textarea::make('media_why_subtitle_en')->label('Subtitle (EN)')->rows(2),
                        Forms\Components\Repeater::make('media_why_items')
                            ->label(__('المميزات'))
                            ->schema([
                                Forms\Components\FileUpload::make('icon')->label(__('أيقونة'))->image()->disk('public')->directory('media/icons')->imageEditor()->columnSpanFull(),
                                Forms\Components\TextInput::make('title_ar')->label(__('العنوان (عربي)')),
                                Forms\Components\TextInput::make('title_en')->label('Title (EN)'),
                                Forms\Components\Textarea::make('desc_ar')->label(__('الوصف (عربي)'))->rows(2),
                                Forms\Components\Textarea::make('desc_en')->label('Description (EN)')->rows(2),
                            ])
                            ->columns(2)->reorderable()->collapsible()
                            ->itemLabel(fn (array $state): ?string => LocaleText::pick($state, 'title', 'ميزة') ?: null)
                            ->addActionLabel(__('➕ ميزة'))
                            ->columnSpanFull(),
                    ])->columns(2),

                    Forms\Components\Section::make(__('6) المنهجية'))->schema([
                        Forms\Components\TextInput::make('media_method_eyebrow_ar')->label(__('فوق العنوان (عربي)')),
                        Forms\Components\TextInput::make('media_method_eyebrow_en')->label('Eyebrow (EN)'),
                        Forms\Components\TextInput::make('media_method_title_ar')->label(__('العنوان (عربي)')),
                        Forms\Components\TextInput::make('media_method_title_en')->label('Title (EN)'),
                        Forms\Components\Textarea::make('media_method_subtitle_ar')->label(__('الوصف (عربي)'))->rows(2),
                        Forms\Components\Textarea::make('media_method_subtitle_en')->label('Subtitle (EN)')->rows(2),
                        Forms\Components\Repeater::make('media_method_steps')
                            ->label(__('الخطوات'))
                            ->schema([
                                Forms\Components\TextInput::make('number')->label(__('الرقم'))->placeholder('01'),
                                Forms\Components\TextInput::make('title_ar')->label(__('العنوان (عربي)'))->required(),
                                Forms\Components\TextInput::make('title_en')->label('Title (EN)'),
                                Forms\Components\Textarea::make('desc_ar')->label(__('الوصف (عربي)'))->rows(2)->columnSpanFull(),
                                Forms\Components\Textarea::make('desc_en')->label('Description (EN)')->rows(2)->columnSpanFull(),
                            ])
                            ->columns(3)->reorderable()->collapsible()
                            ->itemLabel(fn (array $state): ?string => trim(($state['number'] ?? '').' '.(LocaleText::pick($state, 'title', 'خطوة') ?: '')))
                            ->addActionLabel(__('➕ خطوة'))
                            ->columnSpanFull(),
                    ])->columns(2),

                    Forms\Components\Section::make(__('7) الأعمال'))->schema([
                        Forms\Components\TextInput::make('media_works_eyebrow_ar')->label(__('فوق العنوان (عربي)')),
                        Forms\Components\TextInput::make('media_works_eyebrow_en')->label('Eyebrow (EN)'),
                        Forms\Components\TextInput::make('media_works_title_ar')->label(__('العنوان (عربي)')),
                        Forms\Components\TextInput::make('media_works_title_en')->label('Title (EN)'),
                        Forms\Components\Textarea::make('media_works_subtitle_ar')->label(__('الوصف (عربي)'))->rows(2),
                        Forms\Components\Textarea::make('media_works_subtitle_en')->label('Subtitle (EN)')->rows(2),
                        Forms\Components\TextInput::make('media_works_more_ar')->label(__('نص «المزيد» (عربي)')),
                        Forms\Components\TextInput::make('media_works_more_en')->label('More label (EN)'),
                        Forms\Components\Placeholder::make('media_works_crud_hint')
                            ->label(__('قائمة الأعمال'))
                            ->content(__('الأعمال تُدار من: صوت ميديا → أعمال ميديا (جدول media_works). اربط كل عمل بخدمة ليظهر في «نماذج من أعمالنا».'))
                            ->columnSpanFull(),
                    ])->columns(2),

                    Forms\Components\Section::make(__('8) من نخدم؟'))->schema([
                        Forms\Components\TextInput::make('media_audiences_eyebrow_ar')->label(__('فوق العنوان (عربي)')),
                        Forms\Components\TextInput::make('media_audiences_eyebrow_en')->label('Eyebrow (EN)'),
                        Forms\Components\TextInput::make('media_audiences_title_ar')->label(__('العنوان (عربي)')),
                        Forms\Components\TextInput::make('media_audiences_title_en')->label('Title (EN)'),
                        Forms\Components\Textarea::make('media_audiences_subtitle_ar')->label(__('الوصف (عربي)'))->rows(2),
                        Forms\Components\Textarea::make('media_audiences_subtitle_en')->label('Subtitle (EN)')->rows(2),
                        Forms\Components\Repeater::make('media_audiences_items')
                            ->label(__('القطاعات'))
                            ->schema([
                                Forms\Components\TextInput::make('title_ar')->label(__('العنوان (عربي)'))->required(),
                                Forms\Components\TextInput::make('title_en')->label('Title (EN)'),
                                Forms\Components\TextInput::make('tagline_ar')->label(__('سطر فرعي (عربي)')),
                                Forms\Components\TextInput::make('tagline_en')->label('Tagline (EN)'),
                                Forms\Components\Textarea::make('desc_ar')->label(__('الوصف (عربي)'))->rows(2),
                                Forms\Components\Textarea::make('desc_en')->label('Description (EN)')->rows(2),
                                Forms\Components\Textarea::make('bullets_ar')->label(__('النقاط (عربي، سطر لكل نقطة)'))->rows(4),
                                Forms\Components\Textarea::make('bullets_en')->label('Bullets (EN, one per line)')->rows(4),
                            ])
                            ->columns(2)->reorderable()->collapsible()
                            ->itemLabel(fn (array $state): ?string => LocaleText::pick($state, 'title', 'قطاع') ?: null)
                            ->addActionLabel(__('➕ قطاع'))
                            ->columnSpanFull(),
                    ])->columns(2),

                    Forms\Components\Section::make(__('9) شركاء النجاح'))->schema([
                        Forms\Components\TextInput::make('media_partners_eyebrow_ar')->label(__('فوق العنوان (عربي)')),
                        Forms\Components\TextInput::make('media_partners_eyebrow_en')->label('Eyebrow (EN)'),
                        Forms\Components\TextInput::make('media_partners_title_ar')->label(__('العنوان (عربي)')),
                        Forms\Components\TextInput::make('media_partners_title_en')->label('Title (EN)'),
                        Forms\Components\Textarea::make('media_partners_subtitle_ar')->label(__('الوصف (عربي)'))->rows(2),
                        Forms\Components\Textarea::make('media_partners_subtitle_en')->label('Subtitle (EN)')->rows(2),
                        Forms\Components\Repeater::make('media_partners_logos')
                            ->label(__('الشعارات'))
                            ->schema([
                                Forms\Components\FileUpload::make('logo')->label(__('الشعار'))->image()->disk('public')->directory('media/partners')->imageEditor()->columnSpanFull(),
                                Forms\Components\TextInput::make('name')->label(__('الاسم')),
                                Forms\Components\TextInput::make('url')->label(__('رابط'))->url(),
                            ])
                            ->columns(2)->reorderable()->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['name'] ?? __('شعار'))
                            ->addActionLabel(__('➕ شعار'))
                            ->columnSpanFull(),
                    ])->columns(2),

                    Forms\Components\Section::make(__('10) احجز استشارة (نصوص النموذج)'))
                        ->description(__('إرسال النموذج من الواجهة لاحقًا — هنا نصوص القسم فقط'))
                        ->schema([
                            Forms\Components\TextInput::make('media_consult_eyebrow_ar')->label(__('فوق العنوان (عربي)')),
                            Forms\Components\TextInput::make('media_consult_eyebrow_en')->label('Eyebrow (EN)'),
                            Forms\Components\TextInput::make('media_consult_title_ar')->label(__('العنوان (عربي)')),
                            Forms\Components\TextInput::make('media_consult_title_en')->label('Title (EN)'),
                            Forms\Components\Textarea::make('media_consult_body_ar')->label(__('النص (عربي)'))->rows(3),
                            Forms\Components\Textarea::make('media_consult_body_en')->label('Body (EN)')->rows(3),
                            Forms\Components\Textarea::make('media_consult_bullets_ar')->label(__('نقاط (عربي، سطر لكل نقطة)'))->rows(4),
                            Forms\Components\Textarea::make('media_consult_bullets_en')->label('Bullets (EN)')->rows(4),
                            Forms\Components\TextInput::make('media_consult_form_title_ar')->label(__('عنوان النموذج (عربي)')),
                            Forms\Components\TextInput::make('media_consult_form_title_en')->label('Form title (EN)'),
                            Forms\Components\TextInput::make('media_consult_submit_ar')->label(__('نص زر الإرسال (عربي)')),
                            Forms\Components\TextInput::make('media_consult_submit_en')->label('Submit (EN)'),
                        ])->columns(2),

                    Forms\Components\Section::make(__('11) الباقات'))->schema([
                        Forms\Components\TextInput::make('media_packages_eyebrow_ar')->label(__('فوق العنوان (عربي)')),
                        Forms\Components\TextInput::make('media_packages_eyebrow_en')->label('Eyebrow (EN)'),
                        Forms\Components\TextInput::make('media_packages_title_ar')->label(__('العنوان (عربي)')),
                        Forms\Components\TextInput::make('media_packages_title_en')->label('Title (EN)'),
                        Forms\Components\Textarea::make('media_packages_subtitle_ar')->label(__('الوصف (عربي)'))->rows(2),
                        Forms\Components\Textarea::make('media_packages_subtitle_en')->label('Subtitle (EN)')->rows(2),
                        Forms\Components\TextInput::make('media_packages_cta_ar')->label(__('نص الزر (عربي)')),
                        Forms\Components\TextInput::make('media_packages_cta_en')->label('CTA (EN)'),
                        Forms\Components\Repeater::make('media_packages_items')
                            ->label(__('الباقات'))
                            ->schema([
                                Forms\Components\TextInput::make('title_ar')->label(__('العنوان (عربي)'))->required(),
                                Forms\Components\TextInput::make('title_en')->label('Title (EN)'),
                                Forms\Components\TextInput::make('tagline_ar')->label(__('سطر فرعي (عربي)')),
                                Forms\Components\TextInput::make('tagline_en')->label('Tagline (EN)'),
                                Forms\Components\Textarea::make('desc_ar')->label(__('الوصف (عربي)'))->rows(2),
                                Forms\Components\Textarea::make('desc_en')->label('Description (EN)')->rows(2),
                                Forms\Components\Textarea::make('features_ar')
                                    ->label(__('المميزات (عربي)'))
                                    ->helperText(__('سطر لكل ميزة: العنوان|الوصف'))
                                    ->rows(4),
                                Forms\Components\Textarea::make('features_en')
                                    ->label('Features (EN)')
                                    ->helperText('One per line: title|description')
                                    ->rows(4),
                            ])
                            ->columns(2)->reorderable()->collapsible()
                            ->itemLabel(fn (array $state): ?string => LocaleText::pick($state, 'title', 'باقة') ?: null)
                            ->addActionLabel(__('➕ باقة'))
                            ->columnSpanFull(),
                    ])->columns(2),

                    Forms\Components\Section::make(__('12) آراء العملاء'))->schema([
                        Forms\Components\TextInput::make('media_testimonials_eyebrow_ar')->label(__('فوق العنوان (عربي)')),
                        Forms\Components\TextInput::make('media_testimonials_eyebrow_en')->label('Eyebrow (EN)'),
                        Forms\Components\TextInput::make('media_testimonials_title_ar')->label(__('العنوان (عربي)')),
                        Forms\Components\TextInput::make('media_testimonials_title_en')->label('Title (EN)'),
                        Forms\Components\Textarea::make('media_testimonials_subtitle_ar')->label(__('الوصف (عربي)'))->rows(2),
                        Forms\Components\Textarea::make('media_testimonials_subtitle_en')->label('Subtitle (EN)')->rows(2),
                        Forms\Components\Repeater::make('media_testimonials_items')
                            ->label(__('الشهادات'))
                            ->schema([
                                Forms\Components\FileUpload::make('avatar')->label(__('الصورة'))->image()->disk('public')->directory('media/testimonials')->imageEditor()->columnSpanFull(),
                                Forms\Components\TextInput::make('name')->label(__('الاسم'))->required()->columnSpanFull(),
                                Forms\Components\TextInput::make('role_ar')->label(__('المسمى (عربي)')),
                                Forms\Components\TextInput::make('role_en')->label('Role (EN)'),
                                Forms\Components\Textarea::make('quote_ar')->label(__('الشهادة (عربي)'))->rows(2)->required(),
                                Forms\Components\Textarea::make('quote_en')->label('Quote (EN)')->rows(2),
                            ])
                            ->columns(2)->reorderable()->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['name'] ?? __('شهادة'))
                            ->addActionLabel(__('➕ شهادة'))
                            ->columnSpanFull(),
                    ])->columns(2),

                    Forms\Components\Section::make(__('13) الأسئلة الشائعة'))->schema([
                        Forms\Components\TextInput::make('media_faq_eyebrow_ar')->label(__('فوق العنوان (عربي)')),
                        Forms\Components\TextInput::make('media_faq_eyebrow_en')->label('Eyebrow (EN)'),
                        Forms\Components\TextInput::make('media_faq_title_ar')->label(__('العنوان (عربي)')),
                        Forms\Components\TextInput::make('media_faq_title_en')->label('Title (EN)'),
                        Forms\Components\Textarea::make('media_faq_subtitle_ar')->label(__('الوصف (عربي)'))->rows(2),
                        Forms\Components\Textarea::make('media_faq_subtitle_en')->label('Subtitle (EN)')->rows(2),
                        Forms\Components\Repeater::make('media_faq_items')
                            ->label(__('الأسئلة'))
                            ->schema([
                                Forms\Components\TextInput::make('question_ar')->label(__('السؤال (عربي)'))->required(),
                                Forms\Components\TextInput::make('question_en')->label('Question (EN)'),
                                Forms\Components\Textarea::make('answer_ar')->label(__('الجواب (عربي)'))->rows(2)->required(),
                                Forms\Components\Textarea::make('answer_en')->label('Answer (EN)')->rows(2),
                            ])
                            ->columns(2)->reorderable()->collapsible()
                            ->itemLabel(fn (array $state): ?string => LocaleText::pick($state, 'question', 'سؤال') ?: null)
                            ->addActionLabel(__('➕ سؤال'))
                            ->columnSpanFull(),
                    ])->columns(2),
                ]),

                // Contact page used when clicking ابدأ مشروعك (front route /media/contact)
                // Shared banners on every /media/services/{slug} page (hero top + CTA bottom)
                Forms\Components\Tabs\Tab::make(__('تفاصيل الخدمة'))->icon('heroicon-o-photo')->schema([
                    Forms\Components\Section::make(__('1) بانر الهيرو (أعلى الصفحة)'))
                        ->description(__('الصورة الخلفية + العنوان الكبير + مسار التنقل — يظهر على كل صفحات الخدمات.'))
                        ->schema([
                            Forms\Components\FileUpload::make('media_service_detail_hero_image')
                                ->label(__('صورة خلفية الهيرو'))
                                ->image()->disk('public')->directory('media/services/chrome')->imageEditor()
                                ->columnSpanFull(),
                            Forms\Components\TextInput::make('media_service_detail_hero_ar')->label(__('عنوان الهيرو (عربي)')),
                            Forms\Components\TextInput::make('media_service_detail_hero_en')->label('Hero title (EN)'),
                            Forms\Components\TextInput::make('media_service_detail_breadcrumb_home_ar')->label(__('مسار: الرئيسية (عربي)')),
                            Forms\Components\TextInput::make('media_service_detail_breadcrumb_home_en')->label('Breadcrumb Home (EN)'),
                            Forms\Components\TextInput::make('media_service_detail_breadcrumb_services_ar')->label(__('مسار: خدماتنا (عربي)')),
                            Forms\Components\TextInput::make('media_service_detail_breadcrumb_services_en')->label('Breadcrumb Services (EN)'),
                            Forms\Components\Placeholder::make('media_service_detail_breadcrumb_current_hint')
                                ->label(__('المسار الحالي'))
                                ->content(__('الجزء الأخير من المسار = عنوان الخدمة نفسها من صوت ميديا → خدمات ميديا.'))
                                ->columnSpanFull(),
                        ])->columns(2),

                    Forms\Components\Section::make(__('2) عناوين أقسام الصفحة'))->schema([
                        Forms\Components\TextInput::make('media_service_detail_includes_title_ar')->label(__('عنوان «ماذا تشمل» (عربي)')),
                        Forms\Components\TextInput::make('media_service_detail_includes_title_en')->label('Includes title (EN)'),
                        Forms\Components\TextInput::make('media_service_detail_works_title_ar')->label(__('عنوان نماذج الأعمال (عربي)')),
                        Forms\Components\TextInput::make('media_service_detail_works_title_en')->label('Samples title (EN)'),
                        Forms\Components\TextInput::make('media_service_detail_works_more_ar')->label(__('زر عرض المزيد (عربي)')),
                        Forms\Components\TextInput::make('media_service_detail_works_more_en')->label('View more (EN)'),
                    ])->columns(2),

                    Forms\Components\Section::make(__('3) بانر CTA السفلي («احجز استشارة»)'))
                        ->description(__('الصورة الخلفية + العنوان + النص + الزر — أسفل صفحة كل خدمة.'))
                        ->schema([
                            Forms\Components\FileUpload::make('media_service_detail_cta_image')
                                ->label(__('صورة خلفية البانر'))
                                ->image()->disk('public')->directory('media/services/chrome')->imageEditor()
                                ->columnSpanFull(),
                            Forms\Components\TextInput::make('media_service_detail_cta_title_ar')->label(__('العنوان (عربي)')),
                            Forms\Components\TextInput::make('media_service_detail_cta_title_en')->label('CTA title (EN)'),
                            Forms\Components\Textarea::make('media_service_detail_cta_body_ar')->label(__('النص (عربي)'))->rows(3),
                            Forms\Components\Textarea::make('media_service_detail_cta_body_en')->label('CTA body (EN)')->rows(3),
                            Forms\Components\TextInput::make('media_service_detail_cta_label_ar')->label(__('نص الزر (عربي)')),
                            Forms\Components\TextInput::make('media_service_detail_cta_label_en')->label('Button (EN)'),
                            Forms\Components\Placeholder::make('media_service_detail_cta_path_hint')
                                ->label(__('رابط الزر'))
                                ->content(__('الزر يفتح /media/contact (ابدأ مشروعك) — غير قابل للتعديل من هنا.'))
                                ->columnSpanFull(),
                        ])->columns(2),
                ]),

                Forms\Components\Tabs\Tab::make(__('صفحة التواصل'))->icon('heroicon-o-chat-bubble-left-right')->schema([
                    Forms\Components\Section::make(__('صفحة تواصل معنا (/media/contact)'))
                        ->description(__('زر «ابدأ مشروعك» في الهيدر والهيرو يفتح هذه الصفحة.'))
                        ->schema([
                            Forms\Components\TextInput::make('media_contact_title_ar')->label(__('العنوان (عربي)')),
                            Forms\Components\TextInput::make('media_contact_title_en')->label('Title (EN)'),
                            Forms\Components\Textarea::make('media_contact_subtitle_ar')->label(__('الوصف تحت العنوان (عربي)'))->rows(2),
                            Forms\Components\Textarea::make('media_contact_subtitle_en')->label('Subtitle (EN)')->rows(2),
                            Forms\Components\TextInput::make('media_contact_intro_title_ar')->label(__('عنوان القسم (عربي)')),
                            Forms\Components\TextInput::make('media_contact_intro_title_en')->label('Intro title (EN)'),
                            Forms\Components\Textarea::make('media_contact_intro_body_ar')->label(__('نص القسم (عربي)'))->rows(3),
                            Forms\Components\Textarea::make('media_contact_intro_body_en')->label('Intro body (EN)')->rows(3),
                            Forms\Components\TextInput::make('media_contact_wa_label_ar')->label(__('زر واتساب (عربي)')),
                            Forms\Components\TextInput::make('media_contact_wa_label_en')->label('WhatsApp label (EN)'),
                            Forms\Components\TextInput::make('media_contact_wa_hint_ar')->label(__('تلميح واتساب (عربي)')),
                            Forms\Components\TextInput::make('media_contact_wa_hint_en')->label('WhatsApp hint (EN)'),
                            Forms\Components\TextInput::make('media_contact_wa_number')
                                ->label(__('رقم واتساب (اختياري)'))
                                ->helperText(__('اتركه فارغاً لاستخدام واتساب/الهاتف من الإعدادات العامة.'))
                                ->columnSpanFull(),
                            Forms\Components\TextInput::make('media_contact_email_label_ar')->label(__('زر البريد (عربي)')),
                            Forms\Components\TextInput::make('media_contact_email_label_en')->label('Email label (EN)'),
                            Forms\Components\TextInput::make('media_contact_email')
                                ->label(__('البريد (اختياري)'))
                                ->email()
                                ->helperText(__('اتركه فارغاً لاستخدام بريد الإعدادات العامة.'))
                                ->columnSpanFull(),
                            Forms\Components\TextInput::make('media_contact_trust_value')->label(__('رقم الثقة (+150)')),
                            Forms\Components\TextInput::make('media_contact_trust_label_ar')->label(__('نص الثقة (عربي)')),
                            Forms\Components\TextInput::make('media_contact_trust_label_en')->label('Trust label (EN)')->columnSpanFull(),
                        ])->columns(2),
                ]),
            ]),
        ])->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();
        $meta = $this->fieldMeta();

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
            ->title(__('تم حفظ إعدادات ميديا بنجاح'))
            ->success()
            ->send();
    }
}

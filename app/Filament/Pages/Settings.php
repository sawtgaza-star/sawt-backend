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

            // صفحة «ادعم صوت» — النصوص الثابتة (الوسائل والباقات لها جداولها الخاصة)
            'support_header_bg' => ['support', 'string', ''],
            'support_hero_title_ar' => ['support', 'string', 'ادعم صوت'],
            'support_hero_title_en' => ['support', 'string', 'Support Sawt'],
            'support_hero_desc_ar' => ['support', 'text', 'اختر الطريقة الأنسب لك لإتمام تبرعك، وكل مساهمة تتحول إلى قصة تُروى من قلب غزة'],
            'support_hero_desc_en' => ['support', 'text', 'Choose the way that suits you best — every contribution becomes a story told from the heart of Gaza'],
            'support_methods_title_ar' => ['support', 'string', 'اختر طريقة الدعم التي تناسبك'],
            'support_methods_title_en' => ['support', 'string', 'Choose the support method that suits you'],
            'support_methods_desc_ar' => ['support', 'text', ''],
            'support_methods_desc_en' => ['support', 'text', ''],

            // بطاقات الأقسام الثلاثة
            'support_cat_electronic_enabled' => ['support', 'boolean', true],
            'support_cat_electronic_title_ar' => ['support', 'string', 'دفع إلكتروني'],
            'support_cat_electronic_title_en' => ['support', 'string', 'Electronic Payment'],
            'support_cat_electronic_desc_ar' => ['support', 'text', 'يتم التبرع باستخدام بوابة دفع آمنة وسهلة الاستخدام، بحيث يقدر المتبرع إتمام العملية بسرعة وبطريقة موثوقة.'],
            'support_cat_electronic_desc_en' => ['support', 'text', 'Donate through a secure, easy-to-use payment gateway and complete the process quickly and reliably.'],
            'support_cat_electronic_accent' => ['support', 'string', '#F97316'],

            'support_cat_transfer_enabled' => ['support', 'boolean', true],
            'support_cat_transfer_title_ar' => ['support', 'string', 'تحويل مباشر'],
            'support_cat_transfer_title_en' => ['support', 'string', 'Direct Transfer'],
            'support_cat_transfer_desc_ar' => ['support', 'text', 'يتم التبرع من خلال بيانات حساب بنكي أو محفظة إلكترونية، ثم يقوم المتبرع بإرفاق إثبات التحويل ليتم توثيق التبرع.'],
            'support_cat_transfer_desc_en' => ['support', 'text', 'Donate via a bank account or e-wallet, then attach the transfer proof so we can verify your donation.'],
            'support_cat_transfer_accent' => ['support', 'string', '#4D6B2F'],

            'support_cat_crypto_enabled' => ['support', 'boolean', true],
            'support_cat_crypto_title_ar' => ['support', 'string', 'عملات رقمية'],
            'support_cat_crypto_title_en' => ['support', 'string', 'Digital Currencies'],
            'support_cat_crypto_desc_ar' => ['support', 'text', 'يتم التبرع باستخدام عملات رقمية مدعومة، مع إمكانية إرسال إثبات العملية بعد التحويل لتأكيد المساهمة.'],
            'support_cat_crypto_desc_en' => ['support', 'text', 'Donate using supported digital currencies and send the transaction proof afterwards to confirm your contribution.'],
            'support_cat_crypto_accent' => ['support', 'string', '#4B5563'],

            // خطوات الويزارد
            'support_step_method_label_ar' => ['support', 'string', 'اختيار المنصة'],
            'support_step_method_label_en' => ['support', 'string', 'Choose platform'],
            'support_step_method_icon' => ['support', 'string', 'wallet'],
            'support_step_proof_label_ar' => ['support', 'string', 'إثبات التبرع'],
            'support_step_proof_label_en' => ['support', 'string', 'Donation proof'],
            'support_step_proof_icon' => ['support', 'string', 'badge-check'],
            'support_step_team_label_ar' => ['support', 'string', 'دعم الفريق'],
            'support_step_team_label_en' => ['support', 'string', 'Support the team'],
            'support_step_team_icon' => ['support', 'string', 'coins'],
            'support_step_contact_label_ar' => ['support', 'string', 'وسيلة التواصل'],
            'support_step_contact_label_en' => ['support', 'string', 'Contact method'],
            'support_step_contact_icon' => ['support', 'string', 'at-sign'],
            'support_step_progress_label_ar' => ['support', 'string', 'الخطوة :current من :total'],
            'support_step_progress_label_en' => ['support', 'string', 'Step :current of :total'],
            'support_step_completion_label_ar' => ['support', 'string', 'مكتمل بنسبة :percent%'],
            'support_step_completion_label_en' => ['support', 'string', ':percent% complete'],

            // الباقات والمبالغ
            'support_plans_title_ar' => ['support', 'string', 'كيف تريد أن تدعم؟'],
            'support_plans_title_en' => ['support', 'string', 'How would you like to support?'],
            'support_plans_desc_ar' => ['support', 'text', ''],
            'support_plans_desc_en' => ['support', 'text', ''],
            'support_default_interval' => ['support', 'string', 'monthly'],
            'support_default_currency' => ['support', 'string', 'USD'],
            'support_min_amount' => ['support', 'number', 5],
            'support_max_amount' => ['support', 'number', 100000],
            'support_custom_amount_enabled' => ['support', 'boolean', true],
            'support_custom_amount_label_ar' => ['support', 'string', 'أو أدخل مبلغاً'],
            'support_custom_amount_label_en' => ['support', 'string', 'Or enter an amount'],

            // نصوص الأزرار والرسائل
            'support_continue_label_ar' => ['support', 'string', 'المتابعة'],
            'support_continue_label_en' => ['support', 'string', 'Continue'],
            'support_back_label_ar' => ['support', 'string', 'رجوع'],
            'support_back_label_en' => ['support', 'string', 'Back'],
            'support_submit_label_ar' => ['support', 'string', 'إرسال'],
            'support_submit_label_en' => ['support', 'string', 'Submit'],
            'support_copy_label_ar' => ['support', 'string', 'نسخ'],
            'support_copy_label_en' => ['support', 'string', 'Copy'],
            'support_copied_label_ar' => ['support', 'string', 'تم النسخ'],
            'support_copied_label_en' => ['support', 'string', 'Copied'],
            'support_choose_method_label_ar' => ['support', 'string', 'اختر وسيلة التحويل:'],
            'support_choose_method_label_en' => ['support', 'string', 'Choose a transfer method:'],
            'support_proof_hint_ar' => ['support', 'text', 'ارفع لقطة شاشة واضحة لعملية التحويل ليتم توثيق تبرعك'],
            'support_proof_hint_en' => ['support', 'text', 'Upload a clear screenshot of the transfer so we can verify your donation'],
            'support_success_title_ar' => ['support', 'string', 'شكراً لدعمك!'],
            'support_success_title_en' => ['support', 'string', 'Thank you for your support!'],
            'support_success_message_ar' => ['support', 'text', 'استلمنا طلبك وسيقوم الفريق بمراجعة الإثبات والتواصل معك قريباً.'],
            'support_success_message_en' => ['support', 'text', 'We received your request. Our team will review the proof and reach out to you soon.'],

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
            'header_socials_label_ar' => ['header', 'string', 'وسائل التواصل الاجتماعي'],
            'header_socials_label_en' => ['header', 'string', 'Social Media'],
            'header_auth_register_label_ar' => ['header', 'string', 'أنشئ حساب'],
            'header_auth_register_label_en' => ['header', 'string', 'Create account'],
            'header_auth_login_label_ar' => ['header', 'string', 'تسجيل الدخول'],
            'header_auth_login_label_en' => ['header', 'string', 'Sign in'],
            'header_nav_links' => ['header', 'json', [
                ['key' => 'home', 'label_ar' => 'الرئيسية', 'label_en' => 'Home', 'is_visible' => true],
                ['key' => 'about', 'label_ar' => 'من نحن', 'label_en' => 'About Us', 'is_visible' => true],
                ['key' => 'content', 'label_ar' => 'محتوانا', 'label_en' => 'Our Content', 'is_visible' => true],
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
            'home_who_we_are_ar' => ['home', 'string', 'من نحن'],
            'home_who_we_are_en' => ['home', 'string', 'Who We Are'],
            'home_who_subtitle_ar' => ['home', 'string', 'إعلام هادف، قصص حقيقية، وأثر مستدام'],
            'home_who_subtitle_en' => ['home', 'string', 'Impactful media, real stories, and sustainable impact'],
            'home_welcome_lead_ar' => ['home', 'string', ''],
            'home_welcome_lead_en' => ['home', 'string', ''],
            'home_welcome_title_ar' => ['home', 'string', 'نؤمن أن لكل إنسان قصة تستحق أن تروى'],
            'home_welcome_title_en' => ['home', 'string', 'We believe every person has a story worth telling'],
            'home_welcome_desc_ar' => ['home', 'text', ''],
            'home_welcome_desc_en' => ['home', 'text', ''],
            'home_who_cta_ar' => ['home', 'string', 'اكتشف المزيد'],
            'home_who_cta_en' => ['home', 'string', 'Discover more'],
            'home_who_features' => ['home', 'json', [
                ['icon' => null, 'title_ar' => 'محتوى يعبر عن صوتك', 'title_en' => 'Content that expresses your voice'],
                ['icon' => null, 'title_ar' => 'تمكين المواهب الشابة', 'title_en' => 'Empowering young talent'],
                ['icon' => null, 'title_ar' => 'الإنتاج والتغطيات الإعلامية', 'title_en' => 'Media production and coverage'],
                ['icon' => null, 'title_ar' => 'صناعة أثر حقيقي ومستدام', 'title_en' => 'Creating real and sustainable impact'],
            ]],
            'home_hero_trust_ar' => ['home', 'string', 'ثقة آلاف المتابعين في منصة صوت غزة بصدق وتأثير'],
            'home_hero_trust_en' => ['home', 'string', 'Trusted by thousands of followers of Sawt Gaza'],
            'home_hero_btn_support_ar' => ['home', 'string', 'ادعم صوت'],
            'home_hero_btn_support_en' => ['home', 'string', 'Support Sawt'],
            'home_hero_btn_collab_ar' => ['home', 'string', 'تعاون معنا'],
            'home_hero_btn_collab_en' => ['home', 'string', 'Collaborate with us'],

            'home_news_title_ar' => ['home', 'string', 'أخر أخبارنا'],
            'home_news_title_en' => ['home', 'string', 'Our Latest News'],
            'home_news_subtitle_ar' => ['home', 'string', 'شاهد أحدث القصص والفيديوهات من منصة صوت'],
            'home_news_subtitle_en' => ['home', 'string', 'Watch the latest stories and videos from Sawt'],
            'home_news_view_all_ar' => ['home', 'string', 'عرض جميع الأخبار'],
            'home_news_view_all_en' => ['home', 'string', 'View all news'],
            'home_news_read_more_ar' => ['home', 'string', 'اقرأ المزيد'],
            'home_news_read_more_en' => ['home', 'string', 'Read more'],
            'home_news_limit' => ['home', 'number', 3],

            'home_creators_title_ar' => ['home', 'string', 'صناع المحتوى في صوت'],
            'home_creators_title_en' => ['home', 'string', 'Content Creators in Sawt'],
            'home_creators_desc_ar' => ['home', 'text', 'مجموعة من صناع المحتوى المبدعين الذين يوظفون مهاراتهم لإنتاج محتوى هادف ومؤثر.'],
            'home_creators_desc_en' => ['home', 'text', 'A group of creative content creators producing purposeful and influential content.'],
            'home_creators_view_all_ar' => ['home', 'string', 'عرض الكل'],
            'home_creators_view_all_en' => ['home', 'string', 'View all'],
            'home_creators_limit' => ['home', 'number', 10],

            'home_sections_title_ar' => ['home', 'string', 'أقسام المنصة'],
            'home_sections_title_en' => ['home', 'string', 'Platform Sections'],
            'home_sections_subtitle_ar' => ['home', 'string', 'تعرف على أذرع صوت وكيف نعمل معاً لصناعة الأثر'],
            'home_sections_subtitle_en' => ['home', 'string', 'Discover Sawt’s arms and how we work together for impact'],
            'home_platform_sections' => ['home', 'json', [
                [
                    'image' => '',
                    'icon' => '',
                    'title_ar' => 'منصة صوت',
                    'title_en' => 'Sawt Platform',
                    'desc_ar' => '',
                    'desc_en' => '',
                    'stat1_ar' => '+30 مليون مشاهدة',
                    'stat1_en' => '+30 million views',
                    'stat2_ar' => '+100 مقطع',
                    'stat2_en' => '+100 clips',
                    'cta_ar' => 'اقرأ المزيد',
                    'cta_en' => 'Read more',
                ],
                [
                    'image' => '',
                    'icon' => '',
                    'title_ar' => 'حاضنة صوت',
                    'title_en' => 'Sawt Incubator',
                    'desc_ar' => '',
                    'desc_en' => '',
                    'stat1_ar' => '+100 متدرب',
                    'stat1_en' => '+100 trainees',
                    'stat2_ar' => '+10 مشاريع',
                    'stat2_en' => '+10 projects',
                    'cta_ar' => 'اقرأ المزيد',
                    'cta_en' => 'Read more',
                ],
                [
                    'image' => '',
                    'icon' => '',
                    'title_ar' => 'صوت ميديا',
                    'title_en' => 'Sawt Media',
                    'desc_ar' => '',
                    'desc_en' => '',
                    'stat1_ar' => '+500 محتوى إبداعي',
                    'stat1_en' => '+500 creative pieces',
                    'stat2_ar' => '+100 عميل',
                    'stat2_en' => '+100 clients',
                    'cta_ar' => 'اقرأ المزيد',
                    'cta_en' => 'Read more',
                ],
            ]],

            'home_partners_title_ar' => ['home', 'string', 'شركاؤنا في صوت'],
            'home_partners_title_en' => ['home', 'string', 'Our Partners in Sawt'],
            'home_partners_subtitle_ar' => ['home', 'string', 'شركاء يشاركونا رحلة التأثير وصناعة التغيير'],
            'home_partners_subtitle_en' => ['home', 'string', 'Partners who share our journey of impact and change'],
            'home_partners' => ['home', 'json', [
                ['name' => '', 'logo' => ''],
                ['name' => '', 'logo' => ''],
                ['name' => '', 'logo' => ''],
            ]],

            'home_stories_title_ar' => ['home', 'string', 'هل لديك صوت يستحق أن يُسمع؟'],
            'home_stories_title_en' => ['home', 'string', 'Do you have a voice that deserves to be heard?'],
            'home_stories_desc_ar' => ['home', 'text', 'شاركنا قصتك أو قضيتك، وقد تكون القصة القادمة التي نسلط الضوء عليها ليصل صوتها إلى العالم'],
            'home_stories_desc_en' => ['home', 'text', 'Share your story or cause — it may be the next one we highlight so its voice reaches the world'],
            'home_stories_badge_ar' => ['home', 'string', '+100 قصة واقعية نقلتها صوت إلى العالم'],
            'home_stories_badge_en' => ['home', 'string', '+100 real stories Sawt has brought to the world'],
            'home_stories_limit' => ['home', 'number', 4],

            'home_team_title_ar' => ['home', 'string', 'أعضاء فريقنا'],
            'home_team_title_en' => ['home', 'string', 'Our Team Members'],
            'home_team_subtitle_ar' => ['home', 'string', 'تعرف على فريق صوت، مبدعين يصنعون الفرق'],
            'home_team_subtitle_en' => ['home', 'string', 'Get to know the Sawt team, creators who make a difference'],
            'home_team_cta_ar' => ['home', 'string', 'عرض الملف الشخصي'],
            'home_team_cta_en' => ['home', 'string', 'View profile'],
            'home_team_limit' => ['home', 'number', 8],

            'home_join_cta_bg' => ['home', 'string', ''],
            'home_join_cta_title_ar' => ['home', 'string', 'انضم إلينا كصانع محتوى'],
            'home_join_cta_title_en' => ['home', 'string', 'Join us as a content creator'],
            'home_join_cta_desc_ar' => ['home', 'text', 'صوت تجمع صناع المحتوى . كن صوت من لا صوت له'],
            'home_join_cta_desc_en' => ['home', 'text', 'Sawt brings together content creators. Be the voice for the voiceless'],
            'home_join_cta_button_ar' => ['home', 'string', 'طلب الانضمام'],
            'home_join_cta_button_en' => ['home', 'string', 'Request to join'],

            'home_reviews_title_ar' => ['home', 'string', 'آراؤكم في المحتوى'],
            'home_reviews_title_en' => ['home', 'string', 'Your opinions on the content'],
            'home_reviews_desc_ar' => ['home', 'text', 'نفخر بثقة جمهورنا، ونعتز بكل رأي يساهم في تطوير رسالتنا الإعلامية.'],
            'home_reviews_desc_en' => ['home', 'text', 'We take pride in our audience’s trust and value every opinion that develops our media message.'],
            'home_reviews_use_instagram' => ['home', 'boolean', true],

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

            // صفحة محتوانا
            'content_header_bg' => ['content', 'string', ''],
            'content_hero_title_ar' => ['content', 'string', 'كل فكرة إلها صوت... وصوت بيجمعهم'],
            'content_hero_title_en' => ['content', 'string', 'Every idea has a voice… and Sawt brings them together'],
            'content_hero_desc_ar' => ['content', 'text', 'نؤمن أن لكل إنسان قصة تستحق أن تُروى، لذلك جاءت صوت لتكون مساحة حرة للتعبير، حيث يلتقي الأفراد لمشاركة تجاربهم وأفكارهم بصدق.'],
            'content_hero_desc_en' => ['content', 'text', 'We believe every person has a story worth telling. Sawt is a free space for expression where people share experiences with sincerity.'],
            'content_hero_items' => ['content', 'json', []],
            'content_most_viewed_title_ar' => ['content', 'string', 'الأكثر مشاهدة'],
            'content_most_viewed_title_en' => ['content', 'string', 'Most viewed'],
            'content_most_viewed_more_ar' => ['content', 'string', 'رؤية المزيد'],
            'content_most_viewed_more_en' => ['content', 'string', 'See more'],
            'content_most_viewed_limit' => ['content', 'number', 6],

            // صفحة الأخبار / المدونة
            'blog_header_bg' => ['blogs', 'string', ''],
            'blog_hero_title_ar' => ['blogs', 'string', 'آخر الأخبار'],
            'blog_hero_title_en' => ['blogs', 'string', 'Latest News'],
            'blog_hero_desc_ar' => ['blogs', 'text', 'تابع أحدث قصص وتحديثات منصة صوت'],
            'blog_hero_desc_en' => ['blogs', 'text', 'Follow the latest stories and updates from Sawt'],

            // صفحة القصص
            'story_header_bg' => ['stories', 'string', ''],
            'story_hero_title_ar' => ['stories', 'string', 'قصص النجاح'],
            'story_hero_title_en' => ['stories', 'string', 'Success Stories'],
            'story_hero_desc_ar' => ['stories', 'text', 'قصص حقيقية من غزة نقلتها منصة صوت إلى العالم'],
            'story_hero_desc_en' => ['stories', 'text', 'Real stories from Gaza carried by Sawt to the world'],
            'story_related_title_ar' => ['stories', 'string', 'قصص ذات صلة'],
            'story_related_title_en' => ['stories', 'string', 'Related stories'],
            'story_related_subtitle_ar' => ['stories', 'text', 'قصص حقيقية من غزة نقلتها منصة صوت إلى العالم'],
            'story_related_subtitle_en' => ['stories', 'text', 'Real stories from Gaza carried by Sawt to the world'],
            'story_view_all_ar' => ['stories', 'string', 'عرض جميع القصص'],
            'story_view_all_en' => ['stories', 'string', 'View all stories'],

            // صفحة التعاون
            'collaborate_header_bg' => ['collaborate', 'string', ''],
            'collaborate_hero_title_ar' => ['collaborate', 'string', 'تعاون معنا'],
            'collaborate_hero_title_en' => ['collaborate', 'string', 'Collaborate with us'],
            'collaborate_hero_desc_ar' => ['collaborate', 'text', 'تعرّف على صناع المحتوى في صوت، حيث كل فكرة لها صوت، وكل صانع محتوى له قصة.'],
            'collaborate_hero_desc_en' => ['collaborate', 'text', 'Get to know the content creators in Sawt, where every idea has a voice, and every creator has a story.'],

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

            // صفحة صناع المحتوى
            'creators_header_bg' => ['creators', 'string', ''],
            'creators_hero_title_ar' => ['creators', 'string', 'صناع المحتوى في صوت'],
            'creators_hero_title_en' => ['creators', 'string', 'Content Creators in Sawt'],
            'creators_hero_desc_ar' => ['creators', 'text', 'تعرّف على صناع المحتوى في صوت، حيث كل فكرة لها صوت، وكل صانع محتوى له قصة.'],
            'creators_hero_desc_en' => ['creators', 'text', 'Get to know the content creators in Sawt, where every idea has a voice, and every creator has a story.'],
            'creators_grid_title_ar' => ['creators', 'string', '+47 صانع محتوى ناجح في صوت'],
            'creators_grid_title_en' => ['creators', 'string', '+47 successful content creators in Sawt'],
            'creators_grid_subtitle_ar' => ['creators', 'text', ''],
            'creators_grid_subtitle_en' => ['creators', 'text', ''],
            'creators_card_browse_label_ar' => ['creators', 'string', 'تصفح'],
            'creators_card_browse_label_en' => ['creators', 'string', 'Browse'],
            'creators_grid_limit' => ['creators', 'number', 10],
            'creators_all_per_page' => ['creators', 'number', 10],
            'creators_all_followers_suffix_ar' => ['creators', 'string', 'متابع'],
            'creators_all_followers_suffix_en' => ['creators', 'string', 'followers'],
            'creators_stats_title_ar' => ['creators', 'string', 'إنجازات صناع محتوى صوت'],
            'creators_stats_title_en' => ['creators', 'string', 'Achievements of Sawt Content Creators'],
            'creators_stats_subtitle_ar' => ['creators', 'text', 'أرقام حقيقية تعكس قوة مجتمعنا'],
            'creators_stats_subtitle_en' => ['creators', 'text', 'Real numbers reflecting the strength of our community'],
            'creators_stat_creators_label_ar' => ['creators', 'string', 'صانع محتوى نشط'],
            'creators_stat_creators_label_en' => ['creators', 'string', 'Active content creator'],
            'creators_stat_creators_value' => ['creators', 'number', 45],
            'creators_stat_collabs_label_ar' => ['creators', 'string', 'إعلان تعاوني نُفّذ'],
            'creators_stat_collabs_label_en' => ['creators', 'string', 'Collaborative ads executed'],
            'creators_stat_collabs_value' => ['creators', 'number', 500],
            'creators_stat_support_label_ar' => ['creators', 'string', 'دعم مالي وُزّع'],
            'creators_stat_support_label_en' => ['creators', 'string', 'Financial support distributed'],
            'creators_stat_support_value' => ['creators', 'number', 250000],
            'creators_stat_reach_label_ar' => ['creators', 'string', 'شخص وصلهم المحتوى'],
            'creators_stat_reach_label_en' => ['creators', 'string', 'People reached by content'],
            'creators_stat_reach_value' => ['creators', 'number', 4000000],
            'creators_join_bg' => ['creators', 'string', ''],
            'creators_join_title_ar' => ['creators', 'string', 'انضم إلينا كصانع محتوى'],
            'creators_join_title_en' => ['creators', 'string', 'Join us as a content creator'],
            'creators_join_desc_ar' => ['creators', 'text', 'صوت تجمع صناع المحتوى، كن صوت من لا صوت له'],
            'creators_join_desc_en' => ['creators', 'text', 'Sawt brings together content creators — be the voice for the voiceless'],
            'creators_join_button_text_ar' => ['creators', 'string', 'طلب الانضمام'],
            'creators_join_button_text_en' => ['creators', 'string', 'Request to join'],
            'creators_join_form_title_ar' => ['creators', 'string', 'انضم إلينا كصانع محتوى'],
            'creators_join_form_title_en' => ['creators', 'string', 'Join us as a content creator'],
            'creators_join_form_subtitle_ar' => ['creators', 'text', 'أخبرنا عن نفسك وسنتواصل معك قريباً'],
            'creators_join_form_subtitle_en' => ['creators', 'text', 'Tell us about yourself and we will contact you soon'],
            'creators_join_step_1_ar' => ['creators', 'string', 'المعلومات الشخصية'],
            'creators_join_step_1_en' => ['creators', 'string', 'Personal information'],
            'creators_join_step_2_ar' => ['creators', 'string', 'تفاصيل المحتوى'],
            'creators_join_step_2_en' => ['creators', 'string', 'Content details'],
            'creators_join_step_3_ar' => ['creators', 'string', 'مواقع التواصل'],
            'creators_join_step_3_en' => ['creators', 'string', 'Social media'],
            'creators_join_next_ar' => ['creators', 'string', 'التالي'],
            'creators_join_next_en' => ['creators', 'string', 'Next'],
            'creators_join_prev_ar' => ['creators', 'string', 'السابق'],
            'creators_join_prev_en' => ['creators', 'string', 'Previous'],
            'creators_join_cancel_ar' => ['creators', 'string', 'إلغاء'],
            'creators_join_cancel_en' => ['creators', 'string', 'Cancel'],
            'creators_join_submit_ar' => ['creators', 'string', 'تسليم الطلب'],
            'creators_join_submit_en' => ['creators', 'string', 'Submit request'],
            'creators_join_content_types' => ['creators', 'json', [
                ['key' => 'art', 'label_ar' => 'فن وإبداع', 'label_en' => 'Art & creativity'],
                ['key' => 'comedy', 'label_ar' => 'كوميدي وترفيهي', 'label_en' => 'Comedy & entertainment'],
                ['key' => 'culture', 'label_ar' => 'ثقافة وفنون', 'label_en' => 'Culture & arts'],
                ['key' => 'politics', 'label_ar' => 'سياسة', 'label_en' => 'Politics'],
                ['key' => 'tech', 'label_ar' => 'تقنية وتكنولوجيا', 'label_en' => 'Tech'],
                ['key' => 'social', 'label_ar' => 'اجتماعية', 'label_en' => 'Social'],
                ['key' => 'news', 'label_ar' => 'إخبارية وتوعوية', 'label_en' => 'News & awareness'],
                ['key' => 'health', 'label_ar' => 'صحة ولياقة', 'label_en' => 'Health & fitness'],
                ['key' => 'sports', 'label_ar' => 'رياضة وترفيه', 'label_en' => 'Sports & leisure'],
                ['key' => 'other', 'label_ar' => 'أخرى', 'label_en' => 'Other'],
            ]],
            'creators_partners_title_ar' => ['creators', 'string', 'شركات إعلانية تعاونت مع صناع محتوى صوت'],
            'creators_partners_title_en' => ['creators', 'string', 'Advertising companies that collaborated with Sawt creators'],
            'creators_partners_desc_ar' => ['creators', 'text', 'شكراً للشركات التي حملت صوت أهل غزة إلى العالم'],
            'creators_partners_desc_en' => ['creators', 'text', 'Thank you to the companies that carried the voice of Gaza to the world'],
            'creators_collab_title_ar' => ['creators', 'string', 'كيف يبدأ التعاون مع صناع محتوى صوت؟'],
            'creators_collab_title_en' => ['creators', 'string', 'How does collaboration with Sawt content creators begin?'],
            'creators_collab_desc_ar' => ['creators', 'text', 'ميديا صوت هي الجسر الذي يربط الشركات بصناع المحتوى في غزة'],
            'creators_collab_desc_en' => ['creators', 'text', 'Sawt Media is the bridge connecting companies with content creators in Gaza'],
            'creators_collab_brands_label_ar' => ['creators', 'string', 'الشركات والعلامات'],
            'creators_collab_brands_label_en' => ['creators', 'string', 'Companies and Brands'],
            'creators_collab_brands_subtitle_ar' => ['creators', 'string', 'التجارية حول العالم'],
            'creators_collab_brands_subtitle_en' => ['creators', 'string', 'Commercial brands worldwide'],
            'creators_collab_media_image' => ['creators', 'string', ''],
            'creators_collab_media_label_ar' => ['creators', 'string', 'ميديا صوت'],
            'creators_collab_media_label_en' => ['creators', 'string', 'Sawt Media'],
            'creators_collab_media_subtitle_ar' => ['creators', 'string', 'الوسيط الرسمي الموثوق'],
            'creators_collab_media_subtitle_en' => ['creators', 'string', 'The trusted official intermediary'],
            'creators_collab_creators_label_ar' => ['creators', 'string', 'صناع المحتوى'],
            'creators_collab_creators_label_en' => ['creators', 'string', 'Content Creators'],
            'creators_collab_creators_subtitle_ar' => ['creators', 'string', 'مبدعو غزة وفلسطين'],
            'creators_collab_creators_subtitle_en' => ['creators', 'string', 'Creators from Gaza and Palestine'],
            'creators_collab_steps_title_ar' => ['creators', 'string', 'خطوات التعاون'],
            'creators_collab_steps_title_en' => ['creators', 'string', 'Collaboration steps'],
            'creators_collab_step_1_ar' => ['creators', 'text', 'استعرض ملفات صناعنا وفلتر حسب التخصص والميزانية والوصول الجماهيري'],
            'creators_collab_step_1_en' => ['creators', 'text', 'Browse our creators\' profiles and filter by specialty, budget, and audience reach'],
            'creators_collab_step_2_ar' => ['creators', 'text', 'فريق صوت ميديا يتولى التنسيق الكامل بينك وبين صانع المحتوى - من التفاصيل حتى العقد'],
            'creators_collab_step_2_en' => ['creators', 'text', 'The Sawt Media team handles full coordination between you and the creator — from details to contract'],
            'creators_collab_step_3_ar' => ['creators', 'text', 'المحتوى يُنتج ويُنشر، وتحصل على تقرير تفصيلي بالنتائج والتفاعل'],
            'creators_collab_step_3_en' => ['creators', 'text', 'Content is produced and published, and you get a detailed report on results and engagement'],
            'creators_collab_cta_label_ar' => ['creators', 'string', 'تواصل مع فريق صوت للانضمام'],
            'creators_collab_cta_label_en' => ['creators', 'string', 'Contact the Sawt team to join'],
            'creators_faq_title_ar' => ['creators', 'string', 'الأسئلة التي تدور ببالك؟ إليك ردودها'],
            'creators_faq_title_en' => ['creators', 'string', 'Questions on your mind? Here are the answers'],
            'creators_faq_subtitle_ar' => ['creators', 'text', 'كل ما تحتاج معرفته قبل أن تبدأ رحلتك مع صوت'],
            'creators_faq_subtitle_en' => ['creators', 'text', 'Everything you need to know before starting your journey with Sawt'],
            'creators_faq_image' => ['creators', 'string', ''],
            'creators_bio_label_ar' => ['creators', 'string', 'نبذة عنه'],
            'creators_bio_label_en' => ['creators', 'string', 'About'],
            'creators_followers_label_ar' => ['creators', 'string', 'عدد المتابعين'],
            'creators_followers_label_en' => ['creators', 'string', 'Followers'],
            'creators_socials_label_ar' => ['creators', 'string', 'تابعنا على:'],
            'creators_socials_label_en' => ['creators', 'string', 'Follow us on:'],

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

        if (is_array($values['header_nav_links'] ?? null)) {
            $values['header_nav_links'] = $this->filterHeaderNavLinks($values['header_nav_links']);
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
                    Forms\Components\Section::make('1) الهيرو — الشرائح والأزرار')->schema([
                        Forms\Components\Repeater::make('home_hero_slides')
                            ->label('شرائح الكاروسيل')
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
                            ->addActionLabel('➕ إضافة شريحة')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('home_hero_trust_ar')->label('سطر الثقة (عربي)'),
                        Forms\Components\TextInput::make('home_hero_trust_en')->label('Trust line (English)'),
                        Forms\Components\TextInput::make('home_hero_btn_support_ar')->label('زر الدعم (عربي)'),
                        Forms\Components\TextInput::make('home_hero_btn_support_en')->label('Support button (EN)'),
                        Forms\Components\TextInput::make('home_hero_btn_collab_ar')->label('زر التعاون (عربي)'),
                        Forms\Components\TextInput::make('home_hero_btn_collab_en')->label('Collaborate button (EN)'),
                    ])->columns(2),

                    Forms\Components\Section::make('2) شريط الأرقام')->schema([
                        Forms\Components\TextInput::make('home_stat_team')->label('أعضاء الفريق'),
                        Forms\Components\TextInput::make('home_stat_stories')->label('عدد القصص'),
                        Forms\Components\TextInput::make('home_stat_views')->label('المشاهدات'),
                        Forms\Components\TextInput::make('home_stat_videos')->label('عدد الفيديوهات'),
                        Forms\Components\TextInput::make('home_stat_followers')->label('المتابعون'),
                    ])->columns(3),

                    Forms\Components\Section::make('3) من نحن (قسم الصفحة الرئيسية)')->schema([
                        Forms\Components\FileUpload::make('home_hero_image')
                            ->label('الصورة / التكوين البصري')
                            ->image()->disk('public')->directory('home')->imageEditor()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('home_who_we_are_ar')->label('عنوان القسم (عربي)'),
                        Forms\Components\TextInput::make('home_who_we_are_en')->label('Section title (EN)'),
                        Forms\Components\TextInput::make('home_who_subtitle_ar')->label('العنوان الفرعي (عربي)'),
                        Forms\Components\TextInput::make('home_who_subtitle_en')->label('Subtitle (EN)'),
                        Forms\Components\TextInput::make('home_welcome_title_ar')->label('العنوان الرئيسي (عربي)'),
                        Forms\Components\TextInput::make('home_welcome_title_en')->label('Main title (EN)'),
                        Forms\Components\Textarea::make('home_welcome_desc_ar')->label('الوصف (عربي)')->rows(4)->columnSpanFull(),
                        Forms\Components\Textarea::make('home_welcome_desc_en')->label('Description (EN)')->rows(4)->columnSpanFull(),
                        Forms\Components\TextInput::make('home_who_cta_ar')->label('نص الزر (عربي)'),
                        Forms\Components\TextInput::make('home_who_cta_en')->label('CTA (EN)'),
                        Forms\Components\Repeater::make('home_who_features')
                            ->label('المميزات الأربع')
                            ->schema([
                                Forms\Components\FileUpload::make('icon')
                                    ->label('أيقونة (صورة)')
                                    ->image()->disk('public')->directory('home/icons')
                                    ->imageEditor(),
                                Forms\Components\TextInput::make('title_ar')->label('النص (عربي)')->required(),
                                Forms\Components\TextInput::make('title_en')->label('Text (EN)'),
                            ])
                            ->columns(3)
                            ->maxItems(4)
                            ->reorderable()
                            ->columnSpanFull(),
                    ])->columns(2),

                    Forms\Components\Section::make('4) آخر الأخبار')->schema([
                        Forms\Components\TextInput::make('home_news_title_ar')->label('العنوان (عربي)'),
                        Forms\Components\TextInput::make('home_news_title_en')->label('Title (EN)'),
                        Forms\Components\TextInput::make('home_news_subtitle_ar')->label('الوصف (عربي)'),
                        Forms\Components\TextInput::make('home_news_subtitle_en')->label('Subtitle (EN)'),
                        Forms\Components\TextInput::make('home_news_read_more_ar')->label('«اقرأ المزيد» (عربي)'),
                        Forms\Components\TextInput::make('home_news_read_more_en')->label('Read more (EN)'),
                        Forms\Components\TextInput::make('home_news_view_all_ar')->label('زر عرض الكل (عربي)'),
                        Forms\Components\TextInput::make('home_news_view_all_en')->label('View all (EN)'),
                        Forms\Components\TextInput::make('home_news_limit')
                            ->label('عدد البطاقات في الرئيسية')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(12),
                    ])->columns(2)
                        ->description('البطاقات تُجلب من **المحتوى → الأخبار / المدونة**. فعّل «إبراز في الرئيسية» أو انشر أحدث الأخبار.'),

                    Forms\Components\Section::make('5) صناع المحتوى (على الرئيسية)')->schema([
                        Forms\Components\TextInput::make('home_creators_title_ar')->label('العنوان (عربي)'),
                        Forms\Components\TextInput::make('home_creators_title_en')->label('Title (EN)'),
                        Forms\Components\Textarea::make('home_creators_desc_ar')->label('الوصف (عربي)')->rows(2),
                        Forms\Components\Textarea::make('home_creators_desc_en')->label('Description (EN)')->rows(2),
                        Forms\Components\TextInput::make('home_creators_view_all_ar')->label('عرض الكل (عربي)'),
                        Forms\Components\TextInput::make('home_creators_view_all_en')->label('View all (EN)'),
                        Forms\Components\TextInput::make('home_creators_limit')->label('عدد البطاقات')->numeric()->minValue(1)->maxValue(30),
                    ])->columns(2)
                        ->description('البطاقات تُجلب من قائمة صنّاع المحتوى النشطين في لوحة التحكم.'),

                    Forms\Components\Section::make('6) أقسام المنصة')->schema([
                        Forms\Components\TextInput::make('home_sections_title_ar')->label('عنوان القسم (عربي)'),
                        Forms\Components\TextInput::make('home_sections_title_en')->label('Section title (EN)'),
                        Forms\Components\TextInput::make('home_sections_subtitle_ar')->label('الوصف (عربي)'),
                        Forms\Components\TextInput::make('home_sections_subtitle_en')->label('Subtitle (EN)'),
                        Forms\Components\Repeater::make('home_platform_sections')
                            ->label('البطاقات الثلاث')
                            ->schema([
                                Forms\Components\FileUpload::make('image')
                                    ->label('صورة البطاقة')
                                    ->helperText('الصورة الكبيرة أعلى البطاقة')
                                    ->image()
                                    ->disk('public')
                                    ->directory('home/sections')
                                    ->imageEditor()
                                    ->columnSpanFull(),
                                Forms\Components\FileUpload::make('icon')
                                    ->label('أيقونة البطاقة')
                                    ->helperText('الأيقونة الدائرية فوق الصورة')
                                    ->image()
                                    ->disk('public')
                                    ->directory('home/sections/icons')
                                    ->imageEditor(),
                                Forms\Components\TextInput::make('title_ar')->label('العنوان (عربي)'),
                                Forms\Components\TextInput::make('title_en')->label('Title (EN)'),
                                Forms\Components\Textarea::make('desc_ar')->label('الوصف (عربي)')->rows(2),
                                Forms\Components\Textarea::make('desc_en')->label('Description (EN)')->rows(2),
                                Forms\Components\TextInput::make('stat1_ar')->label('رقم 1 (عربي)'),
                                Forms\Components\TextInput::make('stat1_en')->label('Stat 1 (EN)'),
                                Forms\Components\TextInput::make('stat2_ar')->label('رقم 2 (عربي)'),
                                Forms\Components\TextInput::make('stat2_en')->label('Stat 2 (EN)'),
                                Forms\Components\TextInput::make('cta_ar')->label('نص الزر (عربي)'),
                                Forms\Components\TextInput::make('cta_en')->label('CTA (EN)'),
                            ])
                            ->columns(2)
                            ->maxItems(6)
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['title_ar'] ?? 'قسم')
                            ->columnSpanFull(),
                    ])->columns(2),

                    Forms\Components\Section::make('7) الشركاء')->schema([
                        Forms\Components\TextInput::make('home_partners_title_ar')->label('العنوان (عربي)'),
                        Forms\Components\TextInput::make('home_partners_title_en')->label('Title (EN)'),
                        Forms\Components\TextInput::make('home_partners_subtitle_ar')->label('الوصف (عربي)'),
                        Forms\Components\TextInput::make('home_partners_subtitle_en')->label('Subtitle (EN)'),
                        Forms\Components\Repeater::make('home_partners')
                            ->label('شعارات الشركاء')
                            ->schema([
                                Forms\Components\TextInput::make('name')->label('الاسم'),
                                Forms\Components\FileUpload::make('logo')->label('الشعار')->image()->disk('public')->directory('home/partners'),
                            ])
                            ->columns(2)
                            ->reorderable()
                            ->addActionLabel('➕ إضافة شريك')
                            ->columnSpanFull(),
                    ])->columns(2),

                    Forms\Components\Section::make('8) القصص + دعوة المشاركة')->schema([
                        Forms\Components\TextInput::make('home_stories_title_ar')->label('العنوان فوق الحقل (عربي)'),
                        Forms\Components\TextInput::make('home_stories_title_en')->label('Title above form (EN)'),
                        Forms\Components\Textarea::make('home_stories_desc_ar')->label('الوصف فوق الحقل (عربي)')->rows(3),
                        Forms\Components\Textarea::make('home_stories_desc_en')->label('Description above form (EN)')->rows(3),
                        Forms\Components\TextInput::make('home_stories_badge_ar')->label('شارة الإحصائية (عربي)')
                            ->helperText('مثال: +100 قصة واقعية نقلتها صوت إلى العالم'),
                        Forms\Components\TextInput::make('home_stories_badge_en')->label('Stat badge (EN)'),
                        Forms\Components\TextInput::make('home_stories_limit')
                            ->label('عدد بطاقات القصص')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(12)
                            ->helperText('القصص من **المحتوى → القصص** (المميزة أولاً)'),
                    ])->columns(2)
                        ->description('حقل «شاركنا قصتك» ثابت في الواجهة — لا يُعدَّل من هنا.'),

                    Forms\Components\Section::make('9) أعضاء الفريق (على الرئيسية)')->schema([
                        Forms\Components\TextInput::make('home_team_title_ar')->label('العنوان (عربي)'),
                        Forms\Components\TextInput::make('home_team_title_en')->label('Title (EN)'),
                        Forms\Components\TextInput::make('home_team_subtitle_ar')->label('الوصف (عربي)'),
                        Forms\Components\TextInput::make('home_team_subtitle_en')->label('Subtitle (EN)'),
                        Forms\Components\TextInput::make('home_team_cta_ar')->label('نص زر الملف (عربي)'),
                        Forms\Components\TextInput::make('home_team_cta_en')->label('Profile CTA (EN)'),
                        Forms\Components\TextInput::make('home_team_limit')->label('عدد الأعضاء')->numeric()->minValue(1)->maxValue(30),
                    ])->columns(2)
                        ->description('الأعضاء يُجلبون من مورد «أعضاء الفريق» النشطين.'),

                    Forms\Components\Section::make('10) CTA — انضم كصانع محتوى')->schema([
                        Forms\Components\FileUpload::make('home_join_cta_bg')
                            ->label('صورة الخلفية')
                            ->image()->disk('public')->directory('home/cta')->imageEditor()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('home_join_cta_title_ar')->label('العنوان (عربي)'),
                        Forms\Components\TextInput::make('home_join_cta_title_en')->label('Title (EN)'),
                        Forms\Components\Textarea::make('home_join_cta_desc_ar')->label('الوصف (عربي)')->rows(2),
                        Forms\Components\Textarea::make('home_join_cta_desc_en')->label('Description (EN)')->rows(2),
                        Forms\Components\TextInput::make('home_join_cta_button_ar')->label('نص الزر (عربي)'),
                        Forms\Components\TextInput::make('home_join_cta_button_en')->label('Button (EN)'),
                    ])->columns(2)
                        ->description('بانر «انضم إلينا كصانع محتوى» أسفل قسم الفريق على الرئيسية.'),

                    Forms\Components\Section::make('11) الآراء / ريلز إنستغرام')->schema([
                        Forms\Components\TextInput::make('home_reviews_title_ar')->label('العنوان (عربي)'),
                        Forms\Components\TextInput::make('home_reviews_title_en')->label('Title (EN)'),
                        Forms\Components\Textarea::make('home_reviews_desc_ar')->label('الوصف (عربي)')->rows(3),
                        Forms\Components\Textarea::make('home_reviews_desc_en')->label('Description (EN)')->rows(3),
                        Forms\Components\Toggle::make('home_reviews_use_instagram')
                            ->label('وضع الريلز مفعّل')
                            ->helperText('يحتاج أيضاً تفعيل «ريلز إنستغرام» مع Business ID + Access Token. عند التفعيل يُجلب آخر 3 ريلز.')
                            ->columnSpanFull(),
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
                    ])->columns(2),
                ]),

                Forms\Components\Tabs\Tab::make('محتوانا')->icon('heroicon-o-film')->schema([
                    Forms\Components\Section::make('1) الهيرو — العنوان والوصف')->schema([
                        Forms\Components\FileUpload::make('content_header_bg')
                            ->label('صورة خلفية الهيرو')
                            ->image()->disk('public')->directory('content')->imageEditor()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('content_hero_title_ar')->label('العنوان (عربي)'),
                        Forms\Components\TextInput::make('content_hero_title_en')->label('Title (EN)'),
                        Forms\Components\Textarea::make('content_hero_desc_ar')->label('الوصف (عربي)')->rows(3),
                        Forms\Components\Textarea::make('content_hero_desc_en')->label('Description (EN)')->rows(3),
                    ])->columns(2),

                    Forms\Components\Section::make('2) شرائح الهيرو (صور فقط)')->schema([
                        Forms\Components\Repeater::make('content_hero_items')
                            ->label('صور الهيرو')
                            ->schema([
                                Forms\Components\FileUpload::make('image')
                                    ->label('الصورة')
                                    ->image()->disk('public')->directory('content/hero')->imageEditor()
                                    ->helperText('اختياري — إن لم ترفع صورة تُتجاهل هذه الشريحة في الـ API'),
                            ])
                            ->default([])
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => filled($state['image'] ?? null) ? 'صورة' : 'شريحة جديدة')
                            ->addActionLabel('➕ إضافة صورة')
                            ->columnSpanFull(),
                    ]),

                    Forms\Components\Section::make('3) قسم الأكثر مشاهدة')->schema([
                        Forms\Components\TextInput::make('content_most_viewed_title_ar')->label('العنوان (عربي)'),
                        Forms\Components\TextInput::make('content_most_viewed_title_en')->label('Title (EN)'),
                        Forms\Components\TextInput::make('content_most_viewed_more_ar')->label('نص «رؤية المزيد» (عربي)'),
                        Forms\Components\TextInput::make('content_most_viewed_more_en')->label('See more (EN)'),
                        Forms\Components\TextInput::make('content_most_viewed_limit')->label('عدد الريلز')->numeric()->minValue(1)->maxValue(30),
                    ])->columns(2),
                ]),

                Forms\Components\Tabs\Tab::make('الأخبار')->icon('heroicon-o-newspaper')->schema([
                    Forms\Components\Section::make('1) هيرو صفحة قائمة الأخبار')->schema([
                        Forms\Components\FileUpload::make('blog_header_bg')
                            ->label('صورة خلفية الهيرو')
                            ->image()->disk('public')->directory('blogs')->imageEditor()
                            ->helperText('اتركه فارغاً لاستخدام الصورة الافتراضية')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('blog_hero_title_ar')->label('العنوان (عربي)'),
                        Forms\Components\TextInput::make('blog_hero_title_en')->label('Title (EN)'),
                        Forms\Components\Textarea::make('blog_hero_desc_ar')->label('الوصف (عربي)')->rows(3),
                        Forms\Components\Textarea::make('blog_hero_desc_en')->label('Description (EN)')->rows(3),
                    ])->columns(2),
                ]),

                Forms\Components\Tabs\Tab::make('القصص')->icon('heroicon-o-book-open')->schema([
                    Forms\Components\Section::make('1) هيرو صفحة قائمة القصص')->schema([
                        Forms\Components\FileUpload::make('story_header_bg')
                            ->label('صورة خلفية الهيرو')
                            ->image()->disk('public')->directory('stories')->imageEditor()
                            ->helperText('اتركه فارغاً لاستخدام الصورة الافتراضية')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('story_hero_title_ar')->label('العنوان (عربي)'),
                        Forms\Components\TextInput::make('story_hero_title_en')->label('Title (EN)'),
                        Forms\Components\Textarea::make('story_hero_desc_ar')->label('الوصف (عربي)')->rows(3),
                        Forms\Components\Textarea::make('story_hero_desc_en')->label('Description (EN)')->rows(3),
                    ])->columns(2),

                    Forms\Components\Section::make('2) صفحة تفاصيل القصة')->schema([
                        Forms\Components\TextInput::make('story_related_title_ar')->label('عنوان «قصص ذات صلة» (عربي)'),
                        Forms\Components\TextInput::make('story_related_title_en')->label('Related stories title (EN)'),
                        Forms\Components\Textarea::make('story_related_subtitle_ar')->label('وصف «قصص ذات صلة» (عربي)')->rows(2),
                        Forms\Components\Textarea::make('story_related_subtitle_en')->label('Related stories subtitle (EN)')->rows(2),
                        Forms\Components\TextInput::make('story_view_all_ar')->label('«عرض جميع القصص» (عربي)'),
                        Forms\Components\TextInput::make('story_view_all_en')->label('View all stories (EN)'),
                    ])->columns(2),
                ]),

                Forms\Components\Tabs\Tab::make('التعاون')->icon('heroicon-o-hand-raised')->schema([
                    Forms\Components\Section::make('1) هيرو صفحة التعاون')->schema([
                        Forms\Components\FileUpload::make('collaborate_header_bg')
                            ->label('صورة خلفية الهيرو')
                            ->image()->disk('public')->directory('collaborate')->imageEditor()
                            ->helperText('اتركه فارغاً لاستخدام الصورة الافتراضية')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('collaborate_hero_title_ar')->label('العنوان (عربي)'),
                        Forms\Components\TextInput::make('collaborate_hero_title_en')->label('Title (EN)'),
                        Forms\Components\Textarea::make('collaborate_hero_desc_ar')->label('الوصف (عربي)')->rows(3),
                        Forms\Components\Textarea::make('collaborate_hero_desc_en')->label('Description (EN)')->rows(3),
                    ])->columns(2),

                    Forms\Components\Section::make('2) بطاقات أنواع التعاون')->schema([
                        Forms\Components\Placeholder::make('collaborate_types_hint')
                            ->content('إدارة البطاقات (صانع محتوى، رعاية، شراكة…) من **التعاون → أنواع التعاون** في الشريط الجانبي.')
                            ->columnSpanFull(),
                    ]),
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

                Forms\Components\Tabs\Tab::make('صناع المحتوى')->icon('heroicon-o-microphone')->schema([
                    Forms\Components\Section::make('1) الهيرو')->schema([
                        Forms\Components\FileUpload::make('creators_header_bg')
                            ->label('صورة خلفية الهيرو')
                            ->image()->disk('public')->directory('creators')->imageEditor()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('creators_hero_title_ar')->label('العنوان (عربي)'),
                        Forms\Components\TextInput::make('creators_hero_title_en')->label('Title (English)'),
                        Forms\Components\Textarea::make('creators_hero_desc_ar')->label('الوصف (عربي)')->rows(3),
                        Forms\Components\Textarea::make('creators_hero_desc_en')->label('Description (English)')->rows(3),
                    ])->columns(2),

                    Forms\Components\Section::make('2) شبكة صناع المحتوى')->schema([
                        Forms\Components\TextInput::make('creators_grid_title_ar')->label('عنوان القسم (عربي)'),
                        Forms\Components\TextInput::make('creators_grid_title_en')->label('Section title (English)'),
                        Forms\Components\Textarea::make('creators_grid_subtitle_ar')->label('الوصف الفرعي (عربي)')->rows(2),
                        Forms\Components\Textarea::make('creators_grid_subtitle_en')->label('Subtitle (English)')->rows(2),
                        Forms\Components\TextInput::make('creators_card_browse_label_ar')->label('نص «تصفح» (عربي)'),
                        Forms\Components\TextInput::make('creators_card_browse_label_en')->label('Browse label (English)'),
                        Forms\Components\TextInput::make('creators_grid_limit')->label('عدد البطاقات في الصفحة الرئيسية')->numeric()->minValue(1)->default(10),
                        Forms\Components\Placeholder::make('creators_grid_hint')
                            ->content('إدارة صناع المحتوى من **صنّاع المحتوى → Content Creators** في الشريط الجانبي.')
                            ->columnSpanFull(),
                    ])->columns(2),

                    Forms\Components\Section::make('صفحة عرض الكل')->schema([
                        Forms\Components\TextInput::make('creators_all_per_page')
                            ->label('عدد البطاقات في كل صفحة')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(50)
                            ->default(10)
                            ->helperText('صفحة /creators/all — الترقيم أسفل الشبكة'),
                        Forms\Components\TextInput::make('creators_all_followers_suffix_ar')->label('لاحقة المتابعين (عربي)')->placeholder('متابع'),
                        Forms\Components\TextInput::make('creators_all_followers_suffix_en')->label('Followers suffix (English)')->placeholder('followers'),
                        Forms\Components\Placeholder::make('creators_all_hint')
                            ->content('البطاقات نفسها تُدار من **صنّاع المحتوى → Content Creators** (الصورة، الاسم، التخصص، عدد المتابعين، الترتيب، الحالة).')
                            ->columnSpanFull(),
                    ])->columns(2),

                    Forms\Components\Section::make('3) الإحصائيات')->schema([
                        Forms\Components\TextInput::make('creators_stats_title_ar')->label('عنوان القسم (عربي)'),
                        Forms\Components\TextInput::make('creators_stats_title_en')->label('Section title (English)'),
                        Forms\Components\Textarea::make('creators_stats_subtitle_ar')->label('الوصف الفرعي (عربي)')->rows(2),
                        Forms\Components\Textarea::make('creators_stats_subtitle_en')->label('Subtitle (English)')->rows(2),

                        Forms\Components\Section::make('① صانع محتوى نشط')->schema([
                            Forms\Components\TextInput::make('creators_stat_creators_value')
                                ->label('الرقم')
                                ->numeric()
                                ->helperText('مثال: 45 يظهر كـ 45+'),
                            Forms\Components\TextInput::make('creators_stat_creators_label_ar')->label('التسمية (عربي)'),
                            Forms\Components\TextInput::make('creators_stat_creators_label_en')->label('Label (English)'),
                        ])->columns(3)->compact(),

                        Forms\Components\Section::make('② إعلان تعاوني نُفّذ')->schema([
                            Forms\Components\TextInput::make('creators_stat_collabs_value')
                                ->label('الرقم')
                                ->numeric()
                                ->helperText('مثال: 500 يظهر كـ 500+'),
                            Forms\Components\TextInput::make('creators_stat_collabs_label_ar')->label('التسمية (عربي)'),
                            Forms\Components\TextInput::make('creators_stat_collabs_label_en')->label('Label (English)'),
                        ])->columns(3)->compact(),

                        Forms\Components\Section::make('③ دعم مالي وُزّع')->schema([
                            Forms\Components\TextInput::make('creators_stat_support_value')
                                ->label('الرقم')
                                ->numeric()
                                ->helperText('مثال: 250000 يظهر كـ +$250K'),
                            Forms\Components\TextInput::make('creators_stat_support_label_ar')->label('التسمية (عربي)'),
                            Forms\Components\TextInput::make('creators_stat_support_label_en')->label('Label (English)'),
                        ])->columns(3)->compact(),

                        Forms\Components\Section::make('④ شخص وصلهم المحتوى')->schema([
                            Forms\Components\TextInput::make('creators_stat_reach_value')
                                ->label('الرقم')
                                ->numeric()
                                ->helperText('مثال: 4000000 يظهر كـ 4M+'),
                            Forms\Components\TextInput::make('creators_stat_reach_label_ar')->label('التسمية (عربي)'),
                            Forms\Components\TextInput::make('creators_stat_reach_label_en')->label('Label (English)'),
                        ])->columns(3)->compact(),
                    ])->columns(2),

                    Forms\Components\Section::make('4) دعوة الانضمام (CTA)')->schema([
                        Forms\Components\FileUpload::make('creators_join_bg')
                            ->label('صورة الزر / الخلفية')
                            ->image()->disk('public')->directory('creators')->imageEditor()
                            ->helperText('هذه الصورة هي زر «انضم إلينا كصانع محتوى»')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('creators_join_title_ar')->label('العنوان (عربي)'),
                        Forms\Components\TextInput::make('creators_join_title_en')->label('Title (English)'),
                        Forms\Components\Textarea::make('creators_join_desc_ar')->label('الوصف (عربي)')->rows(3),
                        Forms\Components\Textarea::make('creators_join_desc_en')->label('Description (English)')->rows(3),
                        Forms\Components\TextInput::make('creators_join_button_text_ar')->label('نص الزر (عربي)'),
                        Forms\Components\TextInput::make('creators_join_button_text_en')->label('Button text (English)'),
                    ])->columns(2),

                    Forms\Components\Section::make('نموذج الانضمام (3 خطوات)')->schema([
                        Forms\Components\TextInput::make('creators_join_form_title_ar')->label('عنوان النموذج (عربي)'),
                        Forms\Components\TextInput::make('creators_join_form_title_en')->label('Form title (English)'),
                        Forms\Components\Textarea::make('creators_join_form_subtitle_ar')->label('الوصف الفرعي (عربي)')->rows(2),
                        Forms\Components\Textarea::make('creators_join_form_subtitle_en')->label('Subtitle (English)')->rows(2),
                        Forms\Components\TextInput::make('creators_join_step_1_ar')->label('الخطوة 1 (عربي)'),
                        Forms\Components\TextInput::make('creators_join_step_1_en')->label('Step 1 (English)'),
                        Forms\Components\TextInput::make('creators_join_step_2_ar')->label('الخطوة 2 (عربي)'),
                        Forms\Components\TextInput::make('creators_join_step_2_en')->label('Step 2 (English)'),
                        Forms\Components\TextInput::make('creators_join_step_3_ar')->label('الخطوة 3 (عربي)'),
                        Forms\Components\TextInput::make('creators_join_step_3_en')->label('Step 3 (English)'),
                        Forms\Components\TextInput::make('creators_join_next_ar')->label('نص «التالي» (عربي)'),
                        Forms\Components\TextInput::make('creators_join_next_en')->label('Next (English)'),
                        Forms\Components\TextInput::make('creators_join_prev_ar')->label('نص «السابق» (عربي)'),
                        Forms\Components\TextInput::make('creators_join_prev_en')->label('Previous (English)'),
                        Forms\Components\TextInput::make('creators_join_cancel_ar')->label('نص «إلغاء» (عربي)'),
                        Forms\Components\TextInput::make('creators_join_cancel_en')->label('Cancel (English)'),
                        Forms\Components\TextInput::make('creators_join_submit_ar')->label('نص «تسليم الطلب» (عربي)'),
                        Forms\Components\TextInput::make('creators_join_submit_en')->label('Submit (English)'),
                        Forms\Components\Repeater::make('creators_join_content_types')
                            ->label('أنواع المحتوى (الخطوة 2)')
                            ->schema([
                                Forms\Components\TextInput::make('key')->label('المفتاح')->required()->maxLength(80),
                                Forms\Components\TextInput::make('label_ar')->label('التسمية (عربي)')->required(),
                                Forms\Components\TextInput::make('label_en')->label('Label (English)'),
                            ])
                            ->columns(3)
                            ->reorderable()
                            ->collapsible()
                            ->columnSpanFull(),
                    ])->columns(2),

                    Forms\Components\Section::make('5) الشركات الشريكة')->schema([
                        Forms\Components\TextInput::make('creators_partners_title_ar')->label('عنوان القسم (عربي)'),
                        Forms\Components\TextInput::make('creators_partners_title_en')->label('Section title (English)'),
                        Forms\Components\Textarea::make('creators_partners_desc_ar')->label('الوصف (عربي)')->rows(2),
                        Forms\Components\Textarea::make('creators_partners_desc_en')->label('Description (English)')->rows(2),
                        Forms\Components\Placeholder::make('creators_partners_hint')
                            ->content('إدارة الشركات من **صنّاع المحتوى → الشركات الشريكة** في الشريط الجانبي.')
                            ->columnSpanFull(),
                    ])->columns(2),

                    Forms\Components\Section::make('6) خطوات التعاون')->schema([
                        Forms\Components\TextInput::make('creators_collab_title_ar')->label('عنوان القسم (عربي)'),
                        Forms\Components\TextInput::make('creators_collab_title_en')->label('Section title (English)'),
                        Forms\Components\Textarea::make('creators_collab_desc_ar')->label('الوصف (عربي)')->rows(2),
                        Forms\Components\Textarea::make('creators_collab_desc_en')->label('Description (English)')->rows(2),

                        Forms\Components\Section::make('مخطط التعاون — اليمين: صناع المحتوى (نص)')->schema([
                            Forms\Components\TextInput::make('creators_collab_creators_label_ar')->label('العنوان (عربي)'),
                            Forms\Components\TextInput::make('creators_collab_creators_label_en')->label('Title (English)'),
                            Forms\Components\TextInput::make('creators_collab_creators_subtitle_ar')->label('الوصف الفرعي (عربي)'),
                            Forms\Components\TextInput::make('creators_collab_creators_subtitle_en')->label('Subtitle (English)'),
                        ])->columns(2)->compact(),

                        Forms\Components\Section::make('مخطط التعاون — الوسط: ميديا صوت (صورة)')->schema([
                            Forms\Components\FileUpload::make('creators_collab_media_image')
                                ->label('صورة / شعار الوسط')
                                ->image()
                                ->disk('public')
                                ->directory('creators/collab')
                                ->imageEditor()
                                ->helperText('الصورة في الدائرة الوسطى (ميديا صوت)')
                                ->columnSpanFull(),
                            Forms\Components\TextInput::make('creators_collab_media_label_ar')->label('العنوان تحت الصورة (عربي)'),
                            Forms\Components\TextInput::make('creators_collab_media_label_en')->label('Title under image (English)'),
                            Forms\Components\TextInput::make('creators_collab_media_subtitle_ar')->label('شارة الوسط (عربي)'),
                            Forms\Components\TextInput::make('creators_collab_media_subtitle_en')->label('Badge text (English)'),
                        ])->columns(2)->compact(),

                        Forms\Components\Section::make('مخطط التعاون — اليسار: الشركات (نص)')->schema([
                            Forms\Components\TextInput::make('creators_collab_brands_label_ar')->label('العنوان (عربي)'),
                            Forms\Components\TextInput::make('creators_collab_brands_label_en')->label('Title (English)'),
                            Forms\Components\TextInput::make('creators_collab_brands_subtitle_ar')->label('الوصف الفرعي (عربي)'),
                            Forms\Components\TextInput::make('creators_collab_brands_subtitle_en')->label('Subtitle (English)'),
                        ])->columns(2)->compact(),

                        Forms\Components\Section::make('عنوان خطوات التعاون')->schema([
                            Forms\Components\TextInput::make('creators_collab_steps_title_ar')->label('العنوان (عربي)'),
                            Forms\Components\TextInput::make('creators_collab_steps_title_en')->label('Title (English)'),
                        ])->columns(2)->compact(),

                        Forms\Components\Section::make('الخطوة 01')->schema([
                            Forms\Components\Textarea::make('creators_collab_step_1_ar')->label('النص (عربي)')->rows(3),
                            Forms\Components\Textarea::make('creators_collab_step_1_en')->label('Text (English)')->rows(3),
                        ])->columns(2)->compact(),

                        Forms\Components\Section::make('الخطوة 02')->schema([
                            Forms\Components\Textarea::make('creators_collab_step_2_ar')->label('النص (عربي)')->rows(3),
                            Forms\Components\Textarea::make('creators_collab_step_2_en')->label('Text (English)')->rows(3),
                        ])->columns(2)->compact(),

                        Forms\Components\Section::make('الخطوة 03')->schema([
                            Forms\Components\Textarea::make('creators_collab_step_3_ar')->label('النص (عربي)')->rows(3),
                            Forms\Components\Textarea::make('creators_collab_step_3_en')->label('Text (English)')->rows(3),
                        ])->columns(2)->compact(),

                        Forms\Components\Section::make('زر التواصل')->schema([
                            Forms\Components\TextInput::make('creators_collab_cta_label_ar')->label('نص الزر (عربي)'),
                            Forms\Components\TextInput::make('creators_collab_cta_label_en')->label('Button text (English)'),
                        ])->columns(2)->compact(),
                    ])->columns(2),

                    Forms\Components\Section::make('7) الأسئلة الشائعة')->schema([
                        Forms\Components\TextInput::make('creators_faq_title_ar')->label('عنوان القسم (عربي)'),
                        Forms\Components\TextInput::make('creators_faq_title_en')->label('Section title (English)'),
                        Forms\Components\Textarea::make('creators_faq_subtitle_ar')->label('الوصف الفرعي (عربي)')->rows(2),
                        Forms\Components\Textarea::make('creators_faq_subtitle_en')->label('Subtitle (English)')->rows(2),
                        Forms\Components\FileUpload::make('creators_faq_image')
                            ->label('صورة جانبية')
                            ->image()->disk('public')->directory('creators')->imageEditor()
                            ->columnSpanFull(),
                        Forms\Components\Placeholder::make('creators_faq_hint')
                            ->content('إدارة الأسئلة من **صنّاع المحتوى → الأسئلة الشائعة** في الشريط الجانبي.')
                            ->columnSpanFull(),
                    ])->columns(2),

                    Forms\Components\Section::make('8) صفحة تفاصيل صانع المحتوى')->schema([
                        Forms\Components\TextInput::make('creators_bio_label_ar')->label('عنوان النبذة (عربي)'),
                        Forms\Components\TextInput::make('creators_bio_label_en')->label('Bio heading (English)'),
                        Forms\Components\TextInput::make('creators_followers_label_ar')->label('عنوان المتابعين (عربي)'),
                        Forms\Components\TextInput::make('creators_followers_label_en')->label('Followers label (English)'),
                        Forms\Components\TextInput::make('creators_socials_label_ar')->label('عنوان التواصل (عربي)'),
                        Forms\Components\TextInput::make('creators_socials_label_en')->label('Socials label (English)'),
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

                    Forms\Components\Section::make('الشريط العلوي')
                        ->description('الصف العلوي: زر ادعم صوت، تسجيل الدخول، البحث، وتبديل اللغة')
                        ->schema([
                            Forms\Components\TextInput::make('header_socials_label_ar')
                                ->label('عنوان السوشيال (عربي)'),
                            Forms\Components\TextInput::make('header_socials_label_en')
                                ->label('Social label (English)'),
                            Forms\Components\TextInput::make('header_auth_register_label_ar')
                                ->label('زر أنشئ حساب (عربي)'),
                            Forms\Components\TextInput::make('header_auth_register_label_en')
                                ->label('Register button (English)'),
                            Forms\Components\TextInput::make('header_auth_login_label_ar')
                                ->label('زر تسجيل الدخول (عربي)'),
                            Forms\Components\TextInput::make('header_auth_login_label_en')
                                ->label('Sign in button (English)'),
                            Forms\Components\Placeholder::make('header_social_hint')
                                ->label('')
                                ->content('روابط السوشيال ميديا تُعدّل من تبويب «التواصل الاجتماعي» وتُرجع في API ضمن topbar.socials.')
                                ->columnSpanFull(),
                        ])->columns(2),

                    Forms\Components\Section::make('القائمة')
                        ->description('«ادعم صوت» يظهر في الشريط العلوي. «حاضنة صوت» و«صوت ميديا» يظهران يسار الشعار. باقي العناصر في القائمة الرئيسية.')
                        ->schema([
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
                        ]),
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

                    Forms\Components\Section::make('معلومات التواصل')
                        ->schema([
                            Forms\Components\TextInput::make('contact_phone')->label('هاتف التواصل'),
                            Forms\Components\TextInput::make('contact_email')->label('بريد التواصل')->email(),
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

                Forms\Components\Tabs\Tab::make('صفحة الدعم')->icon('heroicon-o-heart')->schema([
                    Forms\Components\Placeholder::make('support_hint')
                        ->label('')
                        ->content('وسائل الدعم (البنوك، فودافون كاش، العملات الرقمية…) والباقات تُدار من «وسائل الدعم» و«باقات الدعم» بقائمة المالية. هنا نصوص الصفحة فقط.')
                        ->columnSpanFull(),

                    Forms\Components\Section::make('الهيدر')->schema([
                        Forms\Components\FileUpload::make('support_header_bg')
                            ->label('خلفية الهيدر')
                            ->image()->disk('public')->directory('support')->visibility('public')
                            ->imageEditor()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('support_hero_title_ar')->label('العنوان (عربي)'),
                        Forms\Components\TextInput::make('support_hero_title_en')->label('Title (English)'),
                        Forms\Components\Textarea::make('support_hero_desc_ar')->label('الوصف (عربي)')->rows(2),
                        Forms\Components\Textarea::make('support_hero_desc_en')->label('Description (English)')->rows(2),
                    ])->columns(2),

                    Forms\Components\Section::make('عنوان قسم الطرق')->schema([
                        Forms\Components\TextInput::make('support_methods_title_ar')->label('العنوان (عربي)'),
                        Forms\Components\TextInput::make('support_methods_title_en')->label('Title (English)'),
                        Forms\Components\Textarea::make('support_methods_desc_ar')->label('الوصف (عربي)')->rows(2),
                        Forms\Components\Textarea::make('support_methods_desc_en')->label('Description (English)')->rows(2),
                    ])->columns(2),

                    Forms\Components\Section::make('بطاقة «دفع إلكتروني»')->schema([
                        Forms\Components\Toggle::make('support_cat_electronic_enabled')->label('مفعّلة')->columnSpanFull(),
                        Forms\Components\TextInput::make('support_cat_electronic_title_ar')->label('العنوان (عربي)'),
                        Forms\Components\TextInput::make('support_cat_electronic_title_en')->label('Title (English)'),
                        Forms\Components\Textarea::make('support_cat_electronic_desc_ar')->label('الوصف (عربي)')->rows(3),
                        Forms\Components\Textarea::make('support_cat_electronic_desc_en')->label('Description (English)')->rows(3),
                        Forms\Components\ColorPicker::make('support_cat_electronic_accent')->label('اللون المميّز'),
                    ])->columns(2)->collapsible(),

                    Forms\Components\Section::make('بطاقة «تحويل مباشر»')->schema([
                        Forms\Components\Toggle::make('support_cat_transfer_enabled')->label('مفعّلة')->columnSpanFull(),
                        Forms\Components\TextInput::make('support_cat_transfer_title_ar')->label('العنوان (عربي)'),
                        Forms\Components\TextInput::make('support_cat_transfer_title_en')->label('Title (English)'),
                        Forms\Components\Textarea::make('support_cat_transfer_desc_ar')->label('الوصف (عربي)')->rows(3),
                        Forms\Components\Textarea::make('support_cat_transfer_desc_en')->label('Description (English)')->rows(3),
                        Forms\Components\ColorPicker::make('support_cat_transfer_accent')->label('اللون المميّز'),
                    ])->columns(2)->collapsible(),

                    Forms\Components\Section::make('بطاقة «عملات رقمية»')->schema([
                        Forms\Components\Toggle::make('support_cat_crypto_enabled')->label('مفعّلة')->columnSpanFull(),
                        Forms\Components\TextInput::make('support_cat_crypto_title_ar')->label('العنوان (عربي)'),
                        Forms\Components\TextInput::make('support_cat_crypto_title_en')->label('Title (English)'),
                        Forms\Components\Textarea::make('support_cat_crypto_desc_ar')->label('الوصف (عربي)')->rows(3),
                        Forms\Components\Textarea::make('support_cat_crypto_desc_en')->label('Description (English)')->rows(3),
                        Forms\Components\ColorPicker::make('support_cat_crypto_accent')->label('اللون المميّز'),
                    ])->columns(2)->collapsible(),

                    Forms\Components\Section::make('خطوات الويزارد')->schema([
                        Forms\Components\TextInput::make('support_step_method_label_ar')->label('الخطوة 1 (عربي)'),
                        Forms\Components\TextInput::make('support_step_method_label_en')->label('Step 1 (English)'),
                        Forms\Components\TextInput::make('support_step_proof_label_ar')->label('الخطوة 2 (عربي)'),
                        Forms\Components\TextInput::make('support_step_proof_label_en')->label('Step 2 (English)'),
                        Forms\Components\TextInput::make('support_step_team_label_ar')->label('الخطوة 3 (عربي)'),
                        Forms\Components\TextInput::make('support_step_team_label_en')->label('Step 3 (English)'),
                        Forms\Components\TextInput::make('support_step_contact_label_ar')->label('الخطوة 4 (عربي)'),
                        Forms\Components\TextInput::make('support_step_contact_label_en')->label('Step 4 (English)'),
                        Forms\Components\TextInput::make('support_step_progress_label_ar')
                            ->label('نص التقدّم (عربي)')->helperText('استخدم :current و :total'),
                        Forms\Components\TextInput::make('support_step_progress_label_en')
                            ->label('Progress text (English)')->helperText('Use :current and :total'),
                        Forms\Components\TextInput::make('support_step_completion_label_ar')
                            ->label('نسبة الإكمال (عربي)')->helperText('استخدم :percent'),
                        Forms\Components\TextInput::make('support_step_completion_label_en')
                            ->label('Completion text (English)')->helperText('Use :percent'),
                    ])->columns(2)->collapsible(),

                    Forms\Components\Section::make('المبالغ والباقات')->schema([
                        Forms\Components\TextInput::make('support_plans_title_ar')->label('عنوان القسم (عربي)'),
                        Forms\Components\TextInput::make('support_plans_title_en')->label('Section title (English)'),
                        Forms\Components\Select::make('support_default_interval')
                            ->label('الدورية الافتراضية')
                            ->options(\App\Support\SupportOptions::intervals()),
                        Forms\Components\TextInput::make('support_default_currency')->label('العملة الافتراضية')->maxLength(3),
                        Forms\Components\TextInput::make('support_min_amount')->label('أقل مبلغ')->numeric()->prefix('$'),
                        Forms\Components\TextInput::make('support_max_amount')->label('أعلى مبلغ')->numeric()->prefix('$'),
                        Forms\Components\Toggle::make('support_custom_amount_enabled')->label('السماح بمبلغ مخصص'),
                        Forms\Components\TextInput::make('support_custom_amount_label_ar')->label('نص حقل المبلغ (عربي)'),
                        Forms\Components\TextInput::make('support_custom_amount_label_en')->label('Amount field text (English)'),
                    ])->columns(2)->collapsible(),

                    Forms\Components\Section::make('نصوص الأزرار والرسائل')->schema([
                        Forms\Components\TextInput::make('support_continue_label_ar')->label('المتابعة (عربي)'),
                        Forms\Components\TextInput::make('support_continue_label_en')->label('Continue (English)'),
                        Forms\Components\TextInput::make('support_back_label_ar')->label('رجوع (عربي)'),
                        Forms\Components\TextInput::make('support_back_label_en')->label('Back (English)'),
                        Forms\Components\TextInput::make('support_submit_label_ar')->label('إرسال (عربي)'),
                        Forms\Components\TextInput::make('support_submit_label_en')->label('Submit (English)'),
                        Forms\Components\TextInput::make('support_copy_label_ar')->label('نسخ (عربي)'),
                        Forms\Components\TextInput::make('support_copy_label_en')->label('Copy (English)'),
                        Forms\Components\TextInput::make('support_copied_label_ar')->label('تم النسخ (عربي)'),
                        Forms\Components\TextInput::make('support_copied_label_en')->label('Copied (English)'),
                        Forms\Components\TextInput::make('support_choose_method_label_ar')->label('اختر وسيلة التحويل (عربي)'),
                        Forms\Components\TextInput::make('support_choose_method_label_en')->label('Choose method (English)'),
                        Forms\Components\Textarea::make('support_proof_hint_ar')->label('تنبيه رفع الإثبات (عربي)')->rows(2),
                        Forms\Components\Textarea::make('support_proof_hint_en')->label('Proof hint (English)')->rows(2),
                        Forms\Components\TextInput::make('support_success_title_ar')->label('عنوان النجاح (عربي)'),
                        Forms\Components\TextInput::make('support_success_title_en')->label('Success title (English)'),
                        Forms\Components\Textarea::make('support_success_message_ar')->label('رسالة النجاح (عربي)')->rows(2),
                        Forms\Components\Textarea::make('support_success_message_en')->label('Success message (English)')->rows(2),
                    ])->columns(2)->collapsible(),
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
                        ->helperText('لما يكون مفعّل، بيتم جلب الريلز من حساب إنستغرام وعرضها في هذا التاب وعبر /api/v1/reels')
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
                        ->helperText('Long-lived token من Meta. إذا انتهت صلاحيته تظهر الريلز فارغة في /pages/content و /reels — جدّد التوكن من Graph API Explorer واحفظه هنا.')
                        ->columnSpanFull(),

                    Forms\Components\Placeholder::make('instagram_reels_preview')
                        ->hiddenLabel()
                        ->content(fn (): \Illuminate\Support\HtmlString => new \Illuminate\Support\HtmlString(
                            view('filament.pages.partials.instagram-reels-preview', [
                                'livewire' => $this,
                            ])->render()
                        ))
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
            $value = $state[$key] ?? $default;

            if ($key === 'header_nav_links' && is_array($value)) {
                $value = $this->filterHeaderNavLinks($value);
            }

            Setting::set($key, $value, group: $group, type: $type);
        }

        Notification::make()
            ->title('تم حفظ الإعدادات بنجاح')
            ->success()
            ->send();
    }

    /**
     * @param  list<array<string, mixed>>  $items
     * @return list<array<string, mixed>>
     */
    protected function filterHeaderNavLinks(array $items): array
    {
        return collect($items)
            ->reject(fn (array $item) => ($item['key'] ?? '') === 'courses')
            ->values()
            ->all();
    }
}
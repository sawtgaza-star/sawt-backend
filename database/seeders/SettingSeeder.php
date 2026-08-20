<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // عام
            ['group' => 'general', 'key' => 'site_name', 'value' => 'منصة صوت', 'type' => 'string'],
            ['group' => 'general', 'key' => 'site_description', 'value' => 'منصة إعلامية عربية تدعم صنّاع المحتوى', 'type' => 'text'],
            ['group' => 'general', 'key' => 'default_locale', 'value' => 'ar', 'type' => 'string'],

            // payment
            ['group' => 'payment', 'key' => 'platform_fee_pct', 'value' => '5', 'type' => 'number'],
            ['group' => 'payment', 'key' => 'min_donation_amount', 'value' => '5', 'type' => 'number'],
            ['group' => 'payment', 'key' => 'bank_name', 'value' => 'Bank of Palestine', 'type' => 'string'],
            ['group' => 'payment', 'key' => 'bank_account_owner', 'value' => 'مؤسسة صوت للإعلام', 'type' => 'string'],
            ['group' => 'payment', 'key' => 'bank_account_number', 'value' => '', 'type' => 'string'],
            ['group' => 'payment', 'key' => 'bank_iban', 'value' => '', 'type' => 'string'],
            ['group' => 'payment', 'key' => 'paypal_mode', 'value' => 'sandbox', 'type' => 'string'],

            // finance (fund split)
            ['group' => 'finance', 'key' => 'fund_split_creators_pct', 'value' => '40', 'type' => 'number'],
            ['group' => 'finance', 'key' => 'fund_split_media_pct', 'value' => '35', 'type' => 'number'],
            ['group' => 'finance', 'key' => 'fund_split_support_pct', 'value' => '25', 'type' => 'number'],

            // contact
            ['group' => 'contact', 'key' => 'contact_phone', 'value' => '', 'type' => 'string'],
            ['group' => 'contact', 'key' => 'contact_email', 'value' => '', 'type' => 'string'],
            ['group' => 'contact', 'key' => 'support_whatsapp', 'value' => '', 'type' => 'string'],

            // social
            ['group' => 'social', 'key' => 'instagram_url', 'value' => '', 'type' => 'string'],
            ['group' => 'social', 'key' => 'twitter_url', 'value' => '', 'type' => 'string'],
            ['group' => 'social', 'key' => 'telegram_url', 'value' => '', 'type' => 'string'],
            ['group' => 'social', 'key' => 'facebook_url', 'value' => '', 'type' => 'string'],
            ['group' => 'social', 'key' => 'linkedin_url', 'value' => '', 'type' => 'string'],
            ['group' => 'social', 'key' => 'youtube_url', 'value' => '', 'type' => 'string'],

            // platform stats (تظهر بصفحة صناع المحتوى)
            ['group' => 'stats', 'key' => 'reach_count', 'value' => '4000000', 'type' => 'number'],
            ['group' => 'stats', 'key' => 'supporters_count', 'value' => '250000', 'type' => 'number'],
            ['group' => 'stats', 'key' => 'collaborations_count', 'value' => '500', 'type' => 'number'],
            ['group' => 'stats', 'key' => 'active_creators_count', 'value' => '45', 'type' => 'number'],

            // about page
            ['group' => 'about', 'key' => 'about_hero_title_ar', 'value' => 'صناع الأثر.. الفريق خلف منصة صوت', 'type' => 'string'],
            ['group' => 'about', 'key' => 'about_hero_title_en', 'value' => 'Impact Makers.. The Team Behind Sawt Platform', 'type' => 'string'],
            ['group' => 'about', 'key' => 'about_hero_desc_ar', 'value' => 'في هذه الصفحة، نشارككم قصة فريق من الناس إلى الناس، رؤيتنا، رسالتنا، وكيف بدأنا لنكون صوتًا حيًّا ومعينًا لمن لا صوت لهم، وكيف منحنا الناس الأمل.', 'type' => 'text'],
            ['group' => 'about', 'key' => 'about_hero_desc_en', 'value' => 'On this page, we share with you the story of a team from the people to the people — our vision, our message, and how we began to be a living, supportive voice for the voiceless, and how we gave people hope.', 'type' => 'text'],
            ['group' => 'about', 'key' => 'about_header_ar', 'value' => 'من نحن', 'type' => 'string'],
            ['group' => 'about', 'key' => 'about_header_en', 'value' => 'About Sawt', 'type' => 'string'],
            ['group' => 'about', 'key' => 'about_intro_ar', 'value' => 'فريق منصة صوت حاضنة لأصوات غزة. لم نبدأ من فكرةٍ خارقة أو خطةٍ مُحكمة، بل من قرارٍ بسيط: أن نكون حاضرِين، نستمع، ونُعلِن صوت غزة للعالم. نحن من الناس ونعيش معاناتهم عن قرب، فرأينا أن الحاجة واضحة فقررنا ألا نصمت. نعمل بدون أن نتكلم نيابةً عن أحد، وبدون وعودٍ تفوق قدراتنا. وظيفتنا توصيل صوت أهل غزة وإيصاله إلى العالم، مع الحفاظ على كرامة الناس وصوتهم. هدفنا أن نكون جسرًا صادقًا بين من يقدّم الانتباه والدعم لصوت غزة ومن ينتظر من يسمعهم حقًا. اليوم نحن أكثر من فكرة آمن بها شخص واحد؛ نحن حاضنة لأصوات غزة، فريق عمل متكامل.', 'type' => 'text'],
            ['group' => 'about', 'key' => 'about_intro_en', 'value' => 'The Sawt platform team is an incubator for the voices of Gaza. We did not start from an extraordinary idea or a tight plan, but from a simple decision: to be present, to listen, and to announce the voice of Gaza to the world.', 'type' => 'text'],
            ['group' => 'about', 'key' => 'about_platform_question_ar', 'value' => 'ما الذي يدفعنا لنكون صوتك؟', 'type' => 'string'],
            ['group' => 'about', 'key' => 'about_platform_question_en', 'value' => 'What drives us to be your voice?', 'type' => 'string'],
            ['group' => 'about', 'key' => 'about_platform_desc_ar', 'value' => 'نؤمن أن لكل إنسان قصة تستحق أن تُروى، لذلك جاءت صوت لتكون مساحة حرة للتعبير، حيث يلتقي الأفراد لمشاركة تجاربهم وأفكارهم بصدق. نساعدك على إيصال صوتك إلى الآخرين، ونمنح المحتوى الإنساني مساحة حقيقية ليُرى، ويُسمع، ويترك أثرًا.', 'type' => 'text'],
            ['group' => 'about', 'key' => 'about_platform_desc_en', 'value' => 'We believe every person has a story worth telling. That\'s why Sawt was created as a free space for expression.', 'type' => 'text'],
            ['group' => 'about', 'key' => 'about_core_values_subtitle_ar', 'value' => 'قيمنا هي الأساس الذي نبني عليه صوت، وهي ما يقود طريقة عملنا وتطويرنا المستمر', 'type' => 'text'],
            ['group' => 'about', 'key' => 'about_core_values_subtitle_en', 'value' => 'Our values are the foundation on which we build Sawt, and they guide the way we work and continuously improve.', 'type' => 'text'],
            ['group' => 'about', 'key' => 'about_core_values', 'value' => json_encode([
                ['title_ar' => 'التمكين', 'title_en' => 'Empowerment', 'desc_ar' => 'نسعى لأن نكون منبرًا يُمكّن الإنسان، ويصنع تأثيرًا إيجابيًا حقيقيًا يساهم في إيصال صوتنا وصوت المجتمع إلى العالم.', 'desc_en' => 'We strive to be a platform that empowers people and creates a real positive impact.'],
                ['title_ar' => 'الموثوقية', 'title_en' => 'Reliability', 'desc_ar' => 'نحرص على صون الأمانة الإعلامية وحماية الرواية وتوثيقها، معتمدين على معايير أخلاقية راسخة في كل ما ننشر.', 'desc_en' => 'We are committed to preserving media integrity, protecting and documenting the narrative.'],
                ['title_ar' => 'الشراكة', 'title_en' => 'Partnership', 'desc_ar' => 'نؤمن أن قوة "صوت غزة" نبع من تلاحم المجتمع، ونعمل كجسر يصل بين الأصوات المختلفة لتعزيز الدعم المتبادل.', 'desc_en' => 'We believe the strength of "Sawt Gaza" springs from the cohesion of the community.'],
                ['title_ar' => 'الابتكار', 'title_en' => 'Innovation', 'desc_ar' => 'نلتزم بنقل الأخبار والقصص بمهنية عالية وأمانة صحفية، لنكون المصدر الموثوق الذي يعبّر عن الواقع بدقة.', 'desc_en' => 'We commit to delivering news and stories with high professionalism and journalistic integrity.'],
            ], JSON_UNESCAPED_UNICODE), 'type' => 'json'],
            ['group' => 'about', 'key' => 'about_story_subtitle_ar', 'value' => 'قيمنا هي الأساس الذي نبني عليه صوت، وهي ما يقود طريقة عملنا وتطويرنا المستمر', 'type' => 'text'],
            ['group' => 'about', 'key' => 'about_story_subtitle_en', 'value' => 'Our values are the foundation on which we build Sawt, and they guide the way we work and continuously improve.', 'type' => 'text'],
            ['group' => 'about', 'key' => 'about_story_cards', 'value' => json_encode([
                ['title_ar' => 'التأثير', 'title_en' => 'Impact', 'desc_ar' => 'منذ انطلاقنا، استطعنا إيصال أصوات الآلاف من الأشخاص الذين كانوا صامتين، وكشفنا حقائق عديدة تم إخفاؤها عن الرأي العام. قصصنا وصلت لملايين المتابعين، وساهمت في لفت انتباه العالم إلى قضايا مهمشة.', 'desc_en' => 'Since we began, we have managed to carry the voices of thousands of people who were silenced.'],
                ['title_ar' => 'ما نقدم', 'title_en' => 'What We Offer', 'desc_ar' => 'نحن نقدم إعلامًا حقيقيًا يعتمد على القصص الحقيقية والأصوات الصادقة، بعيدًا عن ضغوط الإعلام التقليدي والسرديات الرسمية.', 'desc_en' => 'We provide genuine media built on real stories and honest voices.'],
                ['title_ar' => 'رحلتنا', 'title_en' => 'Our Journey', 'desc_ar' => 'بدأت رحلة "صوت" في ظل ظروف صعبة، حيث كانت الكثير من القصص الحقيقية مخفية والأصوات الصادقة مكتومة تحت ضغوط الإعلام التقليدي والسرديات الرسمية.', 'desc_en' => 'The journey of "Sawt" began under difficult circumstances.'],
            ], JSON_UNESCAPED_UNICODE), 'type' => 'json'],
            ['group' => 'about', 'key' => 'about_join_title_ar', 'value' => 'قد تكون قصتك بداية التغيير', 'type' => 'string'],
            ['group' => 'about', 'key' => 'about_join_title_en', 'value' => 'Your story could be the start of change', 'type' => 'string'],
            ['group' => 'about', 'key' => 'about_join_desc_ar', 'value' => 'كل صوت مهم، إذا كانت لديك قصة تستحق أن تُسمع فإن صوت ستدعمك من أول محادثة إلى التأثير العام.', 'type' => 'text'],
            ['group' => 'about', 'key' => 'about_join_desc_en', 'value' => 'Every voice matters. If you have a story worth hearing, Sawt will support you from the first conversation to public impact.', 'type' => 'text'],

            // reels (إنستغرام)
            ['group' => 'reels', 'key' => 'reels_enabled', 'value' => '0', 'type' => 'boolean'],
            ['group' => 'reels', 'key' => 'instagram_user_id', 'value' => '', 'type' => 'string'],
            ['group' => 'reels', 'key' => 'instagram_access_token', 'value' => '', 'type' => 'string'],
            ['group' => 'reels', 'key' => 'instagram_cache_ttl', 'value' => '300', 'type' => 'number'],

            // الصفحة الرئيسية
            ['group' => 'home', 'key' => 'home_hero_slides', 'value' => json_encode([
                ['image' => '', 'title_ar' => 'منصة صوت', 'title_en' => 'Sawt Platform', 'subtitle_ar' => 'نروي قصص غزة بكرامة... ونبني جيلاً جديداً من صناع المحتوى', 'subtitle_en' => "We tell Gaza's stories with dignity and build a new generation of creators"],
                ['image' => '', 'title_ar' => 'منصة صوت', 'title_en' => 'Sawt Platform', 'subtitle_ar' => 'نروي قصص غزة بكرامة... ونبني جيلاً جديداً من صناع المحتوى', 'subtitle_en' => "We tell Gaza's stories with dignity and build a new generation of creators"],
                ['image' => '', 'title_ar' => 'منصة صوت', 'title_en' => 'Sawt Platform', 'subtitle_ar' => 'نروي قصص غزة بكرامة... ونبني جيلاً جديداً من صناع المحتوى', 'subtitle_en' => "We tell Gaza's stories with dignity and build a new generation of creators"],
            ], JSON_UNESCAPED_UNICODE), 'type' => 'json'],
            ['group' => 'home', 'key' => 'home_hero_trust_ar', 'value' => 'ثقة آلاف المتابعين في منصة صوت غزة بصدق وتأثير', 'type' => 'string'],
            ['group' => 'home', 'key' => 'home_hero_trust_en', 'value' => 'Trusted by thousands of followers of Sawt Gaza', 'type' => 'string'],
            ['group' => 'home', 'key' => 'home_hero_btn_support_ar', 'value' => 'ادعم صوت', 'type' => 'string'],
            ['group' => 'home', 'key' => 'home_hero_btn_support_en', 'value' => 'Support Sawt', 'type' => 'string'],
            ['group' => 'home', 'key' => 'home_hero_btn_collab_ar', 'value' => 'تعاون معنا', 'type' => 'string'],
            ['group' => 'home', 'key' => 'home_hero_btn_collab_en', 'value' => 'Collaborate with us', 'type' => 'string'],
            ['group' => 'home', 'key' => 'home_stat_team', 'value' => '20+', 'type' => 'string'],
            ['group' => 'home', 'key' => 'home_stat_stories', 'value' => '100+', 'type' => 'string'],
            ['group' => 'home', 'key' => 'home_stat_views', 'value' => '+30', 'type' => 'string'],
            ['group' => 'home', 'key' => 'home_stat_videos', 'value' => '30+', 'type' => 'string'],
            ['group' => 'home', 'key' => 'home_stat_followers', 'value' => '+10', 'type' => 'string'],
            ['group' => 'home', 'key' => 'home_who_we_are_ar', 'value' => 'من نحن', 'type' => 'string'],
            ['group' => 'home', 'key' => 'home_who_we_are_en', 'value' => 'Who We Are', 'type' => 'string'],
            ['group' => 'home', 'key' => 'home_who_subtitle_ar', 'value' => 'إعلام هادف، قصص حقيقية، وأثر مستدام', 'type' => 'string'],
            ['group' => 'home', 'key' => 'home_who_subtitle_en', 'value' => 'Impactful media, real stories, and sustainable impact', 'type' => 'string'],
            ['group' => 'home', 'key' => 'home_welcome_title_ar', 'value' => 'نؤمن أن لكل إنسان قصة تستحق أن تروى', 'type' => 'string'],
            ['group' => 'home', 'key' => 'home_welcome_title_en', 'value' => 'We believe every person has a story worth telling', 'type' => 'string'],
            ['group' => 'home', 'key' => 'home_welcome_desc_ar', 'value' => '', 'type' => 'text'],
            ['group' => 'home', 'key' => 'home_welcome_desc_en', 'value' => '', 'type' => 'text'],
            ['group' => 'home', 'key' => 'home_who_cta_ar', 'value' => 'اكتشف المزيد', 'type' => 'string'],
            ['group' => 'home', 'key' => 'home_who_cta_en', 'value' => 'Discover more', 'type' => 'string'],
            ['group' => 'home', 'key' => 'home_who_features', 'value' => json_encode([
                ['icon' => null, 'title_ar' => 'محتوى يعبر عن صوتك', 'title_en' => 'Content that expresses your voice'],
                ['icon' => null, 'title_ar' => 'تمكين المواهب الشابة', 'title_en' => 'Empowering young talent'],
                ['icon' => null, 'title_ar' => 'الإنتاج والتغطيات الإعلامية', 'title_en' => 'Media production and coverage'],
                ['icon' => null, 'title_ar' => 'صناعة أثر حقيقي ومستدام', 'title_en' => 'Creating real and sustainable impact'],
            ], JSON_UNESCAPED_UNICODE), 'type' => 'json'],
            ['group' => 'home', 'key' => 'home_news_title_ar', 'value' => 'أخر أخبارنا', 'type' => 'string'],
            ['group' => 'home', 'key' => 'home_news_title_en', 'value' => 'Our Latest News', 'type' => 'string'],
            ['group' => 'home', 'key' => 'home_news_subtitle_ar', 'value' => 'شاهد أحدث القصص والفيديوهات من منصة صوت', 'type' => 'string'],
            ['group' => 'home', 'key' => 'home_news_subtitle_en', 'value' => 'Watch the latest stories and videos from Sawt', 'type' => 'string'],
            ['group' => 'home', 'key' => 'home_news_view_all_ar', 'value' => 'عرض جميع الأخبار', 'type' => 'string'],
            ['group' => 'home', 'key' => 'home_news_view_all_en', 'value' => 'View all news', 'type' => 'string'],
            ['group' => 'home', 'key' => 'home_news_items', 'value' => json_encode([
                ['image' => '', 'title_ar' => 'صانع المحتوى في غزة', 'title_en' => 'Content creator in Gaza', 'excerpt_ar' => '', 'excerpt_en' => '', 'date' => null],
                ['image' => '', 'title_ar' => 'صانع المحتوى في غزة', 'title_en' => 'Content creator in Gaza', 'excerpt_ar' => '', 'excerpt_en' => '', 'date' => null],
                ['image' => '', 'title_ar' => 'صانع المحتوى في غزة', 'title_en' => 'Content creator in Gaza', 'excerpt_ar' => '', 'excerpt_en' => '', 'date' => null],
            ], JSON_UNESCAPED_UNICODE), 'type' => 'json'],
            ['group' => 'home', 'key' => 'home_creators_title_ar', 'value' => 'صناع المحتوى في صوت', 'type' => 'string'],
            ['group' => 'home', 'key' => 'home_creators_title_en', 'value' => 'Content Creators in Sawt', 'type' => 'string'],
            ['group' => 'home', 'key' => 'home_creators_desc_ar', 'value' => 'مجموعة من صناع المحتوى المبدعين الذين يوظفون مهاراتهم لإنتاج محتوى هادف ومؤثر.', 'type' => 'text'],
            ['group' => 'home', 'key' => 'home_creators_desc_en', 'value' => 'A group of creative content creators producing purposeful and influential content.', 'type' => 'text'],
            ['group' => 'home', 'key' => 'home_creators_view_all_ar', 'value' => 'عرض الكل', 'type' => 'string'],
            ['group' => 'home', 'key' => 'home_creators_view_all_en', 'value' => 'View all', 'type' => 'string'],
            ['group' => 'home', 'key' => 'home_creators_limit', 'value' => '10', 'type' => 'number'],
            ['group' => 'home', 'key' => 'home_sections_title_ar', 'value' => 'أقسام المنصة', 'type' => 'string'],
            ['group' => 'home', 'key' => 'home_sections_title_en', 'value' => 'Platform Sections', 'type' => 'string'],
            ['group' => 'home', 'key' => 'home_sections_subtitle_ar', 'value' => 'تعرف على أذرع صوت وكيف نعمل معاً لصناعة الأثر', 'type' => 'string'],
            ['group' => 'home', 'key' => 'home_sections_subtitle_en', 'value' => 'Discover Sawt’s arms and how we work together for impact', 'type' => 'string'],
            ['group' => 'home', 'key' => 'home_platform_sections', 'value' => json_encode([
                ['image' => '', 'title_ar' => 'منصة صوت', 'title_en' => 'Sawt Platform', 'desc_ar' => '', 'desc_en' => '', 'stat1_ar' => '+30 مليون مشاهدة', 'stat1_en' => '+30 million views', 'stat2_ar' => '+100 مقطع', 'stat2_en' => '+100 clips', 'cta_ar' => 'اقرأ المزيد', 'cta_en' => 'Read more'],
                ['image' => '', 'title_ar' => 'حاضنة صوت', 'title_en' => 'Sawt Incubator', 'desc_ar' => '', 'desc_en' => '', 'stat1_ar' => '+100 متدرب', 'stat1_en' => '+100 trainees', 'stat2_ar' => '+10 مشاريع', 'stat2_en' => '+10 projects', 'cta_ar' => 'اقرأ المزيد', 'cta_en' => 'Read more'],
                ['image' => '', 'title_ar' => 'صوت ميديا', 'title_en' => 'Sawt Media', 'desc_ar' => '', 'desc_en' => '', 'stat1_ar' => '+500 محتوى إبداعي', 'stat1_en' => '+500 creative pieces', 'stat2_ar' => '+100 عميل', 'stat2_en' => '+100 clients', 'cta_ar' => 'اقرأ المزيد', 'cta_en' => 'Read more'],
            ], JSON_UNESCAPED_UNICODE), 'type' => 'json'],
            ['group' => 'home', 'key' => 'home_partners_title_ar', 'value' => 'شركاؤنا في صوت', 'type' => 'string'],
            ['group' => 'home', 'key' => 'home_partners_title_en', 'value' => 'Our Partners in Sawt', 'type' => 'string'],
            ['group' => 'home', 'key' => 'home_partners_subtitle_ar', 'value' => 'شركاء يشاركونا رحلة التأثير وصناعة التغيير', 'type' => 'string'],
            ['group' => 'home', 'key' => 'home_partners_subtitle_en', 'value' => 'Partners who share our journey of impact and change', 'type' => 'string'],
            ['group' => 'home', 'key' => 'home_partners', 'value' => json_encode([
                ['name' => '', 'logo' => ''],
                ['name' => '', 'logo' => ''],
                ['name' => '', 'logo' => ''],
            ], JSON_UNESCAPED_UNICODE), 'type' => 'json'],
            ['group' => 'home', 'key' => 'home_stories_title_ar', 'value' => 'هل لديك صوت يستحق أن يُسمع؟', 'type' => 'string'],
            ['group' => 'home', 'key' => 'home_stories_title_en', 'value' => 'Do you have a voice that deserves to be heard?', 'type' => 'string'],
            ['group' => 'home', 'key' => 'home_stories_desc_ar', 'value' => 'شاركنا قصتك أو قضيتك، وقد تكون القصة القادمة التي نسلط الضوء عليها ليصل صوتها إلى العالم', 'type' => 'text'],
            ['group' => 'home', 'key' => 'home_stories_desc_en', 'value' => 'Share your story or cause — it may be the next one we highlight so its voice reaches the world', 'type' => 'text'],
            ['group' => 'home', 'key' => 'home_stories_badge_ar', 'value' => '+100 قصة واقعية نقلتها صوت إلى العالم', 'type' => 'string'],
            ['group' => 'home', 'key' => 'home_stories_badge_en', 'value' => '+100 real stories Sawt has brought to the world', 'type' => 'string'],
            ['group' => 'home', 'key' => 'home_stories_items', 'value' => json_encode([
                [
                    'image' => '',
                    'badge_ar' => 'قصة نجاح',
                    'badge_en' => 'Success story',
                    'title_ar' => 'سمير',
                    'title_en' => 'Samir',
                    'desc_ar' => '',
                    'desc_en' => '',
                ],
                [
                    'image' => '',
                    'badge_ar' => 'قصة نجاح',
                    'badge_en' => 'Success story',
                    'title_ar' => 'أغلى كاسة شاي في العالم',
                    'title_en' => 'The most expensive cup of tea in the world',
                    'desc_ar' => '',
                    'desc_en' => '',
                ],
            ], JSON_UNESCAPED_UNICODE), 'type' => 'json'],
            ['group' => 'home', 'key' => 'home_team_title_ar', 'value' => 'أعضاء فريقنا', 'type' => 'string'],
            ['group' => 'home', 'key' => 'home_team_title_en', 'value' => 'Our Team Members', 'type' => 'string'],
            ['group' => 'home', 'key' => 'home_team_subtitle_ar', 'value' => 'تعرف على فريق صوت، مبدعين يصنعون الفرق', 'type' => 'string'],
            ['group' => 'home', 'key' => 'home_team_subtitle_en', 'value' => 'Get to know the Sawt team, creators who make a difference', 'type' => 'string'],
            ['group' => 'home', 'key' => 'home_team_cta_ar', 'value' => 'عرض الملف الشخصي', 'type' => 'string'],
            ['group' => 'home', 'key' => 'home_team_cta_en', 'value' => 'View profile', 'type' => 'string'],
            ['group' => 'home', 'key' => 'home_team_limit', 'value' => '8', 'type' => 'number'],
            ['group' => 'home', 'key' => 'home_join_cta_title_ar', 'value' => 'انضم إلينا كصانع محتوى', 'type' => 'string'],
            ['group' => 'home', 'key' => 'home_join_cta_title_en', 'value' => 'Join us as a content creator', 'type' => 'string'],
            ['group' => 'home', 'key' => 'home_join_cta_desc_ar', 'value' => 'صوت تجمع صناع المحتوى . كن صوت من لا صوت له', 'type' => 'text'],
            ['group' => 'home', 'key' => 'home_join_cta_desc_en', 'value' => 'Sawt brings together content creators. Be the voice for the voiceless', 'type' => 'text'],
            ['group' => 'home', 'key' => 'home_join_cta_button_ar', 'value' => 'طلب الانضمام', 'type' => 'string'],
            ['group' => 'home', 'key' => 'home_join_cta_button_en', 'value' => 'Request to join', 'type' => 'string'],
            ['group' => 'home', 'key' => 'home_reviews_title_ar', 'value' => 'آراؤكم في المحتوى', 'type' => 'string'],
            ['group' => 'home', 'key' => 'home_reviews_title_en', 'value' => 'Your opinions on the content', 'type' => 'string'],
            ['group' => 'home', 'key' => 'home_reviews_desc_ar', 'value' => 'نفخر بثقة جمهورنا، ونعتز بكل رأي يساهم في تطوير رسالتنا الإعلامية.', 'type' => 'text'],
            ['group' => 'home', 'key' => 'home_reviews_desc_en', 'value' => 'We take pride in our audience’s trust and value every opinion that develops our media message.', 'type' => 'text'],
            ['group' => 'home', 'key' => 'home_reviews_use_instagram', 'value' => '1', 'type' => 'boolean'],

            // صفحة محتوانا
            ['group' => 'content', 'key' => 'content_hero_title_ar', 'value' => 'كل فكرة إلها صوت... وصوت بيجمعهم', 'type' => 'string'],
            ['group' => 'content', 'key' => 'content_hero_title_en', 'value' => 'Every idea has a voice… and Sawt brings them together', 'type' => 'string'],
            ['group' => 'content', 'key' => 'content_hero_desc_ar', 'value' => 'نؤمن أن لكل إنسان قصة تستحق أن تُروى، لذلك جاءت صوت لتكون مساحة حرة للتعبير.', 'type' => 'text'],
            ['group' => 'content', 'key' => 'content_hero_desc_en', 'value' => 'We believe every person has a story worth telling.', 'type' => 'text'],
            ['group' => 'content', 'key' => 'content_hero_items', 'value' => json_encode([
                ['image' => ''],
                ['image' => ''],
                ['image' => ''],
            ], JSON_UNESCAPED_UNICODE), 'type' => 'json'],
            ['group' => 'content', 'key' => 'content_most_viewed_title_ar', 'value' => 'الأكثر مشاهدة', 'type' => 'string'],
            ['group' => 'content', 'key' => 'content_most_viewed_title_en', 'value' => 'Most viewed', 'type' => 'string'],
            ['group' => 'content', 'key' => 'content_most_viewed_more_ar', 'value' => 'رؤية المزيد', 'type' => 'string'],
            ['group' => 'content', 'key' => 'content_most_viewed_more_en', 'value' => 'See more', 'type' => 'string'],
            ['group' => 'content', 'key' => 'content_most_viewed_limit', 'value' => '6', 'type' => 'number'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
